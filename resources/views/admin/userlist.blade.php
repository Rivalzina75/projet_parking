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
                    <p class="section-sub" role="status">{{ $users->total() }} utilisateur(s) enregistré(s)</p>
                </div>
            </div>

            <article class="card mb-6" role="region" aria-label="Créer un compte utilisateur">
                <h2 style="font-size:13px; font-weight:700; margin-bottom:10px;">Créer un compte membre</h2>
                <form method="POST" action="{{ route('admin.users.store') }}" class="inline-form" aria-label="Formulaire de création de compte utilisateur">
                    @csrf
                    <input type="text" name="name" maxlength="30" required placeholder="Prénom" aria-label="Prénom utilisateur">
                    <input type="text" name="lastname" maxlength="30" required placeholder="Nom" aria-label="Nom utilisateur">
                    <input type="email" name="email" maxlength="255" required placeholder="Email" aria-label="Email utilisateur">
                    <input type="password" name="password" required placeholder="Mot de passe" aria-label="Mot de passe">
                    <input type="password" name="password_confirmation" required placeholder="Confirmation" aria-label="Confirmation mot de passe">
                    <button class="btn btn-sm btn-primary" type="submit" data-requires-consent="true"
                            data-consent-message="Confirmer la création de ce compte utilisateur ?">
                        Créer le compte
                    </button>
                </form>
            </article>

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
                                                <button class="btn btn-sm btn-success" type="submit" data-requires-consent="true"
                                                        data-consent-message="Êtes-vous sûr de vouloir valider le compte de {{ $user->name }} {{ $user->lastname }} ?"
                                                        aria-label="Valider le compte de {{ $user->name }} {{ $user->lastname }}">
                                                    Valider
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.reservation.force') }}" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <button class="btn btn-sm" type="submit" data-requires-consent="true"
                                                    data-consent-message="Êtes-vous sûr de vouloir forcer une réservation pour {{ $user->name }} {{ $user->lastname }} ?"
                                                    style="border-color:var(--blue); color:var(--blue);"
                                                    aria-label="Forcer une réservation pour {{ $user->name }} {{ $user->lastname }}">
                                                Forcer résa
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.reservation.remove') }}" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <button class="btn btn-sm btn-danger" type="submit" data-requires-consent="true"
                                                    data-consent-message="Êtes-vous sûr de vouloir enlever la place de {{ $user->name }} {{ $user->lastname }} ?"
                                                    aria-label="Enlever la réservation de {{ $user->name }} {{ $user->lastname }}">
                                                Enlever résa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-panel" role="region" aria-label="Navigation des pages utilisateurs">
                <p class="muted text-sm">
                    Page {{ $users->currentPage() }} / {{ $users->lastPage() }}
                </p>
                {{ $users->onEachSide(1)->links() }}
                <form method="GET" action="{{ route('admin.users') }}" class="inline-form pagination-jump" aria-label="Aller à une page précise des utilisateurs">
                    <label for="users-page-input" class="sr-only">Numéro de page utilisateurs</label>
                    <input id="users-page-input" type="number" name="page" min="1" max="{{ $users->lastPage() }}"
                           value="{{ $users->currentPage() }}" style="width:90px;" aria-label="Numéro de page">
                    <button type="submit" class="btn btn-sm">Aller</button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection