@extends('layouts.app')

@section('title', 'Historique place — Admin')

@section('content')
<div class="page-panel" role="main">
    @include('partials.sidebar_admin', ['active' => 'places'])

    <div class="page-body">
        <div class="section-header mb-6">
            <div>
                <h1 class="section-title">Historique complet de la place {{ $spot->number }}</h1>
                <p class="section-sub">Tous les utilisateurs ayant réservé cette place</p>
            </div>
            <a href="{{ route('admin.places') }}" class="btn btn-sm">Retour places</a>
        </div>

        <div class="table-wrap" role="region" aria-label="Historique complet de la place">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Utilisateur</th>
                        <th scope="col">Email</th>
                        <th scope="col">Début</th>
                        <th scope="col">Expiration</th>
                        <th scope="col">Fin réelle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $reservation)
                        <tr>
                            <td><strong>{{ $reservation->user->name }} {{ $reservation->user->lastname }}</strong></td>
                            <td class="muted text-sm">{{ $reservation->user->email }}</td>
                            <td class="muted">{{ $reservation->starts_at->format('d/m/Y H:i') }}</td>
                            <td class="muted">{{ $reservation->expires_at->format('d/m/Y H:i') }}</td>
                            <td class="muted">{{ $reservation->ended_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted text-center" style="padding:24px;">Aucun historique pour cette place.</td>
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
