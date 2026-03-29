@extends('layouts.app')

@section('title', 'Fiche utilisateur — Admin')

@section('content')
<div class="page-panel" role="main">
    @include('partials.sidebar_admin', ['active' => 'users'])

    <div class="page-body">
        <a href="{{ route('admin.users') }}"
           style="display:inline-flex; align-items:center; gap:5px; font-size:13px; color:var(--amber-text); font-weight:600; margin-bottom:18px;"
           aria-label="Retour liste utilisateurs">
            ← Retour à la liste
        </a>

        <h1 class="section-title mb-6" role="main">{{ $user->name }} {{ $user->lastname }}</h1>

        <div class="cards-2 mb-6" role="region" aria-label="Informations et actions utilisateur">
            {{-- Infos --}}
            <article class="card" role="region" aria-label="Informations utilisateur">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:var(--text-3); margin-bottom:12px;">
                    Informations
                </div>
                <dl>
                    @foreach([
                        ['Nom', $user->lastname],
                        ['Prénom', $user->name],
                        ['Email', $user->email],
                        ['Rôle', ucfirst($user->role)],
                        ['Compte créé', $user->created_at->format('d/m/Y')],
                    ] as [$l, $v])
                        <div class="flex justify-between"
                             style="padding:7px 0; border-bottom:1px solid var(--border); font-size:13px;">
                            <dt class="muted">{{ $l }}</dt>
                            <dd style="font-weight:600; margin:0;">{{ $v }}</dd>
                        </div>
                    @endforeach

                    <div class="flex justify-between"
                         style="padding:7px 0; font-size:13px;">
                        <dt class="muted">Statut</dt>
                        <dd style="margin:0;">
                            <span class="badge {{ $user->is_validated ? 'badge-green' : 'badge-amber' }}"
                                  role="status" aria-label="{{ $user->is_validated ? 'Compte validé' : 'Compte en attente' }}">
                                {{ $user->is_validated ? 'Validé' : 'En attente' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </article>

            {{-- Actions --}}
            <aside style="display:flex; flex-direction:column; gap:12px;" role="region" aria-label="Actions d'administration">
                {{-- Place actuelle --}}
                @if($activeReservation)
                    <article class="card" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-color:#6ee7b7;" role="status" aria-live="polite" aria-label="Place actuelle">
                        <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:var(--green-text); margin-bottom:8px;">
                            Place actuelle
                        </div>
                        <div class="spot-number" style="margin-bottom:4px;" aria-label="Numéro de place">{{ $activeReservation->parkingSpot->number }}</div>
                        <time class="text-sm" style="color:var(--green-text); margin-bottom:12px; display:block;"
                              datetime="{{ $activeReservation->expires_at->toIso8601String() }}"
                              aria-label="Date d'expiration">
                            Expire le {{ $activeReservation->expires_at->format('d/m/Y à H:i') }}
                        </time>
                        <form method="POST" action="{{ route('admin.reservation.close', $activeReservation) }}" aria-label="Formulaire de clôture">
                            @csrf
                            <button class="btn btn-sm btn-danger w-full" style="justify-content:center;" aria-label="Clôturer la réservation de {{ $user->name }}">
                                Clôturer la réservation
                            </button>
                        </form>
                    </article>
                @else
                    <article class="card" style="background:var(--surface-2);" role="status">
                        <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:var(--text-3); margin-bottom:8px;">
                            Place actuelle
                        </div>
                        <div class="muted text-sm">Aucune place active</div>
                    </article>
                @endif

                {{-- Actions admin --}}
                <article class="card" role="region" aria-label="Actions d'administration">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:var(--text-3); margin-bottom:12px;">
                        Actions
                    </div>
                    <div style="display:grid; gap:8px;">
                        @if(!$user->is_validated)
                            <form method="POST" action="{{ route('admin.users.validate', $user) }}" aria-label="Valider le compte">
                                @csrf
                                <button class="btn btn-success w-full" style="justify-content:center;" aria-label="Valider le compte de {{ $user->name }} {{ $user->lastname }}">
                                    Valider le compte
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" aria-label="Réinitialiser le mot de passe">
                            @csrf
                            <button class="btn w-full" style="justify-content:center;" aria-label="Réinitialiser le mot de passe de {{ $user->name }} {{ $user->lastname }}">
                                Réinitialiser le mot de passe
                            </button>
                        </form>
                    </div>
                </article>
            </aside>
        </div>

        {{-- Historique --}}
        <section aria-label="Historique des réservations">
            <div class="section-header">
                <h2 class="section-title">Historique des places</h2>
            </div>

            <div class="table-wrap" role="region" aria-label="Tableau d'historique des réservations">
                <table role="table" aria-label="Historique complet des réservations pour {{ $user->name }} {{ $user->lastname }}">
                    <thead>
                        <tr>
                            <th scope="col">Place</th>
                            <th scope="col">Début</th>
                            <th scope="col">Expiration prévue</th>
                            <th scope="col">Fin réelle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $reservation)
                            <tr>
                                <td><strong>{{ $reservation->parkingSpot->number }}</strong></td>
                                <td class="muted"><time datetime="{{ $reservation->starts_at->toIso8601String() }}">{{ $reservation->starts_at->format('d/m/Y H:i') }}</time></td>
                                <td class="muted"><time datetime="{{ $reservation->expires_at->toIso8601String() }}">{{ $reservation->expires_at->format('d/m/Y H:i') }}</time></td>
                                <td>
                                    @if($reservation->ended_at)
                                        <time class="muted" datetime="{{ $reservation->ended_at->toIso8601String() }}">{{ $reservation->ended_at->format('d/m/Y H:i') }}</time>
                                    @else
                                        <span class="badge badge-green">Active</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="muted text-center" style="padding:24px;">
                                    Aucun historique de réservation.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection