<?php

namespace Tests\Unit\Models;

use App\Models\Reservation;
use App\Models\User;
use App\Models\ParkingSpot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'une réservation appartient à un utilisateur.
     */
    public function test_reservation_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($reservation->user()->is($user));
    }

    /**
     * Test qu'une réservation appartient à une place.
     */
    public function test_reservation_belongs_to_parking_spot(): void
    {
        $spot = ParkingSpot::factory()->create();
        $reservation = Reservation::factory()->create(['parking_spot_id' => $spot->id]);

        $this->assertTrue($reservation->parkingSpot()->is($spot));
    }

    /**
     * Test qu'une réservation peut avoir un closer.
     */
    public function test_reservation_can_have_closer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reservation = Reservation::factory()->create(['closed_by' => $admin->id]);

        $this->assertTrue($reservation->closer()->is($admin));
    }
}
