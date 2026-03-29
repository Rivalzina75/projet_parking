<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un utilisateur peut voir le formulaire de réinitialisation de mot de passe.
     */
    public function test_user_can_view_reset_form(): void
    {
        $this->get('/reset-password')
            ->assertStatus(200);
    }

    /**
     * Test qu'un utilisateur peut demander une réinitialisation de mot de passe.
     */
    public function test_user_can_request_password_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'test@test.local',
        ]);

        $this->post('/reset-password', [
            'email' => $user->email,
        ])
            ->assertStatus(200);
    }

    /**
     * Test que la demande échoue avec une adresse email inexistante.
     */
    public function test_password_reset_fails_with_invalid_email(): void
    {
        $this->post('/reset-password', [
            'email' => 'nonexistent@test.local',
        ])
            ->assertSessionHasErrors('email');
    }
}
