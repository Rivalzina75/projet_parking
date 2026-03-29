<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Reservation;
use App\Models\WaitingListEntry;
use App\Models\ParkingSpot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un user a beaucoup de réservations.
     */
    public function test_user_has_many_reservations(): void
    {
        $user = User::factory()->create();
        $spot = ParkingSpot::factory()->create();

        Reservation::factory()->count(3)->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
        ]);

        $this->assertCount(3, $user->reservations);
    }

    /**
     * Test qu'un user peut avoir une entrée en liste d'attente.
     */
    public function test_user_can_have_waiting_list_entry(): void
    {
        $user = User::factory()->create();

        WaitingListEntry::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->waitingListEntry()->exists());
    }
}
