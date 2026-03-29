<?php

namespace Tests\Feature\Reservation;

use App\Models\Reservation;
use App\Models\User;
use App\Models\ParkingSpot;
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
}
