<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\ParkingSpot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPlacesManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un admin peut créer une place.
     */
    public function test_admin_can_create_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);

        $this->actingAs($admin)
            ->post('/admin/places', [
                'number' => '10',
                'location' => 'Sector A',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('parking_spots', ['number' => '10']);
    }

    /**
     * Test qu'un admin peut modifier une place.
     */
    public function test_admin_can_update_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        $spot = ParkingSpot::factory()->create(['number' => '5']);

        $this->actingAs($admin)
            ->put("/admin/places/{$spot->id}", [
                'number' => '5',
                'location' => 'New Location',
            ])
            ->assertRedirect();

        $spot->refresh();
        $this->assertSame('New Location', $spot->location);
    }

    /**
     * Test qu'un admin peut supprimer une place.
     */
    public function test_admin_can_delete_spot(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_validated' => true]);
        $spot = ParkingSpot::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/places/{$spot->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('parking_spots', ['id' => $spot->id]);
    }
}
