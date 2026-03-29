<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginValidationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que la connexion d'un utilisateur non validé le redirige vers la page d'attente.
     */
    public function test_login_redirects_unvalidated_user_to_pending_page(): void
    {
        $user = User::factory()->create([
            'email' => 'unvalidated@test.local',
            'password' => Hash::make('Password123!'),
            'role' => 'user',
            'is_validated' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('account.pending'));
    }

    /**
     * Test que la connexion d'un utilisateur validé le redirige vers le dashboard user.
     */
    public function test_login_redirects_validated_user_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'validated@test.local',
            'password' => Hash::make('Password123!'),
            'role' => 'user',
            'is_validated' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertRedirect('/utilisateur/dashboard');
    }

    /**
     * Test que la connexion d'un admin le redirige vers la liste des utilisateurs.
     */
    public function test_login_redirects_admin_to_admin_panel(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.local',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'is_validated' => true,
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'Password123!',
        ])->assertRedirect('/admin/utilisateurs');
    }

    /**
     * Test que des identifiants invalides retournent une erreur.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@test.local',
            'password' => Hash::make('Password123!'),
        ]);

        $this->post('/login', [
            'email' => 'test@test.local',
            'password' => 'WrongPassword!',
        ])->assertSessionHasErrors('email');
    }

    /**
     * Test que la déconnexion fonctionne et redirige vers l'accueil.
     */
    public function test_logout_redirects_to_home(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/')
            ->assertSessionHasAll([]);
    }
}
