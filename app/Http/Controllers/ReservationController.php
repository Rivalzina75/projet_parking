<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
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
        ]);

        $user = User::findOrFail($data['user_id']);
        $result = $this->parkingService->requestReservation($user);

        if ($result['status'] === 'error') {
            return back()->withErrors(['reservation' => $result['message']]);
        }

        return back()->with('message', 'Attribution forcée traitée. ' . $result['message']);
    }
}
