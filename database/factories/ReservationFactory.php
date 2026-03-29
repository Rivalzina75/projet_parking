<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use App\Models\ParkingSpot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = Carbon::now();
        $expiresAt = $startsAt->copy()->addDays(30);

        return [
            'user_id' => User::factory(),
            'parking_spot_id' => ParkingSpot::factory(),
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'ended_at' => null,
            'closed_by' => null,
            'notes' => null,
        ];
    }

    /**
     * État pour une réservation expirée.
     */
    public function expired(): self
    {
        return $this->state([
            'expires_at' => Carbon::now()->subMinute(),
            'ended_at' => Carbon::now(),
        ]);
    }

    /**
     * État pour une réservation fermée.
     */
    public function closed(): self
    {
        return $this->state([
            'ended_at' => Carbon::now(),
            'closed_by' => User::factory(),
        ]);
    }
}
