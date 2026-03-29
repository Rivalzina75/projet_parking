@extends('layouts.app')

@section('title', 'Dashboard utilisateur')

@section('content')
    <h1>Tableau de bord</h1>
    <p class="muted">Consultez votre état actuel et votre historique de réservation.</p>

    @if($activeReservation)
        <div class="panel highlight success">
            <span class="pill success">Place attribuée</span>
            <h2>Votre place actuelle : {{ $activeReservation->parkingSpot->number }}</h2>
            <p>Emplacement: {{ $activeReservation->parkingSpot->location ?? 'Non renseigné' }}</p>
            <p>Expire le {{ $activeReservation->expires_at->format('d/m/Y H:i') }}</p>
            <form method="POST" action="{{ route('user.reservation.close', $activeReservation) }}">
                @csrf
                <button class="btn" type="submit">Clôturer ma réservation</button>
            </form>
        </div>
    @elseif($waitingEntry)
        <div class="panel highlight warning">
            <span class="pill warning">En attente</span>
            <h2>Vous êtes en file d’attente</h2>
            <p>Position actuelle : {{ $waitingEntry->position }}</p>
            <p>Inscription le {{ $waitingEntry->created_at->format('d/m/Y H:i') }}</p>
        </div>
    @else
        <div class="panel highlight info">
            <span class="pill">Aucune place active</span>
            <h2>Aucune réservation active</h2>
            <form method="POST" action="{{ route('user.reservation.request') }}">
                @csrf
                <button class="btn btn-primary" type="submit">Faire une demande de réservation</button>
            </form>
        </div>
    @endif

    <div class="panel mt">
        <h2>Historique des places</h2>
        <table>
            <thead>
            <tr>
                <th>Place</th>
                <th>Début</th>
                <th>Fin prévue</th>
                <th>Fin réelle</th>
            </tr>
            </thead>
            <tbody>
            @forelse($history as $reservation)
                <tr>
                    <td>{{ $reservation->parkingSpot->number }}</td>
                    <td>{{ $reservation->starts_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $reservation->expires_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($reservation->ended_at)
                            {{ $reservation->ended_at->format('d/m/Y H:i') }}
                        @else
                            <span class="status-badge validated">Active</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Aucun historique.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
