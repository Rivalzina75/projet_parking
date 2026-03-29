<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\WaitingListEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $activeReservation = Reservation::with('parkingSpot')
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->first();

        $waitingEntry = WaitingListEntry::where('user_id', $user->id)->first();

        $history = Reservation::with('parkingSpot')
            ->where('user_id', $user->id)
            ->orderByDesc('starts_at')
            ->limit(15)
            ->get();

        return view('user.dashboard', compact('activeReservation', 'waitingEntry', 'history'));
    }

    public function profil(Request $request)
    {
        return view('user.profil', ['user' => $request->user()]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:10|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Mot de passe actuel incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('message', 'Mot de passe mis à jour.');
    }
}
