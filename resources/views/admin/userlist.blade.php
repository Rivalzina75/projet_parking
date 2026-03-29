@extends('layouts.app')

@section('title', 'Admin - Utilisateurs')

@section('content')
    <h1>Gestion des utilisateurs</h1>
    <p class="muted">Validation des comptes, suivi des statuts parking et actions administrateur.</p>

    <div class="panel mt">
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
                    <td>{{ $user->email }}</td>
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
                    <td class="actions">
                        <a class="btn" href="{{ route('admin.users.show', $user) }}">Voir</a>

                        @if(! $user->is_validated)
                            <form method="POST" action="{{ route('admin.users.validate', $user) }}">
                                @csrf
                                <button class="btn" type="submit">Valider</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                            @csrf
                            <button class="btn" type="submit">Réinit. mdp</button>
                        </form>

                        @if($user->role !== 'admin')
                            <form method="POST" action="{{ route('admin.reservation.force') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <button class="btn" type="submit">Forcer résa</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="row gap mt">
        <a class="btn" href="{{ route('admin.places') }}">Gérer les places</a>
        <a class="btn" href="{{ route('admin.waiting') }}">Voir la file d’attente</a>
    </div>
@endsection
