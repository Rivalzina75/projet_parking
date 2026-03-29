<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\ParkingSpot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un utilisateur peut voir son profil.
     */
    public function test_user_can_view_profile(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $this->actingAs($user)
            ->get('/utilisateur/profil')
            ->assertStatus(200)
            ->assertViewIs('user.profil');
    }

    /**
     * Test que la modification du mot de passe échoue avec mauvais mot de passe actuel.
     */
    public function test_password_update_fails_with_wrong_current_password(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
            'password' => Hash::make('CurrentPassword123!'),
        ]);

        $this->actingAs($user)
            ->post('/utilisateur/profil/password', [
                'current_password' => 'WrongPassword123!',
                'new_password' => 'NewPassword123!',
                'new_password_confirmation' => 'NewPassword123!',
            ])
            ->assertSessionHasErrors();
    }

    /**
     * Test qu'un utilisateur peut faire une demande de réservation.
     */
    public function test_user_can_request_reservation(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        ParkingSpot::factory()->create();

        $this->actingAs($user)
            ->post('/utilisateur/reservation')
            ->assertStatus(302);
    }
}
