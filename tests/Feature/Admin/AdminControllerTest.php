<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\WaitingListEntry;
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
     * Test qu'un admin peut créer un compte utilisateur membre de la ligue.
     */
    public function test_admin_can_create_user_account(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        $this->actingAs($admin)
            ->post('/admin/utilisateurs', [
                'name' => 'Oceane',
                'lastname' => 'Martin',
                'email' => 'oceane.martin@example.com',
                'password' => 'PasswordStrong123!',
                'password_confirmation' => 'PasswordStrong123!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'oceane.martin@example.com',
            'role' => 'user',
            'is_validated' => true,
        ]);
    }

    /**
     * Test que la liste des utilisateurs admin est paginée à 10 et navigable par numéro de page.
     */
    public function test_admin_user_list_is_paginated_by_ten_with_page_navigation(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        for ($index = 1; $index <= 15; $index++) {
            User::factory()->create([
                'role' => 'user',
                'is_validated' => true,
                'name' => 'User' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'lastname' => 'Test' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'email' => "user{$index}@example.com",
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/utilisateurs')
            ->assertStatus(200)
            ->assertSee('id="users-page-input"', false)
            ->assertSee('name="page"', false)
            ->assertViewHas('users', function ($users) {
                return $users->perPage() === 10
                    && $users->currentPage() === 1
                    && $users->lastPage() === 2
                    && $users->count() === 10;
            });

        $this->actingAs($admin)
            ->get('/admin/utilisateurs?page=2')
            ->assertStatus(200)
            ->assertViewHas('users', function ($users) {
                return $users->perPage() === 10
                    && $users->currentPage() === 2
                    && $users->lastPage() === 2
                    && $users->count() === 5;
            });
    }

    /**
     * Test que la liste admin des utilisateurs affiche uniquement les comptes role=user.
     */
    public function test_admin_user_list_only_shows_role_user_accounts(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'name' => 'Super',
            'lastname' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'is_validated' => true,
        ]);

        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Jean',
            'lastname' => 'Dupont',
            'email' => 'jean@example.com',
            'role' => 'user',
            'is_validated' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/utilisateurs')
            ->assertStatus(200)
            ->assertSee($user->name, false)
            ->assertSee($user->email, false)
            ->assertDontSee('admin@example.com', false)
            ->assertDontSee('Super Admin', false)
            ->assertDontSee('Réinit. mdp', false);
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

        /** @var User $user */
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
     * Test que la liste des places est paginée à 10 et navigable par numéro de page.
     */
    public function test_admin_places_list_is_paginated_by_ten_with_page_navigation(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        for ($index = 1; $index <= 15; $index++) {
            ParkingSpot::factory()->create([
                'number' => 'P-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'location' => 'Bâtiment A',
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/places')
            ->assertStatus(200)
            ->assertSee('id="places-page-input"', false)
            ->assertSee('name="page"', false)
            ->assertViewHas('spots', function ($spots) {
                return $spots->perPage() === 10
                    && $spots->currentPage() === 1
                    && $spots->lastPage() === 2
                    && $spots->count() === 10;
            });

        $this->actingAs($admin)
            ->get('/admin/places?page=2')
            ->assertStatus(200)
            ->assertViewHas('spots', function ($spots) {
                return $spots->perPage() === 10
                    && $spots->currentPage() === 2
                    && $spots->lastPage() === 2
                    && $spots->count() === 5;
            });
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

    /**
     * Test que la file d'attente admin est paginée à 10 et dispose du saut par numéro de page.
     */
    public function test_admin_waiting_list_is_paginated_by_ten_with_page_navigation(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        for ($index = 1; $index <= 15; $index++) {
            $user = User::factory()->create([
                'role' => 'user',
                'is_validated' => true,
                'email' => "waiting{$index}@example.com",
            ]);

            WaitingListEntry::factory()->create([
                'user_id' => $user->id,
                'position' => $index,
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/liste-attente')
            ->assertStatus(200)
            ->assertSee('id="waiting-page-input"', false)
            ->assertViewHas('waiting', function ($waiting) {
                return $waiting->perPage() === 10
                    && $waiting->currentPage() === 1
                    && $waiting->lastPage() === 2
                    && $waiting->count() === 10;
            });

        $this->actingAs($admin)
            ->get('/admin/liste-attente?page=2')
            ->assertStatus(200)
            ->assertViewHas('waiting', function ($waiting) {
                return $waiting->currentPage() === 2
                    && $waiting->count() === 5;
            });
    }

    /**
     * Test qu'un admin peut consulter le contenu de la liste d'attente.
     */
    public function test_admin_can_view_waiting_list_entries_content(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        /** @var User $waitingUser */
        $waitingUser = User::factory()->create([
            'role' => 'user',
            'is_validated' => true,
            'name' => 'Jean',
            'lastname' => 'Martin',
        ]);

        WaitingListEntry::factory()->create([
            'user_id' => $waitingUser->id,
            'position' => 2,
        ]);

        $this->actingAs($admin)
            ->get('/admin/liste-attente')
            ->assertStatus(200)
            ->assertViewIs('admin.waiting_list')
            ->assertSee('Jean', false)
            ->assertSee('Martin', false)
            ->assertSee('#2', false);
    }

    /**
     * Test qu'un admin peut modifier la position d'une personne en file d'attente.
     */
    public function test_admin_can_move_waiting_list_position(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        /** @var User $user1 */
        $user1 = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        /** @var User $user2 */
        $user2 = User::factory()->create(['role' => 'user', 'is_validated' => true]);
        /** @var User $user3 */
        $user3 = User::factory()->create(['role' => 'user', 'is_validated' => true]);

        $entry1 = WaitingListEntry::factory()->create(['user_id' => $user1->id, 'position' => 1]);
        $entry2 = WaitingListEntry::factory()->create(['user_id' => $user2->id, 'position' => 2]);
        $entry3 = WaitingListEntry::factory()->create(['user_id' => $user3->id, 'position' => 3]);

        $this->actingAs($admin)
            ->post("/admin/liste-attente/{$entry3->id}/move", [
                'position' => 1,
            ])
            ->assertStatus(302);

        $entry1->refresh();
        $entry2->refresh();
        $entry3->refresh();

        $this->assertSame(2, $entry1->position);
        $this->assertSame(3, $entry2->position);
        $this->assertSame(1, $entry3->position);
    }

    /**
     * Test qu'un admin peut consulter l'historique d'attribution d'un utilisateur.
     */
    public function test_admin_can_view_user_allocation_history(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'user',
            'is_validated' => true,
        ]);

        $spot = ParkingSpot::factory()->create(['number' => 'A-42']);

        Reservation::factory()->closed()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
        ]);

        $this->actingAs($admin)
            ->get("/admin/utilisateurs/{$user->id}")
            ->assertStatus(200)
            ->assertViewIs('admin.user_instance')
            ->assertSee('A-42', false);
    }

    /**
     * Test qu'un admin peut attribuer manuellement une place précise.
     */
    public function test_admin_can_assign_specific_place_to_user(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'user',
            'is_validated' => true,
        ]);

        $spot = ParkingSpot::factory()->create();

        $this->actingAs($admin)
            ->post('/admin/places/assign', [
                'user_id' => $user->id,
                'spot_id' => $spot->id,
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
        ]);
    }

    /**
     * Test que la page places expose bien les utilisateurs pour un filtrage instantané côté front.
     */
    public function test_admin_places_page_exposes_users_for_live_manual_assignment_search(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        ParkingSpot::factory()->create(['number' => 'A-1']);

        /** @var User $match */
        $match = User::factory()->create([
            'name' => 'Martin',
            'lastname' => 'Dupond',
            'role' => 'user',
            'is_validated' => true,
        ]);

        /** @var User $other */
        $other = User::factory()->create([
            'name' => 'Claire',
            'lastname' => 'Bernard',
            'role' => 'user',
            'is_validated' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/places')
            ->assertStatus(200)
            ->assertSee("{$match->name} {$match->lastname}", false)
            ->assertSee("{$other->name} {$other->lastname}", false);
    }

    /**
     * Test qu'un admin peut consulter l'historique complet d'une place.
     */
    public function test_admin_can_view_full_history_for_a_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        $spot = ParkingSpot::factory()->create(['number' => 'B-12']);
        /** @var User $user1 */
        $user1 = User::factory()->create(['role' => 'user', 'is_validated' => true, 'name' => 'Luc']);
        /** @var User $user2 */
        $user2 = User::factory()->create(['role' => 'user', 'is_validated' => true, 'name' => 'Nina']);

        Reservation::factory()->closed()->create([
            'user_id' => $user1->id,
            'parking_spot_id' => $spot->id,
        ]);

        Reservation::factory()->closed()->create([
            'user_id' => $user2->id,
            'parking_spot_id' => $spot->id,
        ]);

        $this->actingAs($admin)
            ->get("/admin/places/{$spot->id}/historique")
            ->assertStatus(200)
            ->assertViewIs('admin.spot_history')
            ->assertSee('B-12', false)
            ->assertSee('Luc', false)
            ->assertSee('Nina', false);
    }

    /**
     * Test qu'un admin ne peut pas attribuer une place à un compte non validé.
     */
    public function test_admin_cannot_assign_place_to_unvalidated_user(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'user',
            'is_validated' => false,
        ]);

        $spot = ParkingSpot::factory()->create();

        $this->actingAs($admin)
            ->from('/admin/places')
            ->post('/admin/places/assign', [
                'user_id' => $user->id,
                'spot_id' => $spot->id,
            ])
            ->assertSessionHasErrors('assign');

        $this->assertDatabaseMissing('reservations', [
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
        ]);
    }

    /**
     * Test qu'un admin ne peut pas attribuer une place déjà occupée.
     */
    public function test_admin_cannot_assign_already_occupied_place(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_validated' => true,
        ]);

        /** @var User $occupiedBy */
        $occupiedBy = User::factory()->create([
            'role' => 'user',
            'is_validated' => true,
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'role' => 'user',
            'is_validated' => true,
        ]);

        $spot = ParkingSpot::factory()->create();

        Reservation::factory()->create([
            'user_id' => $occupiedBy->id,
            'parking_spot_id' => $spot->id,
            'ended_at' => null,
            'expires_at' => now()->addHours(2),
        ]);

        $this->actingAs($admin)
            ->from('/admin/places')
            ->post('/admin/places/assign', [
                'user_id' => $targetUser->id,
                'spot_id' => $spot->id,
            ])
            ->assertSessionHasErrors('assign');

        $this->assertDatabaseMissing('reservations', [
            'user_id' => $targetUser->id,
            'parking_spot_id' => $spot->id,
        ]);
    }
}
