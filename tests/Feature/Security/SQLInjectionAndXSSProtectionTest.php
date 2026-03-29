<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SQLInjectionAndXSSProtectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que les injections SQL dans les noms ne créent pas de compte admin.
     */
    public function test_register_prevents_sql_injection_in_name(): void
    {
        $this->post('/inscription', [
            'name' => "'; DROP TABLE users; --",
            'lastname' => 'User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        // La table users doit toujours exister
        $this->assertTrue(DB::table('users')->count() > 0);

        // L'utilisateur doit avoir son nom tel qu'entré
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame("'; DROP TABLE users; --", $user->name);
    }

    /**
     * Test que les injections SQL dans l'email ne sont pas échappées.
     */
    public function test_register_prevents_sql_injection_in_email(): void
    {
        $this->post('/inscription', [
            'name' => 'Test',
            'lastname' => 'User',
            'email' => "test' OR '1'='1",
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertSessionHasErrors('email');  // L'email est invalide
    }

    /**
     * Test que les XSS dans les noms sont échappés à l'affichage.
     */
    public function test_xss_protection_in_user_names(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => "<script>alert('XSS')</script>",
            'is_validated' => true,
        ]);

        $response = $this->actingAs($user)
            ->get('/utilisateur/profil')
            ->assertStatus(200);

        // Le script ne doit pas être exécutable dans le contenu
        $response->assertDontSee("<script>alert('XSS')</script>", false);
    }

    /**
     * Test que les XSS dans les localisations de places sont échappés.
     */
    public function test_xss_protection_in_spot_locations(): void
    {
        $spot = ParkingSpot::factory()->create([
            'location' => '<img src=x onerror="alert(\'XSS\')">',
        ]);

        $user = User::factory()->create(['is_validated' => true]);
        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/utilisateur/dashboard')
            ->assertStatus(200);

        // Vérification que les caractères HTML dangereux sont échappés
        $content = $response->getContent();
        // Si l'attribut onerror n'est pas exécutable, c'est que c'est safe
        // Blade échappe automatiquement avec {{ }}
        $this->assertStringContainsString('&lt;img', $content);
        $this->assertStringNotContainsString('<img src=x onerror=', $content);
    }

    /**
     * Test que les données du formulaire de place admin sont validées et échappées.
     */
    public function test_admin_spot_creation_escapes_special_characters(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        $this->actingAs($admin)
            ->post('/admin/places', [
                'number' => 'P-1',
                'location' => '<script>alert("XSS")</script>',
            ])
            ->assertRedirect();

        $spot = ParkingSpot::where('number', 'P-1')->first();
        $this->assertNotNull($spot);
        $this->assertSame('<script>alert("XSS")</script>', $spot->location);
    }

    /**
     * Test que les paramètres d'URL ne peuvent pas être utilisés pour une injection.
     */
    public function test_url_parameters_are_safe(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        /** @var User $targetUser */
        $targetUser = User::factory()->create(['role' => 'user', 'is_validated' => true, 'name' => 'Alice']);

        // La route model-binding doit ignorer l'injection dans la query string
        $this->actingAs($admin)
            ->get("/admin/utilisateurs/{$targetUser->id}?id=1 OR 1=1")
            ->assertStatus(200)
            ->assertSee('Alice', false);
    }

    /**
     * Test que les données de waiting list ne contiennent pas de scripts.
     */
    public function test_waiting_list_display_escapes_xss(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => '<b>Exploit</b>',
            'lastname' => '">< script>alert(1)</ script>',
            'is_validated' => true,
        ]);

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        \App\Models\WaitingListEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)
            ->get('/admin/liste-attente')
            ->assertStatus(200);

        $response->assertDontSee('"><script>', false);
    }

    /**
     * Test que les routes sensibles sont protégées contre les invités.
     */
    public function test_guest_cannot_post_to_protected_routes(): void
    {
        $this->post('/utilisateur/reservation')
            ->assertRedirect('/login');

        $this->post('/admin/reservation/force', [
            'user_id' => 1,
        ])->assertRedirect('/login');
    }

    /**
     * Test que les données échappées sont stockées correctement dans la base.
     */
    public function test_escaped_data_stored_correctly(): void
    {
        $testName = "Test & <Special> \"Chars\"";

        $this->post('/inscription', [
            'name' => $testName,
            'lastname' => 'User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertSame($testName, $user->name);  // Les données brutes sont stockées
    }

    /**
     * Test que la modification du mot de passe valide l'ancien mot de passe.
     */
    public function test_password_change_requires_correct_old_password(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_validated' => true,
            'password' => bcrypt('CorrectPass123!'),
        ]);

        $response = $this->actingAs($user)
            ->post('/utilisateur/profil/password', [
                'current_password' => 'WrongPass123!',
                'password' => 'NewPass123!',
                'password_confirmation' => 'NewPass123!',
            ]);

        $response->assertSessionHasErrors('current_password');

        // Le mot de passe ne doit pas avoir changé
        $user->refresh();
        $this->assertTrue(Hash::check('CorrectPass123!', $user->password));
    }

    /**
     * Test que les accès aux ressources d'autres utilisateurs sont bloqués.
     */
    public function test_users_cannot_access_other_users_data(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create(['is_validated' => true]);
        /** @var User $user2 */
        $user2 = User::factory()->create(['is_validated' => true]);

        $spot = ParkingSpot::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user1->id,
            'parking_spot_id' => $spot->id,
        ]);

        // User2 ne peut pas fermer la réservation de user1
        $response = $this->actingAs($user2)
            ->post("/utilisateur/reservation/{$reservation->id}/close");

        $this->assertSame(403, $response->status());

        // La réservation ne doit pas être fermée
        $reservation->refresh();
        $this->assertNull($reservation->ended_at);
    }
}
