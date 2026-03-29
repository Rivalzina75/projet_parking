<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un visiteur peut voir le formulaire d'inscription.
     */
    public function test_guest_can_view_register_form(): void
    {
        $this->get('/inscription')
            ->assertStatus(200);
    }

    /**
     * Test qu'un utilisateur peut s'inscrire.
     */
    public function test_user_can_register(): void
    {
        $this->post('/inscription', [
            'name' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@test.local',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@test.local',
            'is_validated' => false,
        ]);
    }

    /**
     * Test que l'enregistrement avec email dupliqué échoue.
     */
    public function test_register_fails_with_duplicate_email(): void
    {
        $this->post('/inscription', [
            'name' => 'John',
            'lastname' => 'Doe',
            'email' => 'test@test.local',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->post('/inscription', [
            'name' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'test@test.local',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertSessionHasErrors('email');
    }

    /**
     * Test que l'enregistrement échoue sans confirmation du mot de passe.
     */
    public function test_register_fails_without_password_confirmation(): void
    {
        $this->post('/inscription', [
            'name' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@test.local',
            'password' => 'Password123!',
        ])
            ->assertSessionHasErrors('password');
    }
}
