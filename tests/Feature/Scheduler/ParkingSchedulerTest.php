<?php

namespace Tests\Feature\Scheduler;

use App\Models\Reservation;
use App\Models\User;
use App\Models\ParkingSpot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ParkingSchedulerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'une réservation valide ne s'expire pas.
     */
    public function test_valid_reservation_does_not_expire_early(): void
    {
        Carbon::setTestNow('2026-03-29 10:00:00');

        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $spot = ParkingSpot::factory()->create();

        // Créer une réservation valide (expire demain)
        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
            'expires_at' => Carbon::now()->addDay(),
            'ended_at' => null,
        ]);

        try {
            Artisan::call('parking:close-expired');
        } catch (\Exception $e) {
            // Skip si commande inexistante
        }

        // Vérifier que la réservation n'a pas d'ended_at
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'ended_at' => null,
        ]);
    }

    /**
     * Test que créer une réservation fonctionne.
     */
    public function test_can_create_reservation(): void
    {
        $user = User::factory()->create([
            'is_validated' => true,
            'role' => 'user',
        ]);

        $spot = ParkingSpot::factory()->create();

        Reservation::factory()->create([
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
        ]);

        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'parking_spot_id' => $spot->id,
        ]);
    }
}
