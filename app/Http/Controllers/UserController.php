<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\WaitingListEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Affiche le tableau de bord utilisateur avec réservation active, file d'attente et historique.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $activeReservation = Reservation::with('parkingSpot')
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        $waitingEntry = WaitingListEntry::where('user_id', $user->id)->first();

        $history = Reservation::with('parkingSpot')
            ->where('user_id', $user->id)
            ->orderByDesc('starts_at')
            ->limit(15)
            ->get();

        return view('user.dashboard', compact('activeReservation', 'waitingEntry', 'history'));
    }

    /**
     * Affiche l'historique complet des réservations de l'utilisateur connecté.
     */
    public function history(Request $request)
    {
        $history = Reservation::with('parkingSpot')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('starts_at')
            ->paginate(25);

        return view('user.history', compact('history'));
    }

    /**
     * Affiche la page de profil de l'utilisateur connecté.
     */
    public function profil(Request $request)
    {
        return view('user.profil', ['user' => $request->user()]);
    }

    /**
     * Met à jour le mot de passe de l'utilisateur après vérification de l'ancien mot de passe.
     */
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
