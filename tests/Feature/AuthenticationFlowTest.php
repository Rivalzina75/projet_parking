<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test le flux complet: inscription → non validé → validation admin → accès.
     */
    public function test_complete_authentication_flow(): void
    {
        // 1. Nouvel utilisateur s'inscrit
        $this->post('/inscription', [
            'name' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@test.local',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect('/');

        // 2. Essayer de se connecter → est redirigé vers page d'attente
        $this->post('/login', [
            'email' => 'john@test.local',
            'password' => 'Password123!',
        ])->assertRedirect(route('account.pending'));

        // 3. Admin valide le compte
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        $user = User::where('email', 'john@test.local')->first();

        $this->actingAs($admin)
            ->post("/admin/utilisateurs/{$user->id}/validate")
            ->assertRedirect();

        // 4. User connecté → accès
        $user->refresh();
        $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertStatus(200);
    }

    /**
     * Test que le logout fonctionne.
     */
    public function test_logout_works(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['is_validated' => true]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');
    }

    /**
     * Test le refus d'accès sans authentification.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->get('/utilisateur/dashboard')
            ->assertRedirect('/login');
    }
}
