<?php

namespace App\Services;

use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use App\Models\WaitingListEntry;
use Illuminate\Support\Facades\DB;

class ParkingService
{
    public function assignSpecificSpotToUser(User $user, ParkingSpot $spot, ?int $closedBy = null): array
    {
        if (! $user->is_validated) {
            return ['status' => 'error', 'message' => 'Le compte utilisateur doit être validé avant attribution.'];
        }

        if ($this->hasActiveReservation($user)) {
            return ['status' => 'error', 'message' => 'Cet utilisateur a déjà une réservation active.'];
        }

        if (! $spot->is_active) {
            return ['status' => 'error', 'message' => 'La place sélectionnée est désactivée.'];
        }

        if (! $this->spotIsAvailable($spot->id)) {
            return ['status' => 'error', 'message' => 'La place sélectionnée est déjà occupée.'];
        }

        return DB::transaction(function () use ($user, $spot, $closedBy) {
            $this->closeExpiredReservations();

            $duration = $this->defaultDurationHours();
            $now = now();

            Reservation::create([
                'user_id' => $user->id,
                'parking_spot_id' => $spot->id,
                'starts_at' => $now,
                'expires_at' => $now->copy()->addHours($duration),
                'closed_by' => $closedBy,
                'notes' => 'Attribution manuelle administrateur',
            ]);

            WaitingListEntry::where('user_id', $user->id)->delete();
            $this->reorderWaitingList();

            return ['status' => 'reserved', 'message' => 'Place attribuée manuellement avec succès.'];
        });
    }

    public function requestReservation(User $user): array
    {
        if ($this->hasActiveReservation($user)) {
            return ['status' => 'error', 'message' => 'Vous avez déjà une réservation active.'];
        }

        if ($this->isInWaitingList($user)) {
            return ['status' => 'error', 'message' => 'Vous êtes déjà en file d’attente.'];
        }

        return DB::transaction(function () use ($user) {
            $this->closeExpiredReservations();

            $availableSpot = $this->pickRandomAvailableSpot();

            if (! $availableSpot) {
                $entry = WaitingListEntry::create([
                    'user_id' => $user->id,
                    'position' => ((int) WaitingListEntry::max('position')) + 1,
                ]);

                return [
                    'status' => 'waiting',
                    'message' => 'Aucune place libre : vous avez été ajouté en file d’attente.',
                    'position' => $entry->position,
                ];
            }

            $duration = $this->defaultDurationHours();
            $now = now();

            Reservation::create([
                'user_id' => $user->id,
                'parking_spot_id' => $availableSpot->id,
                'starts_at' => $now,
                'expires_at' => $now->copy()->addHours($duration),
            ]);

            return [
                'status' => 'reserved',
                'message' => 'Une place vous a été attribuée immédiatement.',
            ];
        });
    }

    public function closeReservation(Reservation $reservation, ?int $closedBy = null): void
    {
        if ($reservation->ended_at) {
            return;
        }

        DB::transaction(function () use ($reservation, $closedBy) {
            $reservation->update([
                'ended_at' => now(),
                'closed_by' => $closedBy,
            ]);

            $this->assignSpotToNextWaitingUser($reservation->parking_spot_id);
        });
    }

    public function moveWaitingEntry(int $entryId, int $newPosition): void
    {
        DB::transaction(function () use ($entryId, $newPosition) {
            $entries = WaitingListEntry::orderBy('position')->get();
            $entry = $entries->firstWhere('id', $entryId);

            if (! $entry) {
                return;
            }

            $entries = $entries->reject(fn($item) => $item->id === $entryId)->values();
            $newPosition = max(1, min($newPosition, $entries->count() + 1));
            $entries->splice($newPosition - 1, 0, [$entry]);

            foreach ($entries->values() as $index => $item) {
                WaitingListEntry::whereKey($item->id)->update(['position' => $index + 1]);
            }
        });
    }

    public function closeExpiredReservations(): void
    {
        $expired = Reservation::whereNull('ended_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expired as $reservation) {
            $this->closeReservation($reservation);
        }
    }

    public function assignSpotToNextWaitingUser(?int $spotId = null): void
    {
        DB::transaction(function () use ($spotId) {
            $entry = WaitingListEntry::orderBy('position')->first();
            if (! $entry) {
                return;
            }

            $availableSpot = $spotId
                ? ParkingSpot::whereKey($spotId)->first()
                : $this->pickRandomAvailableSpot();

            if (! $availableSpot || ! $this->spotIsAvailable($availableSpot->id)) {
                return;
            }

            $duration = $this->defaultDurationHours();
            $now = now();

            Reservation::create([
                'user_id' => $entry->user_id,
                'parking_spot_id' => $availableSpot->id,
                'starts_at' => $now,
                'expires_at' => $now->copy()->addHours($duration),
                'notes' => 'Attribuée depuis la file d’attente',
            ]);

            $entry->delete();
            $this->reorderWaitingList();
        });
    }

    public function reorderWaitingList(): void
    {
        $entries = WaitingListEntry::orderBy('position')->get();

        foreach ($entries as $index => $entry) {
            if ($entry->position !== ($index + 1)) {
                $entry->update(['position' => $index + 1]);
            }
        }
    }

    private function hasActiveReservation(User $user): bool
    {
        return Reservation::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    private function isInWaitingList(User $user): bool
    {
        return WaitingListEntry::where('user_id', $user->id)->exists();
    }

    private function spotIsAvailable(int $spotId): bool
    {
        return ! Reservation::where('parking_spot_id', $spotId)
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    private function pickRandomAvailableSpot(): ?ParkingSpot
    {
        return ParkingSpot::where('is_active', true)
            ->whereNotIn('id', function ($query) {
                $query->select('parking_spot_id')
                    ->from('reservations')
                    ->whereNull('ended_at')
                    ->where('expires_at', '>', now());
            })
            ->inRandomOrder()
            ->first();
    }

    private function defaultDurationHours(): int
    {
        return (int) (DB::table('app_settings')->value('default_reservation_hours') ?? 8);
    }
}
