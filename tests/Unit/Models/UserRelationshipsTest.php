<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que l'utilisateur a une relation "reservations".
     */
    public function test_user_has_reservations_relationship(): void
    {
        $user = User::factory()->create();
        Reservation::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->reservations);
    }

    /**
     * Test que l'utilisateur peut être un "closer" d'une réservation.
     */
    public function test_user_can_be_closer_of_reservation(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'closed_by' => $admin->id,
        ]);

        $this->assertTrue($reservation->closer()->is($admin));
    }
}
