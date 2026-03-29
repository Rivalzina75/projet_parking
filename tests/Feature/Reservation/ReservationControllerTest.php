<?php

namespace Tests\Feature\Reservation;

use App\Models\Reservation;
use App\Models\User;
use App\Models\ParkingSpot;
use App\Models\WaitingListEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un user validé peut voir son dashboard avec réservations.
     */
    public function test_user_can_view_reservations_in_dashboard(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $spot = ParkingSpot::factory()->create();

        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
        ]);

        $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertStatus(200)
            ->assertViewIs('user.dashboard');
    }

    /**
     * Test qu'un user peut voir le numéro de sa place réservée.
     */
    public function test_user_can_view_assigned_parking_spot(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $spot = ParkingSpot::factory()->create(['number' => '42']);

        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
        ]);

        $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertStatus(200)
            ->assertSee('42', false);
    }

    /**
     * Test qu'un user peut fermer sa réservation.
     */
    public function test_user_can_cancel_reservation(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $spot = ParkingSpot::factory()->create();

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
            'ended_at' => null,
        ]);

        $this->actingAs($user)
            ->post("/utilisateur/reservation/{$reservation->id}/close")
            ->assertStatus(302);

        $reservation->refresh();
        $this->assertNotNull($reservation->ended_at);
    }

    /**
     * Test qu'un user peut voir son historique de réservations.
     */
    public function test_user_can_view_reservation_history(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $spot1 = ParkingSpot::factory()->create(['number' => '1']);
        $spot2 = ParkingSpot::factory()->create(['number' => '2']);

        Reservation::factory()->closed()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot1->id,
        ]);

        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot2->id,
        ]);

        $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertStatus(200);
    }

    /**
     * Test qu'un utilisateur peut voir son rang en file d'attente sur le dashboard.
     */
    public function test_user_can_view_waiting_rank_in_dashboard(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        WaitingListEntry::factory()->create([
            'user_id' => $user->id,
            'position' => 3,
        ]);

        $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertStatus(200)
            ->assertSee('Rang 3', false);
    }

    /**
     * Test qu'un utilisateur ne peut pas fermer la réservation de quelqu'un d'autre.
     */
    public function test_user_cannot_close_other_user_reservation(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        /** @var User $user2 */
        $user2 = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $spot = ParkingSpot::factory()->create();

        $reservation = Reservation::factory()->create([
            'user_id' => $user1->id,
            'parking_spot_id' => $spot->id,
            'ended_at' => null,
        ]);

        $this->actingAs($user2)
            ->post("/utilisateur/reservation/{$reservation->id}/close")
            ->assertStatus(403);

        $reservation->refresh();
        $this->assertNull($reservation->ended_at);
    }

    /**
     * Test qu'après une fermeture prématurée, l'utilisateur peut refaire une demande.
     */
    public function test_user_can_request_reservation_after_early_close(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $spot = ParkingSpot::factory()->create();

        // Première réservation
        $reservation1 = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
            'ended_at' => null,
        ]);

        // Fermer la réservation prématurément
        $this->actingAs($user)
            ->post("/utilisateur/reservation/{$reservation1->id}/close")
            ->assertStatus(302);

        // Vérifier qu'elle est fermée
        $reservation1->refresh();
        $this->assertNotNull($reservation1->ended_at);

        // Deuxième demande devrait réussir
        $this->actingAs($user)
            ->post('/utilisateur/reservation')
            ->assertStatus(302);

        // Vérifier qu'une nouvelle réservation existe
        $reservation2 = Reservation::where('user_id', $user->id)
            ->where('id', '!=', $reservation1->id)
            ->first();

        $this->assertNotNull($reservation2);
        $this->assertNull($reservation2->ended_at);
    }

    /**
     * Test qu'après une expiration, l'utilisateur peut refaire une demande.
     */
    public function test_user_can_request_reservation_after_expiration(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $spot = ParkingSpot::factory()->create();

        // Première réservation expirée
        $reservation1 = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
            'starts_at' => now()->subHours(9),
            'expires_at' => now()->subHours(1),
            'ended_at' => null,
        ]);

        // Fermer les réservations expirées
        app(\App\Services\ParkingService::class)->closeExpiredReservations();

        // Vérifier qu'elle est fermée
        $reservation1->refresh();
        $this->assertNotNull($reservation1->ended_at);

        // Deuxième demande devrait réussir
        $this->actingAs($user)
            ->post('/utilisateur/reservation')
            ->assertStatus(302);

        // Vérifier qu'une nouvelle réservation existe
        $reservation2 = Reservation::where('user_id', $user->id)
            ->where('id', '!=', $reservation1->id)
            ->first();

        $this->assertNotNull($reservation2);
        $this->assertNull($reservation2->ended_at);
    }
}
