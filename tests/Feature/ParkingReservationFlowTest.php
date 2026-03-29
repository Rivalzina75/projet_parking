<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\WaitingListEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test le flux complet: demande → réservation → fermeture.
     */
    public function test_complete_reservation_flow(): void
    {
        // Créer place et user
        $spot1 = ParkingSpot::factory()->create(['number' => '1']);
        $spot2 = ParkingSpot::factory()->create(['number' => '2']);
        /** @var User $user1 */
        $user1 = User::factory()->create(['is_validated' => true, 'role' => 'user']);
        /** @var User $user2 */
        $user2 = User::factory()->create(['is_validated' => true, 'role' => 'user']);

        // User1 demande une place → obtient spot1
        $this->actingAs($user1)->post('/utilisateur/reservation')->assertRedirect();
        $this->assertTrue(Reservation::where('user_id', $user1->id)->exists());

        // User2 demande une place → obtient spot2
        $this->actingAs($user2)->post('/utilisateur/reservation')->assertRedirect();
        $this->assertTrue(Reservation::where('user_id', $user2->id)->exists());
    }

    /**
     * Test la file d'attente quand plus de places.
     */
    public function test_waiting_list_flow(): void
    {
        $spot = ParkingSpot::factory()->create();
        /** @var User $user1 */
        $user1 = User::factory()->create(['is_validated' => true, 'role' => 'user']);
        /** @var User $user2 */
        $user2 = User::factory()->create(['is_validated' => true, 'role' => 'user']);
        /** @var User $user3 */
        $user3 = User::factory()->create(['is_validated' => true, 'role' => 'user']);

        // Réserver la seule place
        $this->actingAs($user1)->post('/utilisateur/reservation')->assertRedirect();

        // User2 et User3 devraient être en attente
        $this->actingAs($user2)->post('/utilisateur/reservation')->assertRedirect();
        $this->actingAs($user3)->post('/utilisateur/reservation')->assertRedirect();

        $this->assertCount(2, WaitingListEntry::all());
    }

    /**
     * Test l'accès refusé pour les non-validés.
     */
    public function test_unvalidated_user_cannot_request(): void
    {
        ParkingSpot::factory()->create();
        /** @var User $user */
        $user = User::factory()->create(['is_validated' => false, 'role' => 'user']);

        $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertRedirect(route('account.pending'));
    }

    /**
     * Test l'attribution forcée par admin.
     */
    public function test_admin_forced_assignment(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        $spot = ParkingSpot::factory()->create();

        $this->actingAs($admin)
            ->post('/admin/reservation/force', [
                'user_id' => $user->id,
                'parking_spot_id' => $spot->id,
            ])
            ->assertRedirect();

        $this->assertTrue(
            Reservation::where('user_id', $user->id)->where('parking_spot_id', $spot->id)->exists()
        );
    }

    /**
     * Test que l'expiration respecte la durée par défaut de l'application.
     */
    public function test_reservation_expiration_uses_default_duration(): void
    {
        $user = User::factory()->create(['is_validated' => true, 'role' => 'user']);
        ParkingSpot::factory()->create();

        $this->actingAs($user)->post('/utilisateur/reservation')->assertRedirect();

        $reservation = Reservation::where('user_id', $user->id)->first();
        $expectedExpiration = $reservation->starts_at->addHours(8);

        // Durée par défaut est 8h, tolérance 2 sec
        $this->assertTrue(
            $reservation->expires_at->diffInSeconds($expectedExpiration) < 2
        );
    }

    /**
     * Test qu'un utilisateur avec réservation active ne peut pas en faire une nouvelle.
     */
    public function test_user_with_active_reservation_cannot_request_another(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['is_validated' => true, 'role' => 'user']);
        ParkingSpot::factory()->count(3)->create();

        // Première demande OK
        $this->actingAs($user)->post('/utilisateur/reservation')->assertRedirect();
        $this->assertDatabaseHas('reservations', ['user_id' => $user->id]);

        // Deuxième tentative → erreur
        $this->actingAs($user)
            ->from('/utilisateur/dashboard')
            ->post('/utilisateur/reservation')
            ->assertSessionHasErrors('reservation');
    }

    /**
     * Test qu'un utilisateur en file d'attente ne peut pas faire une autre demande.
     */
    public function test_user_in_waiting_list_cannot_request_again(): void
    {
        $spot = ParkingSpot::factory()->create();
        /** @var User $user1 */
        $user1 = User::factory()->create(['is_validated' => true, 'role' => 'user']);
        /** @var User $user2 */
        $user2 = User::factory()->create(['is_validated' => true, 'role' => 'user']);

        // User1 prend la seule place
        $this->actingAs($user1)->post('/utilisateur/reservation')->assertRedirect();

        // User2 premier appel → attente
        $this->actingAs($user2)->post('/utilisateur/reservation')->assertRedirect();
        $this->assertDatabaseHas('waiting_list_entries', ['user_id' => $user2->id]);

        // User2 deuxième appel → bloqué
        $this->actingAs($user2)
            ->from('/utilisateur/dashboard')
            ->post('/utilisateur/reservation')
            ->assertSessionHasErrors('reservation');
    }
}
