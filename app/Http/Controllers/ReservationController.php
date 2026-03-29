<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ParkingSpot;
use App\Models\User;
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

        if ($result['status'] === 'error') {
            return back()->withErrors(['reservation' => $result['message']]);
        }

        return back()->with('message', 'Attribution forcée traitée. ' . $result['message']);
    }
}
