<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingSpot>
 */
class ParkingSpotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $spotNumber = 1;

        return [
            'number' => (string)($spotNumber++),
            'location' => fake()->word(),
            'is_active' => true,
        ];
    }

    /**
     * État pour une place inactive.
     */
    public function inactive(): self
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}
