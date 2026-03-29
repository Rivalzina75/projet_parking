<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use App\Services\ParkingService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(private readonly ParkingService $parkingService)
    {
    }

    public function requestReservation(Request $request)
    {
        $result = $this->parkingService->requestReservation($request->user());

        if ($result['status'] === 'error') {
            return back()->withErrors(['reservation' => $result['message']]);
        }

        return back()->with('message', $result['message']);
    }

    public function closeReservation(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            abort(403);
        }

        $this->parkingService->closeReservation($reservation, $request->user()->id);

        return back()->with('message', 'Réservation clôturée.');
    }

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

        return back()->with('message', 'Attribution forcée traitée. '.$result['message']);
    }
}
