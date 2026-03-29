<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\ParkingSpot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un admin peut accéder à la liste des utilisateurs.
     */
    public function test_admin_can_view_user_list(): void
    {
        /** @var User $admin */
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/utilisateurs')
            ->assertStatus(200)
            ->assertViewIs('admin.userlist');
    }

    /**
     * Test qu'un user ne peut pas accéder à l'admin panel.
     */
    public function test_user_cannot_access_admin_panel(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'user',
            'is_validated' => true,
        ]);

        $this->actingAs($user)
            ->get('/admin/utilisateurs')
            ->assertStatus(403);
    }

    /**
     * Test qu'un admin peut valider un utilisateur.
     */
    public function test_admin_can_validate_user(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'is_validated' => false,
        ]);

        $this->actingAs($admin)
            ->post("/admin/utilisateurs/{$user->id}/validate")
            ->assertStatus(302);

        $user->refresh();
        $this->assertTrue($user->is_validated);
    }

    /**
     * Test qu'un admin peut voir la liste des places.
     */
    public function test_admin_can_view_parking_spots(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        ParkingSpot::factory()->count(5)->create();

        $this->actingAs($admin)
            ->get('/admin/places')
            ->assertStatus(200)
            ->assertViewIs('admin.places');
    }

    /**
     * Test qu'un admin peut créer une nouvelle place.
     */
    public function test_admin_can_create_parking_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        $this->actingAs($admin)
            ->post('/admin/places', [
                'number' => '99',
                'location' => 'Zone A',
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('parking_spots', [
            'number' => '99',
            'location' => 'Zone A',
        ]);
    }

    /**
     * Test qu'un admin peut modifier une place.
     */
    public function test_admin_can_update_parking_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        $spot = ParkingSpot::factory()->create(['number' => '1']);

        $this->actingAs($admin)
            ->post("/admin/places/{$spot->id}", [
                'number' => '1',
                'location' => 'Zone B (updated)',
                '_method' => 'PUT',
            ])
            ->assertStatus(302);

        $spot->refresh();
        $this->assertSame('Zone B (updated)', $spot->location);
    }

    /**
     * Test qu'un admin peut supprimer une place.
     */
    public function test_admin_can_delete_parking_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        $spot = ParkingSpot::factory()->create();

        $this->actingAs($admin)
            ->post("/admin/places/{$spot->id}", [
                '_method' => 'DELETE',
            ])
            ->assertStatus(302);

        $this->assertDatabaseMissing('parking_spots', [
            'id' => $spot->id,
        ]);
    }

    /**
     * Test qu'un admin peut voir la liste d'attente.
     */
    public function test_admin_can_view_waiting_list(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/liste-attente')
            ->assertStatus(200);
    }
}
