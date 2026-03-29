<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidatedUserMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que le middleware laisse passer les utilisateurs validés.
     */
    public function test_validated_user_can_pass(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['is_validated' => true]);

        $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertStatus(200);
    }

    /**
     * Test que le middleware bloque les utilisateurs non validés.
     */
    public function test_unvalidated_user_is_blocked(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['is_validated' => false]);

        $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertRedirect(route('account.pending'));
    }
}
