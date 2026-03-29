<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnvalidatedAccountAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un compte non validé est redirigé vers la page d'attente.
     */
    public function test_unvalidated_user_is_blocked_from_dashboard(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'user',
            'is_validated' => false,
        ]);

        $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertRedirect(route('account.pending'));
    }

    /**
     * Test qu'un compte validé peut accéder à son dashboard.
     */
    public function test_validated_user_can_access_dashboard(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'user',
            'is_validated' => true,
        ]);

        $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertStatus(200);
    }

    /**
     * Test qu'un compte non validé peut accéder à la page d'attente.
     */
    public function test_unvalidated_user_can_access_pending_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'user',
            'is_validated' => false,
        ]);

        $this->actingAs($user)
            ->get(route('account.pending'))
            ->assertStatus(200);
    }

    /**
     * Test qu'un compte non validé peut se déconnecter.
     */
    public function test_unvalidated_user_can_logout(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'user',
            'is_validated' => false,
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');
    }

    /**
     * Test qu'un utilisateur non authentifié peut voir le formulaire de connexion.
     */
    public function test_guest_can_view_login_form(): void
    {
        $this->get('/login')
            ->assertStatus(200);
    }
}
