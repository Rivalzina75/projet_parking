<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que les passwords doivent matcher.
     */
    public function test_passwords_must_match_on_register(): void
    {
        $this->post('/inscription', [
            'name' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@test.local',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
        ])
            ->assertSessionHasErrors('password');
    }

    /**
     * Test que email dupliqué échoue.
     */
    public function test_duplicate_email_fails(): void
    {
        User::factory()->create(['email' => 'test@test.local']);

        $this->post('/inscription', [
            'name' => 'John',
            'lastname' => 'Doe',
            'email' => 'test@test.local',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertSessionHasErrors('email');
    }

    /**
     * Test password reset avec email valide.
     */
    public function test_password_reset_request_succeeds(): void
    {
        $user = User::factory()->create(['email' => 'test@test.local']);

        $this->post('/reset-password', [
            'email' => $user->email,
        ])
            ->assertStatus(200);
    }

    /**
     * Test password reset avec email invalide.
     */
    public function test_password_reset_fails_with_invalid_email(): void
    {
        $this->post('/reset-password', [
            'email' => 'nonexistent@test.local',
        ])
            ->assertSessionHasErrors('email');
    }
}
