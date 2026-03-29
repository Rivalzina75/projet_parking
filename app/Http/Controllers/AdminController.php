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
    public function __construct(private readonly ParkingService $parkingService)
    {
    }

    public function users()
    {
        $users = User::orderBy('lastname')->orderBy('name')->get();

        $activeReservationByUser = Reservation::with('parkingSpot')
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->get()
            ->keyBy('user_id');

        $waitingByUser = WaitingListEntry::orderBy('position')->get()->keyBy('user_id');

        return view('admin.userlist', compact('users', 'activeReservationByUser', 'waitingByUser'));
    }

    public function userDetail(User $user)
    {
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

    public function validateUser(User $user)
    {
        $user->update(['is_validated' => true]);

        return back()->with('message', 'Compte utilisateur validé.');
    }

    public function resetUserPassword(User $user)
    {
        $newPassword = Str::password(12);

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return back()->with('message', 'Nouveau mot de passe temporaire : '.$newPassword);
    }

    public function places()
    {
        $spots = ParkingSpot::orderBy('number')->get();

        $users = User::where('role', 'user')
            ->where('is_validated', true)
            ->orderBy('lastname')
            ->orderBy('name')
            ->get();

        $activeReservations = Reservation::with('user')
            ->whereNull('ended_at')
            ->where('expires_at', '>', now())
            ->get()
            ->keyBy('parking_spot_id');

        $total = $spots->count();
        $occupied = $activeReservations->count();
        $free = max(0, $total - $occupied);
        $defaultDuration = (int) (DB::table('app_settings')->value('default_reservation_hours') ?? 8);

        return view('admin.places', compact('spots', 'users', 'activeReservations', 'total', 'occupied', 'free', 'defaultDuration'));
    }

    public function assignPlace(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'spot_id' => 'required|exists:parking_spots,id',
        ]);

        $user = User::findOrFail($data['user_id']);
        $spot = ParkingSpot::findOrFail($data['spot_id']);

        $result = $this->parkingService->assignSpecificSpotToUser($user, $spot, Auth::id());

        if ($result['status'] === 'error') {
            return back()->withErrors(['assign' => $result['message']]);
        }

        return back()->with('message', $result['message']);
    }

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

    public function updatePlace(Request $request, ParkingSpot $spot)
    {
        $data = $request->validate([
            'number' => 'required|string|max:30|unique:parking_spots,number,'.$spot->id,
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

    public function waitingList()
    {
        $waiting = WaitingListEntry::with('user')->orderBy('position')->get();

        return view('admin.waiting_list', compact('waiting'));
    }

    public function moveWaiting(Request $request, WaitingListEntry $entry)
    {
        $data = $request->validate([
            'position' => 'required|integer|min:1',
        ]);

        $this->parkingService->moveWaitingEntry($entry->id, (int) $data['position']);

        return back()->with('message', 'Position mise à jour.');
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'default_reservation_hours' => 'required|integer|min:1|max:240',
        ]);

        DB::table('app_settings')->update([
            'default_reservation_hours' => $data['default_reservation_hours'],
            'updated_at' => now(),
        ]);

        return back()->with('message', 'Durée par défaut mise à jour.');
    }
}
