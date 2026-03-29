<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Models\ParkingSpot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InputValidationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que les noms de champs inattendus sont ignorés (mass assignment protection).
     */
    public function test_register_rejects_unexpected_fields(): void
    {
        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'admin',  // Tentative d'injection
            'is_validated' => true,  // Tentative d'injection
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);  // role ne doit pas être changé
        $this->assertFalse($user->is_validated);  // is_validated ne doit pas être changé
    }

    /**
     * Test que les données vides sont rejetées.
     */
    public function test_register_rejects_empty_fields(): void
    {
        $this->post('/inscription', [
            'name' => '',
            'lastname' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors(['name', 'lastname', 'email', 'password']);
    }

    /**
     * Test que les emails invalides sont rejetés.
     */
    public function test_register_rejects_invalid_email(): void
    {
        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => 'not-an-email',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertSessionHasErrors('email');

        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => 'test@',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertSessionHasErrors('email');
    }

    /**
     * Test que les passwords faibles sont rejetés.
     */
    public function test_register_rejects_weak_passwords(): void
    {
        // Pas de minuscule
        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => 'test1@example.com',
            'password' => 'SECUREPASS123!',
            'password_confirmation' => 'SECUREPASS123!',
        ])->assertSessionHasErrors('password');

        // Pas de majuscule
        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => 'test2@example.com',
            'password' => 'securepass123!',
            'password_confirmation' => 'securepass123!',
        ])->assertSessionHasErrors('password');

        // Pas de chiffre
        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => 'test3@example.com',
            'password' => 'SecurePass!',
            'password_confirmation' => 'SecurePass!',
        ])->assertSessionHasErrors('password');

        // Pas de symbole spécial
        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => 'test4@example.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ])->assertSessionHasErrors('password');

        // Moins de 10 caractères
        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => 'test5@example.com',
            'password' => 'Pass1!',
            'password_confirmation' => 'Pass1!',
        ])->assertSessionHasErrors('password');
    }

    /**
     * Test que les mots de passe non confirmés sont rejetés.
     */
    public function test_register_rejects_unmatched_passwords(): void
    {
        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass123!',
        ])->assertSessionHasErrors('password');
    }

    /**
     * Test que les emails en doublon sont rejetés.
     */
    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => 'duplicate@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertSessionHasErrors('email');
    }

    /**
     * Test que les noms/prénoms trop longs sont rejetés.
     */
    public function test_register_rejects_too_long_names(): void
    {
        $longName = str_repeat('a', 31);

        $this->post('/inscription', [
            'name' => $longName,
            'lastname' => 'User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertSessionHasErrors('name');

        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => $longName,
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertSessionHasErrors('lastname');
    }

    /**
     * Test que les places ne peuvent pas être créées avec des numéros en doublon.
     */
    public function test_admin_cannot_create_duplicate_spot_number(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        ParkingSpot::factory()->create(['number' => 'P-1']);

        $this->actingAs($admin)
            ->post('/admin/places', [
                'number' => 'P-1',
                'location' => 'Bâtiment A',
            ])
            ->assertSessionHasErrors('number');

        $this->assertCount(1, ParkingSpot::where('number', 'P-1')->get());
    }

    /**
     * Test que les numéros de place trop longs sont rejetés.
     */
    public function test_admin_cannot_create_spot_with_too_long_number(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        $longNumber = str_repeat('P', 31);

        $this->actingAs($admin)
            ->post('/admin/places', [
                'number' => $longNumber,
                'location' => 'Bâtiment A',
            ])
            ->assertSessionHasErrors('number');
    }

    /**
     * Test que les mots de passe utilisateur cambient correctement avec validation.
     */
    public function test_password_update_requires_strong_new_password(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'password' => bcrypt('OldPassword123!'),
        ]);

        $this->actingAs($user)
            ->post('/utilisateur/profil/password', [
                'current_password' => 'OldPassword123!',
                'password' => 'weak',  // Trop faible
                'password_confirmation' => 'weak',
            ])
            ->assertSessionHasErrors('password');
    }

    /**
     * Test que les données de réservation admin rejectent les IDs invalides.
     */
    public function test_admin_force_assign_rejects_invalid_user_id(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        $spot = ParkingSpot::factory()->create();

        $this->actingAs($admin)
            ->post('/admin/reservation/force', [
                'user_id' => 99999,  // ID inexistant
                'parking_spot_id' => $spot->id,
            ])
            ->assertSessionHasErrors('user_id');
    }

    /**
     * Test que la durée par défaut a des limites.
     */
    public function test_admin_default_duration_respects_boundaries(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        $this->actingAs($admin)
            ->from('/admin/places')
            ->post('/admin/settings', [
                'default_reservation_hours' => 'invalid',
            ])
            ->assertSessionHasErrors('default_reservation_hours');

        $this->actingAs($admin)
            ->from('/admin/places')
            ->post('/admin/settings', [
                'default_reservation_hours' => 0,
            ])
            ->assertSessionHasErrors('default_reservation_hours');

        $this->actingAs($admin)
            ->from('/admin/places')
            ->post('/admin/settings', [
                'default_reservation_hours' => 241,
            ])
            ->assertSessionHasErrors('default_reservation_hours');
    }
}
