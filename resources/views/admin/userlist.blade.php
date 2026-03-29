@extends('layouts.app')

@section('title', 'Admin - Utilisateurs')

@section('content')
<div class="panel page-panel">
    @include('partials.sidebar_admin', ['active' => 'users'])

    <div class="page-body">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <div>
                <div style="font-size:15px;font-weight:500;">Gestion des utilisateurs</div>
                <div class="muted" style="font-size:12px;">{{ $users->count() }} utilisateurs</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Validation</th>
                    <th>Statut parking</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    @php
                        $reservation = $activeReservationByUser->get($user->id);
                        $waiting = $waitingByUser->get($user->id);
                    @endphp
                    <tr>
                        <td>{{ $user->name }} {{ $user->lastname }}</td>
                        <td class="muted">{{ $user->email }}</td>
                        <td>
                            <span class="status-badge {{ $user->is_validated ? 'validated' : 'pending' }}">
                                {{ $user->is_validated ? 'Validé' : 'En attente' }}
                            </span>
                        </td>
                        <td>
                            @if($reservation)
                                <span class="status-badge validated">Place {{ $reservation->parkingSpot->number }}</span>
                            @elseif($waiting)
                                <span class="status-badge pending">Attente rang {{ $waiting->position }}</span>
                            @else
                                <span class="status-badge neutral">Sans place</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <a class="btn" href="{{ route('admin.users.show', $user) }}" style="font-size:12px;padding:4px 9px;border-color:#854F0B;color:#854F0B;">Voir</a>

                                @if(!$user->is_validated)
                                    <form method="POST" action="{{ route('admin.users.validate', $user) }}">
                                        @csrf
                                        <button class="btn" type="submit" style="font-size:12px;padding:4px 9px;">Valider</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                                    @csrf
                                    <button class="btn" type="submit" style="font-size:12px;padding:4px 9px;">Réinit. mdp</button>
                                </form>

                                @if($user->role !== 'admin')
                                    <form method="POST" action="{{ route('admin.reservation.force') }}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <button class="btn" type="submit" style="font-size:12px;padding:4px 9px;">Forcer résa</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection