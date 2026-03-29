<?php

namespace Tests\Feature\Reservation;

use App\Models\User;
use App\Models\Reservation;
use App\Models\ParkingSpot;
use App\Models\WaitingListEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un admin peut forcer une attribution de place.
     */
    public function test_admin_can_force_assign_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        $spotA = ParkingSpot::factory()->create();
        $spotB = ParkingSpot::factory()->create();

        $this->actingAs($admin)
            ->post('/admin/reservation/force', [
                'user_id' => $user->id,
                'parking_spot_id' => $spotB->id,
            ])
            ->assertRedirect();

        $this->assertTrue(
            Reservation::where('user_id', $user->id)
                ->where('parking_spot_id', $spotB->id)
                ->exists()
        );

        $this->assertFalse(
            Reservation::where('user_id', $user->id)
                ->where('parking_spot_id', $spotA->id)
                ->exists()
        );
    }

    /**
     * Test qu'un admin peut fermer la réservation d'un utilisateur.
     */
    public function test_admin_can_close_user_reservation(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        $reservation = Reservation::factory()->create(['user_id' => $user->id, 'ended_at' => null]);

        $this->actingAs($admin)
            ->post("/admin/reservation/{$reservation->id}/close")
            ->assertRedirect();

        $reservation->refresh();
        $this->assertNotNull($reservation->ended_at);
    }

    /**
     * Test qu'un admin peut enlever une place active via l'action dédiée.
     */
    public function test_admin_can_remove_user_reservation_from_user_list_action(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        $spot = ParkingSpot::factory()->create();

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
            'ended_at' => null,
        ]);

        $this->actingAs($admin)
            ->post('/admin/reservation/remove', [
                'user_id' => $user->id,
            ])
            ->assertRedirect();

        $reservation->refresh();
        $this->assertNotNull($reservation->ended_at);
    }

    /**
     * Test qu'un admin ne peut pas enlever une place à un utilisateur sans réservation active.
     */
    public function test_admin_cannot_remove_user_reservation_when_user_has_no_active_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);

        $this->actingAs($admin)
            ->from('/admin/utilisateurs')
            ->post('/admin/reservation/remove', [
                'user_id' => $user->id,
            ])
            ->assertSessionHasErrors('reservation_remove');
    }

    /**
     * Test qu'un non-admin ne peut pas forcer une attribution.
     */
    public function test_non_admin_cannot_force_assign(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        /** @var User $otherUser */
        $otherUser = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        $spot = ParkingSpot::factory()->create();

        $this->actingAs($user)
            ->post('/admin/reservation/force', [
                'user_id' => $otherUser->id,
                'parking_spot_id' => $spot->id,
            ])
            ->assertStatus(403);
    }

    /**
     * Test que la validation échoue si user_id est absent.
     */
    public function test_admin_force_assign_requires_user_id(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        $spot = ParkingSpot::factory()->create();

        $this->actingAs($admin)
            ->from('/admin/utilisateurs')
            ->post('/admin/reservation/force', [
                'parking_spot_id' => $spot->id,
            ])
            ->assertSessionHasErrors('user_id');
    }

    /**
     * Test que le message admin est formulé côté tiers quand l'utilisateur ciblé a déjà une réservation.
     */
    public function test_admin_force_assign_uses_admin_wording_when_target_has_active_reservation(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        $spot = ParkingSpot::factory()->create();

        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
            'ended_at' => null,
            'expires_at' => now()->addHours(2),
        ]);

        $this->actingAs($admin)
            ->from('/admin/utilisateurs')
            ->post('/admin/reservation/force', [
                'user_id' => $user->id,
            ])
            ->assertSessionHasErrors([
                'reservation' => 'L\'utilisateur a déjà une réservation active.',
            ]);
    }

    /**
     * Test que l'assignation échoue pour un compte non validé.
     */
    public function test_admin_cannot_force_assign_unvalidated_user_on_specific_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'is_validated' => false]);
        $spot = ParkingSpot::factory()->create();

        $this->actingAs($admin)
            ->from('/admin/utilisateurs')
            ->post('/admin/reservation/force', [
                'user_id' => $user->id,
                'parking_spot_id' => $spot->id,
            ])
            ->assertSessionHasErrors('reservation');

        $this->assertDatabaseMissing('reservations', [
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
        ]);
    }

    /**
     * Test qu'après fermeture d'une réservation, le prochain utilisateur en attente reçoit la place.
     */
    public function test_next_waiting_user_gets_spot_after_close(): void
    {
        $spot = ParkingSpot::factory()->create();
        /** @var User $user1 */
        $user1 = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        /** @var User $user2 */
        $user2 = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        // User1 réserve la seule place
        $this->actingAs($user1)->post('/utilisateur/reservation')->assertRedirect();
        $reservation1 = Reservation::where('user_id', $user1->id)->first();
        $this->assertSame($spot->id, $reservation1->parking_spot_id);

        // User2 est mis en attente (pas de place)
        $this->actingAs($user2)->post('/utilisateur/reservation')->assertRedirect();
        $this->assertDatabaseHas('waiting_list_entries', ['user_id' => $user2->id, 'position' => 1]);

        // Admin ferme la réservation de user1
        $this->actingAs($admin)
            ->post("/admin/reservation/{$reservation1->id}/close")
            ->assertRedirect();

        // Vérifier que user2 a maintenant une réservation
        $reservation2 = Reservation::where('user_id', $user2->id)->first();
        $this->assertNotNull($reservation2);
        $this->assertSame($spot->id, $reservation2->parking_spot_id);

        // Vérifier que user2 n'est plus en attente
        $this->assertDatabaseMissing('waiting_list_entries', ['user_id' => $user2->id]);
    }

    /**
     * Test qu'enlever résa retire aussi l'utilisateur de la file d'attente si aucune place active.
     */
    public function test_admin_remove_reservation_action_can_remove_user_from_waiting_list(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);

        WaitingListEntry::factory()->create([
            'user_id' => $user->id,
            'position' => 1,
        ]);

        $this->actingAs($admin)
            ->post('/admin/reservation/remove', [
                'user_id' => $user->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('waiting_list_entries', [
            'user_id' => $user->id,
        ]);
    }
}
