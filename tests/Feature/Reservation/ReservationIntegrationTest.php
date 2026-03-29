<?php

namespace Tests\Feature\Reservation;

use App\Models\User;
use App\Models\Reservation;
use App\Models\ParkingSpot;
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
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        $reservation = Reservation::factory()->create(['user_id' => $user->id, 'ended_at' => null]);

        $this->actingAs($admin)
            ->post("/admin/reservation/{$reservation->id}/close")
            ->assertRedirect();

        $reservation->refresh();
        $this->assertNotNull($reservation->ended_at);
    }

    /**
     * Test qu'un non-admin ne peut pas forcer une attribution.
     */
    public function test_non_admin_cannot_force_assign(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);
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
     * Test que l'assignation échoue pour un compte non validé.
     */
    public function test_admin_cannot_force_assign_unvalidated_user_on_specific_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
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
}
