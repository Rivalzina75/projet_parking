<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasswordResetAndHashingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que les mots de passe sont hashés avec bcrypt.
     */
    public function test_passwords_are_hashed_with_bcrypt(): void
    {
        $plainPassword = 'SecurePass123!';

        $user = User::factory()->create([
            'password' => Hash::make($plainPassword),
        ]);

        // Le hash en base doit commencer par $2y$ (bcrypt)
        $this->assertTrue(str_starts_with($user->password, '$2y$'));

        // Hash::check doit valider le password clair
        $this->assertTrue(Hash::check($plainPassword, $user->password));

        // Un password différent ne doit pas valider
        $this->assertFalse(Hash::check('WrongPassword123!', $user->password));

        // Un password identique créé deux fois ne doit pas avoir le même hash (salt aléatoire)
        $hash1 = Hash::make($plainPassword);
        $hash2 = Hash::make($plainPassword);
        $this->assertNotSame($hash1, $hash2);
        $this->assertTrue(Hash::check($plainPassword, $hash1));
        $this->assertTrue(Hash::check($plainPassword, $hash2));
    }

    /**
     * Test le flux complet de "mot de passe oublié".
     */
    public function test_complete_password_reset_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        // 1. Afficher le formulaire "Mot de passe oublié"
        $this->get('/reset-password')
            ->assertStatus(200)
            ->assertSee('reset-password');

        // 2. Demander la réinitialisation
        $response = $this->post('/reset-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);

        // 3. Vérifier qu'un token a été créé en base
        $resetEntry = DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->first();

        $this->assertNotNull($resetEntry);
        $this->assertNotEmpty($resetEntry->token);

        // 4. Le token doit être haché (commence par $2y$)
        $this->assertTrue(str_starts_with($resetEntry->token, '$2y$'));

        // 5. Extraire le token de l'affichage (view contient le token en clair)
        $viewData = $response->viewData();
        $token = $viewData['token'] ?? null;
        $this->assertNotNull($token);

        // 6. Le token en clair ne doit pas être la même chose que le hash
        $this->assertNotSame($token, $resetEntry->token);
        $this->assertTrue(Hash::check($token, $resetEntry->token));

        // 7. Réinitialiser le mot de passe avec le nouveau mot de passe
        $this->post('/reset-password/confirm', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ])->assertRedirect('/login');

        // 8. Vérifier que l'ancien password ne fonctionne plus
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'OldPassword123!',
        ])->assertSessionHasErrors();

        // 9. Vérifier que le nouveau password fonctionne
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'NewPassword456!',
        ])->assertRedirect('/utilisateur/dashboard');

        // 10. Vérifier que le token a été supprimé après usage
        $resetEntry = DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->first();

        $this->assertNull($resetEntry);

        // 11. Vérifier que le nouveau password est haché en base
        $user->refresh();
        $this->assertTrue(str_starts_with($user->password, '$2y$'));
        $this->assertTrue(Hash::check('NewPassword456!', $user->password));
    }

    /**
     * Test que les tokens invalides sont rejetés.
     */
    public function test_invalid_reset_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post('/reset-password/confirm', [
            'email' => 'test@example.com',
            'token' => 'invalid_token_12345',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ])->assertSessionHasErrors('email');

        // Le mot de passe ne doit pas avoir changé
        $user->refresh();
        $this->assertFalse(Hash::check('NewPassword456!', $user->password));
    }

    /**
     * Test que les tokens avec hash incorrect sont rejetés.
     */
    public function test_tampered_reset_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $validToken = 'valid_token_123456';
        $tamperedToken = 'tampered_token_789012';

        // Créer un token valide hashé
        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($validToken),
            'created_at' => now(),
        ]);

        // Tentative avec un token modifié
        $this->post('/reset-password/confirm', [
            'email' => 'test@example.com',
            'token' => $tamperedToken,
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ])->assertSessionHasErrors('email');

        // Le mot de passe ne doit pas avoir changé
        $user->refresh();
        $this->assertFalse(Hash::check('NewPassword456!', $user->password));
    }

    /**
     * Test que les tokens peuvent être utilisés une seule fois.
     */
    public function test_reset_token_can_only_be_used_once(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        // Créer un token
        $token = 'test_token_single_use';
        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Première utilisation: succès
        $this->post('/reset-password/confirm', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'FirstReset123!',
            'password_confirmation' => 'FirstReset123!',
        ])->assertRedirect('/login');

        // Vérifier que le token a été supprimé
        $this->assertNull(DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->first());

        // Deuxième tentative avec le même token: échoue
        $this->post('/reset-password/confirm', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'SecondReset456!',
            'password_confirmation' => 'SecondReset456!',
        ])->assertSessionHasErrors('email');

        // Le mot de passe doit rester le premier reset, pas le second
        $user->refresh();
        $this->assertTrue(Hash::check('FirstReset123!', $user->password));
        $this->assertFalse(Hash::check('SecondReset456!', $user->password));
    }

    /**
     * Test que les réinitialisations de mot de passe valident la force du nouveau password.
     */
    public function test_reset_password_requires_strong_password(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $token = 'test_token_strength';
        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Tentative avec mot de passe faible
        $this->post('/reset-password/confirm', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors('password');

        // Le token ne doit pas avoir été consommé  
        $this->assertNotNull(DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->first());
    }

    /**
     * Test que les résets de password admin utilisent aussi le hachage.
     */
    public function test_admin_reset_passwords_are_hashed(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($admin)
            ->post("/admin/utilisateurs/{$user->id}/reset-password")
            ->assertRedirect();

        // Un nouveau mot de passe doit avoir été généré et haché
        $user->refresh();

        // Le hash doit être un bcrypt valide
        $this->assertTrue(str_starts_with($user->password, '$2y$'));

        // L'ancien password ne doit pas fonctionner
        $this->assertFalse(Hash::check('OldPassword123!', $user->password));
    }
}
