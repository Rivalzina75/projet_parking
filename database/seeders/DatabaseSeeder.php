<?php

namespace Database\Seeders;

use App\Models\ParkingSpot;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@parking.local'], [
            'name' => 'Admin',
            'lastname' => 'Parking',
            'password' => Hash::make('Admin@123456'),
            'role' => 'admin',
            'is_validated' => true,
        ]);

        User::updateOrCreate(['email' => 'user@parking.local'], [
            'name' => 'User',
            'lastname' => 'Demo',
            'password' => Hash::make('User@123456'),
            'role' => 'user',
            'is_validated' => true,
        ]);

        User::factory(8)->create();

        $spots = [
            ['number' => 'P-01', 'location' => 'Bâtiment A - N-1'],
            ['number' => 'P-02', 'location' => 'Bâtiment A - N-1'],
            ['number' => 'P-03', 'location' => 'Bâtiment A - N-1'],
            ['number' => 'P-04', 'location' => 'Bâtiment B - N-1'],
            ['number' => 'P-05', 'location' => 'Bâtiment B - N-1'],
            ['number' => 'P-06', 'location' => 'Bâtiment B - N-2'],
            ['number' => 'P-07', 'location' => 'Bâtiment C - N-1'],
            ['number' => 'P-08', 'location' => 'Bâtiment C - N-2'],
            ['number' => 'P-09', 'location' => 'Bâtiment C - N-2'],
            ['number' => 'P-10', 'location' => 'Bâtiment D - N-1'],
        ];

        foreach ($spots as $spot) {
            ParkingSpot::updateOrCreate(['number' => $spot['number']], $spot);
        }
    }
}
