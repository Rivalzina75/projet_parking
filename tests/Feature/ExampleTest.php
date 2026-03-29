<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test que la home non connectée propose l'inscription/connexion.
     */
    public function test_home_for_guest_shows_auth_cta(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Demander une place', false)
            ->assertSee('Se connecter', false);
    }

    /**
     * Test que la home connectée affiche un message de bienvenue adapté.
     */
    public function test_home_for_authenticated_user_shows_welcome_and_dashboard_link(): void
    {
        $user = \App\Models\User::factory()->create([
            'role' => 'user',
            'is_validated' => true,
            'name' => 'Nina',
        ]);

        $this->actingAs($user)
            ->get('/')
            ->assertStatus(200)
            ->assertSee('Bienvenue Nina', false)
            ->assertSee('Aller à mon dashboard', false)
            ->assertDontSee('Se connecter', false);
    }
}
