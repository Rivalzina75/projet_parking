<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\WaitingListEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaitingListEntryModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'une entrée en liste d'attente appartient à un utilisateur.
     */
    public function test_waiting_list_entry_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $entry = WaitingListEntry::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($entry->user()->is($user));
    }
}
