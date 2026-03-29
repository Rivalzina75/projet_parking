<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ParkingSpot;
use App\Models\User;
use App\Models\WaitingListEntry;
use App\Services\ParkingService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Injecte le service métier responsable des réservations et attributions.
     */
    public function __construct(private readonly ParkingService $parkingService) {}

    /**
     * Traite la demande de réservation de l'utilisateur connecté.
     */
    public function requestReservation(Request $request)
    {
        if (!$request->user()->is_validated) {
            return back()->withErrors(['reservation' => 'Votre compte doit être validé pour effectuer une réservation.']);
        }

        $result = $this->parkingService->requestReservation($request->user());

        if ($result['status'] === 'error') {
            return back()->withErrors(['reservation' => $result['message']]);
        }

        return back()->with('message', $result['message']);
    }

    /**
     * Clôture une réservation si l'utilisateur en est propriétaire ou administrateur.
     */
    public function closeReservation(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            abort(403);
        }

        $this->parkingService->closeReservation($reservation, $request->user()->id);

        return back()->with('message', 'Réservation clôturée.');
    }

    /**
     * Force une attribution de réservation pour un utilisateur depuis l'interface admin.
     */
    public function forceAssign(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'parking_spot_id' => 'nullable|exists:parking_spots,id',
        ]);

        $user = User::findOrFail($data['user_id']);
        if (! empty($data['parking_spot_id'])) {
            $spot = ParkingSpot::findOrFail($data['parking_spot_id']);
            $result = $this->parkingService->assignSpecificSpotToUser($user, $spot, $request->user()->id);
        } else {
            $result = $this->parkingService->requestReservation($user);
        }

        if ($request->user()->role === 'admin') {
            $result['message'] = $this->adaptMessageForAdmin($result['message']);
        }

        if ($result['status'] === 'error') {
            return back()->withErrors(['reservation' => $result['message']]);
        }

        return back()->with('message', 'Attribution forcée traitée. ' . $result['message']);
    }

    /**
     * Retire la place active d'un utilisateur ciblé depuis l'interface admin.
     */
    public function removeAssign(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($data['user_id']);

        if ($user->role !== 'user') {
            return back()->withErrors(['reservation_remove' => 'Seuls les utilisateurs standard peuvent être concernés par cette action.']);
        }

        $activeReservation = Reservation::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->latest('starts_at')
            ->first();

        if (! $activeReservation) {
            $waitingEntry = WaitingListEntry::where('user_id', $user->id)->first();

            if (! $waitingEntry) {
                return back()->withErrors(['reservation_remove' => "Vous ne pouvez pas enlever de place à un utilisateur qui n'en a pas."]);
            }

            $waitingEntry->delete();
            $this->parkingService->reorderWaitingList();

            return back()->with('message', 'Utilisateur retiré de la file d’attente avec succès.');
        }

        $this->parkingService->closeReservation($activeReservation, $request->user()->id);

        return back()->with('message', 'Place retirée avec succès.');
    }

    private function adaptMessageForAdmin(string $message): string
    {
        if ($message === 'Vous avez déjà une réservation active.') {
            return 'L\'utilisateur a déjà une réservation active.';
        }

        if ($message === 'Vous êtes déjà en file d’attente.') {
            return 'L\'utilisateur est déjà en file d\'attente.';
        }

        return $message;
    }
}
