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

    /**
     * Test que l'expiration de réservation respecte la durée par défaut (8 heures).
     */
    public function test_reservation_expires_after_default_duration(): void
    {
        $user = User::factory()->create(['is_validated' => true]);
        $spot = ParkingSpot::factory()->create();

        $this->parkingService->assignSpecificSpotToUser($user, $spot);

        $reservation = Reservation::where('user_id', $user->id)->first();
        $expectedExpiration = $reservation->starts_at->addHours(8);

        $this->assertTrue(
            $reservation->expires_at->diffInSeconds($expectedExpiration) < 2
        );
    }

    /**
     * Test qu'un utilisateur avec réservation active ne peut pas en faire une nouvelle.
     */
    public function test_user_with_active_reservation_cannot_request_another(): void
    {
        $user = User::factory()->create(['is_validated' => true]);
        ParkingSpot::factory()->count(5)->create();

        // Première réservation OK
        $result1 = $this->parkingService->requestReservation($user);
        $this->assertSame('reserved', $result1['status']);

        // Deuxième tentative bloquée
        $result2 = $this->parkingService->requestReservation($user);
        $this->assertSame('error', $result2['status']);
        $this->assertStringContainsString('déjà une réservation', $result2['message']);
    }

    /**
     * Test qu'un utilisateur en file d'attente ne peut pas faire une autre demande.
     */
    public function test_user_in_waiting_list_cannot_request_again(): void
    {
        $user = User::factory()->create(['is_validated' => true]);
        $spot = ParkingSpot::factory()->create();
        Reservation::factory()->create(['parking_spot_id' => $spot->id]);

        // Première demande → attente
        $result1 = $this->parkingService->requestReservation($user);
        $this->assertSame('waiting', $result1['status']);

        // Deuxième tentative bloquée
        $result2 = $this->parkingService->requestReservation($user);
        $this->assertSame('error', $result2['status']);
        $this->assertStringContainsString('attente', $result2['message']);
    }

    /**
     * Test que les attributions utilisent des spots variés (aléatoire).
     */
    public function test_reservations_are_randomly_assigned_to_different_spots(): void
    {
        $spots = ParkingSpot::factory()->count(5)->create();
        $users = User::factory()->count(5)->create(['is_validated' => true]);

        $spotIds = [];
        foreach ($users as $user) {
            $this->parkingService->requestReservation($user);
            $reservation = Reservation::where('user_id', $user->id)->first();
            $spotIds[] = $reservation->parking_spot_id;
        }

        // Au moins 3 spots différents utilisés (sur 5 tentatives + 5 spots)
        $uniqueSpots = count(array_unique($spotIds));
        $this->assertGreaterThan(1, $uniqueSpots);
    }

    /**
     * Test qu'un utilisateur est ajouté en dernière position si la file existe déjà.
     */
    public function test_user_is_appended_to_end_of_waiting_list(): void
    {
        $newUser = User::factory()->create(['is_validated' => true]);

        $spot = ParkingSpot::factory()->create();
        Reservation::factory()->create(['parking_spot_id' => $spot->id]);

        for ($position = 1; $position <= 10; $position++) {
            $waitingUser = User::factory()->create(['is_validated' => true]);
            WaitingListEntry::factory()->create([
                'user_id' => $waitingUser->id,
                'position' => $position,
            ]);
        }

        $result = $this->parkingService->requestReservation($newUser);

        $this->assertSame('waiting', $result['status']);
        $this->assertSame(11, $result['position']);
        $this->assertDatabaseHas('waiting_list_entries', [
            'user_id' => $newUser->id,
            'position' => 11,
        ]);
    }

    /**
     * Test que lorsqu'une place se libère, le premier de file obtient la place et la file baisse de 1.
     */
    public function test_first_waiting_user_gets_freed_spot_and_queue_shrinks(): void
    {
        $owner = User::factory()->create(['is_validated' => true]);
        $waiting1 = User::factory()->create(['is_validated' => true]);
        $waiting2 = User::factory()->create(['is_validated' => true]);
        $waiting3 = User::factory()->create(['is_validated' => true]);

        $spot = ParkingSpot::factory()->create();

        $activeReservation = Reservation::factory()->create([
            'user_id' => $owner->id,
            'parking_spot_id' => $spot->id,
            'ended_at' => null,
            'expires_at' => now()->addHours(2),
        ]);

        WaitingListEntry::factory()->create(['user_id' => $waiting1->id, 'position' => 1]);
        WaitingListEntry::factory()->create(['user_id' => $waiting2->id, 'position' => 2]);
        WaitingListEntry::factory()->create(['user_id' => $waiting3->id, 'position' => 3]);

        $this->parkingService->closeReservation($activeReservation);

        $this->assertDatabaseHas('reservations', [
            'user_id' => $waiting1->id,
            'parking_spot_id' => $spot->id,
            'ended_at' => null,
        ]);

        $this->assertDatabaseMissing('waiting_list_entries', [
            'user_id' => $waiting1->id,
        ]);

        $entry2 = WaitingListEntry::where('user_id', $waiting2->id)->first();
        $entry3 = WaitingListEntry::where('user_id', $waiting3->id)->first();

        $this->assertNotNull($entry2);
        $this->assertNotNull($entry3);
        $this->assertSame(1, $entry2->position);
        $this->assertSame(2, $entry3->position);
    }
}
