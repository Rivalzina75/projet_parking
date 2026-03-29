<?php

namespace Tests\Feature\Admin;

use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un admin peut réinitialiser le mot de passe d'un utilisateur.
     */
    public function test_admin_can_reset_user_password(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);

        $this->actingAs($admin)
            ->post("/admin/utilisateurs/{$user->id}/reset-password")
            ->assertRedirect();
    }

    /**
     * Test qu'un admin peut voir les détails d'un utilisateur.
     */
    public function test_admin_can_view_user_detail(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);

        $this->actingAs($admin)
            ->get("/admin/utilisateurs/{$user->id}")
            ->assertStatus(200);
    }

    /**
     * Test que les settings admin fonctionnent.
     */
    public function test_admin_can_update_settings(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        $this->actingAs($admin)
            ->post('/admin/settings', [
                'default_duration_hours' => '72',
            ])
            ->assertStatus(302);
    }

    /**
     * Test que modifier la durée par défaut affecte les futures réservations.
     */
    public function test_changing_default_duration_affects_new_reservations(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        /** @var User $user1 */
        $user1 = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        /** @var User $user2 */
        $user2 = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        ParkingSpot::factory()->count(2)->create();

        // Première réservation avec durée par défaut (8h)
        $this->actingAs($user1)->post('/utilisateur/reservation')->assertRedirect();
        $reservation1 = Reservation::where('user_id', $user1->id)->first();
        $duration1 = (int) abs($reservation1->expires_at->diffInHours($reservation1->starts_at));
        $this->assertSame(8, $duration1);

        // Admin change la durée à 72h
        $this->actingAs($admin)
            ->post('/admin/settings', ['default_reservation_hours' => '72'])
            ->assertRedirect();

        // Deuxième réservation avec nouvelle durée
        // (On ferme la première pour que user2 puisse réserver)
        $this->actingAs($user1)
            ->post("/utilisateur/reservation/{$reservation1->id}/close")
            ->assertRedirect();

        $this->actingAs($user2)->post('/utilisateur/reservation')->assertRedirect();
        $reservation2 = Reservation::where('user_id', $user2->id)->first();
        $duration2 = (int) abs($reservation2->expires_at->diffInHours($reservation2->starts_at));
        $this->assertSame(72, $duration2);
    }
}
