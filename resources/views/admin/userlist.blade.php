@extends('layouts.app')

@section('title', 'Utilisateurs — Admin')

@section('content')
<div class="page-panel">
    @include('partials.sidebar_admin', ['active' => 'users'])

    <div class="page-body">
        <div class="section-header mb-6">
            <div>
                <div class="section-title">Gestion des utilisateurs</div>
                <div class="section-sub">{{ $users->count() }} utilisateur(s) enregistré(s)</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
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
                            <td>
                                <div style="font-weight:600;">{{ $user->name }} {{ $user->lastname }}</div>
                                <div class="text-xs muted">{{ ucfirst($user->role) }}</div>
                            </td>
                            <td class="muted text-sm">{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->is_validated ? 'badge-green' : 'badge-amber' }}">
                                    {{ $user->is_validated ? 'Validé' : 'En attente' }}
                                </span>
                            </td>
                            <td>
                                @if($reservation)
                                    <span class="badge badge-green">Place {{ $reservation->parkingSpot->number }}</span>
                                @elseif($waiting)
                                    <span class="badge badge-amber">File rang {{ $waiting->position }}</span>
                                @else
                                    <span class="badge badge-gray">Sans place</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-sm" href="{{ route('admin.users.show', $user) }}"
                                       style="border-color:var(--amber); color:var(--amber-text);">
                                        Voir fiche
                                    </a>

                                    @if(!$user->is_validated)
                                        <form method="POST" action="{{ route('admin.users.validate', $user) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success" type="submit">Valider</button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                                        @csrf
                                        <button class="btn btn-sm" type="submit">Réinit. mdp</button>
                                    </form>

                                    @if($user->role !== 'admin')
                                        <form method="POST" action="{{ route('admin.reservation.force') }}">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <button class="btn btn-sm" type="submit"
                                                    style="border-color:var(--blue); color:var(--blue);">
                                                Forcer résa
                                            </button>
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
</div>
@endsection