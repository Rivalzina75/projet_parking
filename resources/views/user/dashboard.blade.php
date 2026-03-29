@extends('layouts.app')

@section('title', 'Tableau de bord — ParkingPro')

@section('content')
<div class="page-panel">
    @include('partials.sidebar_user', ['active' => 'dashboard'])

    <div class="page-body">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <div style="font-size:18px; font-weight:800; letter-spacing:-0.3px;">
                    Bonjour, {{ auth()->user()->name }} 👋
                </div>
                <div class="text-sm muted" style="margin-top:2px;">
                    {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </div>
            </div>
        </div>

        {{-- Statut réservation --}}
        @if($activeReservation)
            <div class="status-card has-spot">
                <div>
                    <div class="spot-label" style="color: var(--green-text);">Ma place actuelle</div>
                    <div class="spot-number">{{ $activeReservation->parkingSpot->number }}</div>
                    <div class="spot-detail" style="color: var(--green-text);">
                        @if($activeReservation->parkingSpot->location)
                            {{ $activeReservation->parkingSpot->location }} ·
                        @endif
                        Expire le {{ $activeReservation->expires_at->format('d/m/Y') }}
                        à {{ $activeReservation->expires_at->format('H:i') }}
                    </div>
                </div>
                <div class="spot-icon green">🅿</div>
            </div>
            <form method="POST" action="{{ route('user.reservation.close', $activeReservation) }}"
                  style="margin-bottom:24px;">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    Libérer ma place
                </button>
            </form>

        @elseif($waitingEntry)
            <div class="status-card waiting">
                <div>
                    <div class="spot-label" style="color: var(--amber-text);">En file d'attente</div>
                    <div class="rank-number">Rang {{ $waitingEntry->position }}</div>
                    <div class="spot-detail" style="color: var(--amber-text);">
                        En attente depuis le {{ $waitingEntry->created_at->format('d/m/Y à H:i') }}
                    </div>
                </div>
                <div class="spot-icon amber">⏳</div>
            </div>
            <div class="notice notice-amber" style="margin-bottom:24px;">
                Vous serez automatiquement notifié et une place vous sera attribuée dès qu'elle se libère.
            </div>

        @else
            <div class="status-card empty">
                <div>
                    <div class="spot-label" style="color: var(--text-3);">Aucune réservation active</div>
                    <div style="font-size:15px; font-weight:600; color:var(--text-2); margin-top:4px;">
                        Disponible pour réserver
                    </div>
                    <div class="spot-detail muted">
                        Une place libre sera attribuée immédiatement. Sinon, vous rejoindrez la file.
                    </div>
                </div>
                <div class="spot-icon gray">🅿</div>
            </div>
            <form method="POST" action="{{ route('user.reservation.request') }}"
                  style="margin-bottom:24px;">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Demander une place
                </button>
            </form>
        @endif

        {{-- Historique --}}
        <div class="section-header">
            <div>
                <div class="section-title">Historique des attributions</div>
                <div class="section-sub">Vos {{ count($history) }} dernières réservations</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Place</th>
                        <th>Début</th>
                        <th>Expiration</th>
                        <th>Fin réelle</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $reservation)
                        <tr>
                            <td><strong>{{ $reservation->parkingSpot->number }}</strong></td>
                            <td class="muted">{{ $reservation->starts_at->format('d/m/Y H:i') }}</td>
                            <td class="muted">{{ $reservation->expires_at->format('d/m/Y H:i') }}</td>
                            <td class="muted">
                                {{ $reservation->ended_at?->format('d/m/Y H:i') ?? '—' }}
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
    </div>
</div>
@endsection