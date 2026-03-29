<?php

namespace Tests\Unit\Models;

use App\Models\ParkingSpot;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingSpotModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'une place peut avoir beaucoup de réservations.
     */
    public function test_spot_has_many_reservations(): void
    {
        $spot = ParkingSpot::factory()->create();

        Reservation::factory()->count(5)->create([
            'parking_spot_id' => $spot->id,
        ]);

        $this->assertCount(5, $spot->reservations);
    }
}
