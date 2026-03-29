<?php

namespace Tests\Feature\Admin;

use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Sequence;
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
        /** @var User $user */
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
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'is_validated' => true]);

        $this->actingAs($admin)
            ->get("/admin/utilisateurs/{$user->id}")
            ->assertStatus(200);
    }

    /**
     * Test que les settings admin fonctionnent.
     * BUGFIX: la clé correcte est 'default_reservation_hours' (pas 'default_duration_hours').
     */
    public function test_admin_can_update_settings(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        $this->actingAs($admin)
            ->post('/admin/settings', [
                'default_reservation_hours' => '72',
            ])
            ->assertStatus(302);
    }

    /**
     * Test qu'un admin peut accéder à la page paramètres.
     */
    public function test_admin_can_view_settings_page(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        $this->actingAs($admin)
            ->get('/admin/parametres')
            ->assertStatus(200)
            ->assertViewIs('admin.settings');
    }

    /**
     * Test que l'interface admin rend la modale de consentement UX.
     */
    public function test_admin_layout_renders_consent_modal_for_double_confirmation(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        $this->actingAs($admin)
            ->get('/admin/utilisateurs')
            ->assertStatus(200)
            ->assertSee('id="consent-modal"', false)
            ->assertSee('id="consent-confirm"', false)
            ->assertSee('id="consent-cancel"', false);
    }

    /**
     * Test qu'il n'y a qu'une déconnexion côté admin (dans la sidebar) et pas dans le header.
     */
    public function test_admin_has_single_sidebar_logout_button(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        $this->actingAs($admin)
            ->get('/admin/utilisateurs')
            ->assertStatus(200)
            ->assertSee('class="sb-logout"', false)
            ->assertDontSee('class="logout-form"', false);
    }

    /**
     * Test qu'un admin peut activer le double consentement.
     */
    public function test_admin_can_enable_double_consent_setting(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        $this->actingAs($admin)
            ->post('/admin/settings', [
                'settings_toggle' => '1',
                'double_consent_enabled' => '1',
            ])
            ->assertStatus(302);

        $this->assertTrue((bool) DB::table('app_settings')->value('double_consent_enabled'));
    }

    /**
     * Test que modifier la durée par défaut affecte les futures réservations.
     * BUGFIX: clé corrigée 'default_reservation_hours'.
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

        // Fermer la première réservation
        $this->actingAs($user1)
            ->post("/utilisateur/reservation/{$reservation1->id}/close")
            ->assertRedirect();

        // Deuxième réservation avec nouvelle durée
        $this->actingAs($user2)->post('/utilisateur/reservation')->assertRedirect();
        $reservation2 = Reservation::where('user_id', $user2->id)->first();
        $duration2 = (int) abs($reservation2->expires_at->diffInHours($reservation2->starts_at));
        $this->assertSame(72, $duration2);
    }
}
