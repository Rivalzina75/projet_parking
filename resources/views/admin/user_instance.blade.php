@extends('layouts.app')

@section('title', 'Admin - Fiche utilisateur')

@section('content')
    <a class="nav-link" href="{{ route('admin.users') }}">← Retour</a>
    <h1>Fiche utilisateur</h1>

    <div class="panel mt">
        <div class="cards three">
            <article class="card metric"><h3>Nom</h3><p>{{ $user->name }} {{ $user->lastname }}</p></article>
            <article class="card metric"><h3>Email</h3><p>{{ $user->email }}</p></article>
            <article class="card metric"><h3>Validation</h3><p><span class="status-badge {{ $user->is_validated ? 'validated' : 'pending' }}">{{ $user->is_validated ? 'Validé' : 'En attente' }}</span></p></article>
        </div>

        @if($activeReservation)
            <p class="mt"><strong>Place actuelle:</strong> <span class="status-badge validated">{{ $activeReservation->parkingSpot->number }}</span> (jusqu’au {{ $activeReservation->expires_at->format('d/m/Y H:i') }})</p>
            <form method="POST" action="{{ route('admin.reservation.close', $activeReservation) }}">
                @csrf
                <button class="btn" type="submit">Clôturer la réservation</button>
            </form>
        @endif
    </div>

    <div class="panel mt">
        <h2>Historique des attributions</h2>
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
                    <td>{{ $reservation->ended_at?->format('d/m/Y H:i') ?? 'Active' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Aucun historique.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
