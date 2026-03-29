<?php

namespace Database\Factories;

use App\Models\WaitingListEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WaitingListEntry>
 */
class WaitingListEntryFactory extends Factory
{
    protected $model = WaitingListEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $position = 1;

        return [
            'user_id' => User::factory(),
            'position' => $position++,
        ];
    }
}
