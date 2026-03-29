<?php

namespace Tests\Unit\Services;

use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use App\Models\WaitingListEntry;
use App\Services\ParkingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingServiceTest extends TestCase
{
    use RefreshDatabase;

    private ParkingService $parkingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parkingService = app(ParkingService::class);
    }

    /**
     * Test qu'une place peut être assignée manuellement.
     */
    public function test_assigns_specific_spot_to_user(): void
    {
        $user = User::factory()->create(['is_validated' => true]);
        $spot = ParkingSpot::factory()->create();

        $result = $this->parkingService->assignSpecificSpotToUser($user, $spot);

        $this->assertArrayHasKey('status', $result);
        $this->assertSame('reserved', $result['status']);
    }

    /**
     * Test que l'utilisateur est mis en liste d'attente si aucune place disponible.
     */
    public function test_user_added_to_waiting_list_when_no_spots(): void
    {
        $user = User::factory()->create(['is_validated' => true]);

        // Créer des places toutes réservées
        $spots = ParkingSpot::factory()->count(3)->create();
        foreach ($spots as $spot) {
            Reservation::factory()->create(['parking_spot_id' => $spot->id]);
        }

        $result = $this->parkingService->requestReservation($user);

        $this->assertTrue(
            WaitingListEntry::where('user_id', $user->id)->exists()
        );
    }

    /**
     * Test qu'une réservation peut être fermée.
     */
    public function test_reservation_can_be_closed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reservation = Reservation::factory()->create();

        $this->parkingService->closeReservation($reservation, $admin->id);

        $reservation->refresh();
        $this->assertNotNull($reservation->ended_at);
        $this->assertSame($admin->id, $reservation->closed_by);
    }

    /**
     * Test que la réinitialisation des réservations expirées fonctionne.
     */
    public function test_expired_reservations_are_closed(): void
    {
        $reservation = Reservation::factory()->create([
            'expires_at' => now()->subHour(),
            'ended_at' => null,
        ]);

        $this->parkingService->closeExpiredReservations();

        $reservation->refresh();
        $this->assertNotNull($reservation->ended_at);
    }

    /**
     * Test que la réservation n'est pas valide si l'utilisateur n'est pas vérifié.
     */
    public function test_unvalidated_user_cannot_be_assigned(): void
    {
        $user = User::factory()->create(['is_validated' => false]);
        $spot = ParkingSpot::factory()->create();

        $result = $this->parkingService->assignSpecificSpotToUser($user, $spot);

        $this->assertSame('error', $result['status']);
    }
}
