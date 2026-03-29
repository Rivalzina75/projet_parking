@extends('layouts.app')

@section('title', 'Historique complet — ParkingPro')

@section('content')
<div class="page-panel" role="main">
    @include('partials.sidebar_user', ['active' => 'dashboard'])

    <div class="page-body">
        <div class="section-header mb-6">
            <div>
                <h1 class="section-title">Historique complet des réservations</h1>
                <p class="section-sub">Toutes vos places réservées, datées par ordre décroissant</p>
            </div>
            <a href="{{ route('user.dashboard') }}" class="btn btn-sm">Retour dashboard</a>
        </div>

        <div class="table-wrap" role="region" aria-label="Historique complet utilisateur">
            <table>
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
                            <td class="muted">{{ $reservation->starts_at->format('d/m/Y H:i') }}</td>
                            <td class="muted">{{ $reservation->expires_at->format('d/m/Y H:i') }}</td>
                            <td class="muted">{{ $reservation->ended_at?->format('d/m/Y H:i') ?? '—' }}</td>
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
                            <td colspan="5" class="muted text-center" style="padding:24px;">Aucun historique de réservation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;">
            {{ $history->links() }}
        </div>
    </div>
</div>
@endsection
