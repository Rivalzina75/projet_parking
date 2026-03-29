@extends('layouts.app')

@section('title', 'Tableau de bord — ParkingPro')

@section('content')
<div class="page-panel" role="main">
    @include('partials.sidebar_user', ['active' => 'dashboard'])

    <div class="page-body">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6" role="region" aria-label="Bienvenue">
            <div>
                <h1 style="font-size:18px; font-weight:800; letter-spacing:-0.3px; margin:0;">
                    Bonjour, <span aria-label="Prénom de l'utilisateur">{{ auth()->user()->name }}</span> 👋
                </h1>
                <time class="text-sm muted" style="margin-top:2px; display:block;">
                    {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </time>
            </div>
        </div>

        {{-- Statut réservation --}}
        <section aria-label="Statut de réservation actuel" role="region">
        @if($activeReservation)
            <article class="status-card has-spot" role="status" aria-live="polite">
                <div>
                    <div class="spot-label" style="color: var(--green-text);">Ma place actuelle</div>
                    <div class="spot-number" aria-label="Numéro de place">{{ $activeReservation->parkingSpot->number }}</div>
                    <div class="spot-detail" style="color: var(--green-text);">
                        @if($activeReservation->parkingSpot->location)
                            <span aria-label="Localisation">{{ $activeReservation->parkingSpot->location }}</span> ·
                        @endif
                        <time datetime="{{ $activeReservation->expires_at->toIso8601String() }}" aria-label="Date et heure d'expiration">
                            Expire le {{ $activeReservation->expires_at->format('d/m/Y') }}
                            à {{ $activeReservation->expires_at->format('H:i') }}
                        </time>
                    </div>
                </div>
                <div class="spot-icon green" aria-hidden="true">🅿</div>
            </article>
            <form method="POST" action="{{ route('user.reservation.close', $activeReservation) }}"
                  style="margin-bottom:24px;" aria-label="Formulaire de libération de place">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    Libérer ma place
                </button>
            </form>

        @elseif($waitingEntry)
            <article class="status-card waiting" role="status" aria-live="polite">
                <div>
                    <div class="spot-label" style="color: var(--amber-text);">En file d'attente</div>
                    <div class="rank-number" aria-label="Position en file d'attente">Rang {{ $waitingEntry->position }}</div>
                    <div class="spot-detail" style="color: var(--amber-text);">
                        <time datetime="{{ $waitingEntry->created_at->toIso8601String() }}" aria-label="Date et heure d'insertion en file">
                            En attente depuis le {{ $waitingEntry->created_at->format('d/m/Y à H:i') }}
                        </time>
                    </div>
                </div>
                <div class="spot-icon amber" aria-hidden="true">⏳</div>
            </article>
            <div class="notice notice-amber" style="margin-bottom:24px;" role="status">
                Vous serez automatiquement notifié et une place vous sera attribuée dès qu'elle se libère.
            </div>

        @else
            <article class="status-card empty" role="status" aria-live="polite">
                <div>
                    <div class="spot-label" style="color: var(--text-3);">Aucune réservation active</div>
                    <div style="font-size:15px; font-weight:600; color:var(--text-2); margin-top:4px;">
                        Disponible pour réserver
                    </div>
                    <div class="spot-detail muted">
                        Une place libre sera attribuée immédiatement. Sinon, vous rejoindrez la file.
                    </div>
                </div>
                <div class="spot-icon gray" aria-hidden="true">🅿</div>
            </article>
            <form method="POST" action="{{ route('user.reservation.request') }}"
                  style="margin-bottom:24px;" aria-label="Formulaire de demande de place">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Demander une place
                </button>
            </form>
        @endif
        </section>

        {{-- Historique --}}
        <section aria-label="Historique des réservations" role="region">
        <div class="section-header">
            <div>
                <h2 class="section-title">Historique des attributions</h2>
                <p class="section-sub">Vos {{ count($history) }} dernières réservations</p>
            </div>
        </div>

        <div class="table-wrap" role="region" aria-label="Tableau d'historique">
            <table role="table" aria-label="Historique complet des réservations">
                <thead>
                    <tr>
                        <th scope="col">Place</th>
                        <th scope="col">Début</th>
                        <th scope="col">Expiration</th>
                        <th scope="col">Fin réelle</th>
                        <th scope="col">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $reservation)
                        <tr>
                            <td><strong>{{ $reservation->parkingSpot->number }}</strong></td>
                            <td class="muted"><time datetime="{{ $reservation->starts_at->toIso8601String() }}">{{ $reservation->starts_at->format('d/m/Y H:i') }}</time></td>
                            <td class="muted"><time datetime="{{ $reservation->expires_at->toIso8601String() }}">{{ $reservation->expires_at->format('d/m/Y H:i') }}</time></td>
                            <td class="muted">
                                @if($reservation->ended_at)
                                    <time datetime="{{ $reservation->ended_at->toIso8601String() }}">{{ $reservation->ended_at->format('d/m/Y H:i') }}</time>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if(!$reservation->ended_at && $reservation->expires_at->isFuture())
                                    <span class="badge badge-green">Active</span>
                                @else
                                    <span class="badge badge-gray">Terminée</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted text-center" style="padding:28px;">
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