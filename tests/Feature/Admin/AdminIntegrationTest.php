<?php

namespace Tests\Feature\Admin;

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
}
