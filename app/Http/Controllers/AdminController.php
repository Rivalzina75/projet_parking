<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Models\User;
use App\Models\WaitingListEntry;
use App\Services\ParkingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Injecte le service métier de gestion du parking.
     */
    public function __construct(private readonly ParkingService $parkingService) {}

    /**
     * Affiche la liste des utilisateurs avec leur réservation active et leur position en file d'attente.
     */
    public function users()
    {
        $users = User::where('role', 'user')
            ->orderBy('lastname')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $userIds = $users->getCollection()->pluck('id');

        $activeReservationByUser = Reservation::with('parkingSpot')
            ->whereIn('user_id', $userIds)
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->get()
            ->keyBy('user_id');

        $waitingByUser = WaitingListEntry::whereIn('user_id', $userIds)
            ->orderBy('position')
            ->get()
            ->keyBy('user_id');

        return view('admin.userlist', compact('users', 'activeReservationByUser', 'waitingByUser'));
    }

    /**
     * Permet à l'administrateur de créer un compte utilisateur membre de la ligue.
     */
    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:30',
            'lastname' => 'required|string|max:30',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:10|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/|confirmed',
        ]);

        User::create([
            'name' => trim($data['name']),
            'lastname' => trim($data['lastname']),
            'email' => trim($data['email']),
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'is_validated' => true,
        ]);

        return back()->with('message', 'Compte utilisateur créé et validé par administrateur.');
    }

    /**
     * Affiche le détail d'un utilisateur, sa réservation active et son historique.
     */
    public function userDetail(User $user)
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        $activeReservation = Reservation::with('parkingSpot')
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->first();

        $history = Reservation::with('parkingSpot')
            ->where('user_id', $user->id)
            ->orderByDesc('starts_at')
            ->limit(25)
            ->get();

        return view('admin.user_instance', compact('user', 'activeReservation', 'history'));
    }

    /**
     * Valide le compte d'un utilisateur pour lui autoriser l'accès à l'application.
     */
    public function validateUser(User $user)
    {
        $user->update(['is_validated' => true]);

        return back()->with('message', 'Compte utilisateur validé.');
    }

    /**
     * Génère et applique un mot de passe temporaire pour un utilisateur.
     */
    public function resetUserPassword(User $user)
    {
        $newPassword = Str::password(12);

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return back()->with('message', 'Nouveau mot de passe temporaire : ' . $newPassword);
    }

    /**
     * Affiche la gestion des places avec les statistiques et réservations en cours.
     */
    public function places()
    {
        $spots = ParkingSpot::orderBy('number')
            ->paginate(10)
            ->withQueryString();

        $spotIds = $spots->getCollection()->pluck('id');

        $allSpotsForAssign = ParkingSpot::orderBy('number')->get();

        $users = User::where('role', 'user')
            ->where('is_validated', true)
            ->orderBy('lastname')
            ->orderBy('name')
            ->get();

        $activeReservations = Reservation::with('user')
            ->whereIn('parking_spot_id', $spotIds)
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->get()
            ->keyBy('parking_spot_id');

        $total = ParkingSpot::count();
        $occupied = Reservation::whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->count();
        $free = max(0, $total - $occupied);
        $defaultDuration = (int) (DB::table('app_settings')->value('default_reservation_hours') ?? 8);

        return view('admin.places', compact(
            'spots',
            'allSpotsForAssign',
            'users',
            'activeReservations',
            'total',
            'occupied',
            'free',
            'defaultDuration'
        ));
    }

    /**
     * Affiche la page dédiée aux paramètres administrateur.
     */
    public function settingsPage()
    {
        $defaultDuration = (int) (DB::table('app_settings')->value('default_reservation_hours') ?? 8);
        $doubleConsentEnabled = (bool) (DB::table('app_settings')->value('double_consent_enabled') ?? false);

        return view('admin.settings', compact('defaultDuration', 'doubleConsentEnabled'));
    }

    /**
     * Affiche l'historique complet des réservations d'une place donnée.
     */
    public function spotHistory(ParkingSpot $spot)
    {
        $history = Reservation::with('user')
            ->where('parking_spot_id', $spot->id)
            ->orderByDesc('starts_at')
            ->paginate(30);

        return view('admin.spot_history', compact('spot', 'history'));
    }

    /**
     * Attribue manuellement une place précise à un utilisateur valide.
     */
    public function assignPlace(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'spot_id' => 'required|exists:parking_spots,id',
        ]);

        $user = User::findOrFail($data['user_id']);
        $spot = ParkingSpot::findOrFail($data['spot_id']);

        if ($user->role !== 'user') {
            return back()->withErrors(['assign' => 'Seuls les utilisateurs standard peuvent recevoir une place.']);
        }

        $result = $this->parkingService->assignSpecificSpotToUser($user, $spot, Auth::id());

        if ($result['status'] === 'error') {
            return back()->withErrors(['assign' => $result['message']]);
        }

        return back()->with('message', $result['message']);
    }

    /**
     * Crée une nouvelle place puis tente une attribution automatique au prochain utilisateur en attente.
     */
    public function storePlace(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|string|max:30|unique:parking_spots,number',
            'location' => 'nullable|string|max:255',
        ]);

        ParkingSpot::create($data);

        $this->parkingService->assignSpotToNextWaitingUser();

        return back()->with('message', 'Place ajoutée.');
    }

    /**
     * Met à jour les informations d'une place (numéro, localisation, état actif).
     */
    public function updatePlace(Request $request, ParkingSpot $spot)
    {
        $data = $request->validate([
            'number' => 'required|string|max:30|unique:parking_spots,number,' . $spot->id,
            'location' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $spot->update([
            'number' => $data['number'],
            'location' => $data['location'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return back()->with('message', 'Place mise à jour.');
    }

    /**
     * Supprime une place uniquement si elle n'est pas actuellement occupée.
     */
    public function deletePlace(ParkingSpot $spot)
    {
        $hasActiveReservation = Reservation::where('parking_spot_id', $spot->id)
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($hasActiveReservation) {
            return back()->withErrors([
                'place' => 'Impossible de supprimer une place actuellement occupée.',
            ]);
        }

        $spot->delete();

        return back()->with('message', 'Place supprimée.');
    }

    /**
     * Affiche la file d'attente des utilisateurs sans place.
     */
    public function waitingList()
    {
        $waiting = WaitingListEntry::with('user')
            ->orderBy('position')
            ->paginate(10)
            ->withQueryString();

        return view('admin.waiting_list', compact('waiting'));
    }

    /**
     * Déplace un utilisateur à une nouvelle position dans la file d'attente.
     */
    public function moveWaiting(Request $request, WaitingListEntry $entry)
    {
        $data = $request->validate([
            'position' => 'required|integer|min:1',
        ]);

        $this->parkingService->moveWaitingEntry($entry->id, (int) $data['position']);

        return back()->with('message', 'Position mise à jour.');
    }

    /**
     * Met à jour la durée par défaut des réservations dans les paramètres applicatifs.
     */
    public function settings(Request $request)
    {
        $data = $request->validate([
            'default_reservation_hours' => 'nullable|integer|min:1|max:240',
            'double_consent_enabled' => 'nullable|boolean',
        ]);

        $settingsRow = DB::table('app_settings')->first();

        $updates = [
            'updated_at' => now(),
        ];

        $messages = [];

        if ($request->has('default_reservation_hours')) {
            $updates['default_reservation_hours'] = (int) $data['default_reservation_hours'];
            $messages[] = 'Durée par défaut mise à jour.';
        }

        if ($request->has('double_consent_enabled') || $request->boolean('settings_toggle', false)) {
            $updates['double_consent_enabled'] = $request->boolean('double_consent_enabled');
            $messages[] = $updates['double_consent_enabled']
                ? 'Double consentement activé.'
                : 'Double consentement désactivé.';
        }

        if (! isset($updates['default_reservation_hours']) && ! array_key_exists('double_consent_enabled', $updates)) {
            return back()->withErrors(['settings' => 'Aucun paramètre à mettre à jour.']);
        }

        if ($settingsRow) {
            DB::table('app_settings')->where('id', $settingsRow->id)->update($updates);
        } else {
            DB::table('app_settings')->insert([
                'default_reservation_hours' => $updates['default_reservation_hours'] ?? 8,
                'double_consent_enabled' => $updates['double_consent_enabled'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('message', implode(' ', $messages));
    }
}
