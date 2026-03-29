@extends('layouts.app')

@section('title', 'Utilisateurs — Admin')

@section('content')
<div class="page-panel" role="main">
    @include('partials.sidebar_admin', ['active' => 'users'])

    <div class="page-body">
        <section aria-label="Gestion des utilisateurs">
            <div class="section-header mb-6">
                <div>
                    <h1 class="section-title">Gestion des utilisateurs</h1>
                    <p class="section-sub" role="status">{{ $users->count() }} utilisateur(s) enregistré(s)</p>
                </div>
            </div>

            <div class="table-wrap" role="region" aria-label="Tableau des utilisateurs">
                <table role="table" aria-label="Liste complète des utilisateurs" role="region">
                    <thead>
                        <tr>
                            <th scope="col">Utilisateur</th>
                            <th scope="col">Email</th>
                            <th scope="col">Validation</th>
                            <th scope="col">Statut parking</th>
                            <th scope="col">Actions</th>
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
                                    <strong>{{ $user->name }} {{ $user->lastname }}</strong>
                                    <div class="text-xs muted">{{ ucfirst($user->role) }}</div>
                                </td>
                                <td class="muted text-sm">{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->is_validated ? 'badge-green' : 'badge-amber' }}"
                                          role="status" aria-label="{{ $user->is_validated ? 'Validé' : 'En attente de validation' }}">
                                        {{ $user->is_validated ? 'Validé' : 'En attente' }}
                                    </span>
                                </td>
                                <td>
                                    @if($reservation)
                                        <span class="badge badge-green" role="status" aria-label="Place attribuée">Place {{ $reservation->parkingSpot->number }}</span>
                                    @elseif($waiting)
                                        <span class="badge badge-amber" role="status" aria-label="En file d'attente">File rang {{ $waiting->position }}</span>
                                    @else
                                        <span class="badge badge-gray" role="status" aria-label="Aucune place">Sans place</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="actions" role="toolbar" aria-label="Actions pour cet utilisateur">
                                        <a class="btn btn-sm" href="{{ route('admin.users.show', $user) }}"
                                           style="border-color:var(--amber); color:var(--amber-text);"
                                           aria-label="Voir la fiche complète de {{ $user->name }} {{ $user->lastname }}">
                                            Voir fiche
                                        </a>

                                        @if(!$user->is_validated)
                                            <form method="POST" action="{{ route('admin.users.validate', $user) }}" style="display: inline;">
                                                @csrf
                                                <button class="btn btn-sm btn-success" type="submit" 
                                                        aria-label="Valider le compte de {{ $user->name }} {{ $user->lastname }}">
                                                    Valider
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" style="display: inline;">
                                            @csrf
                                            <button class="btn btn-sm" type="submit"
                                                    aria-label="Réinitialiser le mot de passe de {{ $user->name }} {{ $user->lastname }}">
                                                Réinit. mdp
                                            </button>
                                        </form>

                                        @if($user->role !== 'admin')
                                            <form method="POST" action="{{ route('admin.reservation.force') }}" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button class="btn btn-sm" type="submit"
                                                        style="border-color:var(--blue); color:var(--blue);"
                                                        aria-label="Forcer une réservation pour {{ $user->name }} {{ $user->lastname }}">
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
        </section>
    </div>
</div>
@endsection