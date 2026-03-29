@extends('layouts.app')

@section('title', 'Fiche utilisateur — Admin')

@section('content')
<div class="page-panel">
    @include('partials.sidebar_admin', ['active' => 'users'])

    <div class="page-body">
        <a href="{{ route('admin.users') }}"
           style="display:inline-flex; align-items:center; gap:5px; font-size:13px; color:var(--amber-text); font-weight:600; margin-bottom:18px;">
            ← Retour à la liste
        </a>

        <div class="section-title mb-6">{{ $user->name }} {{ $user->lastname }}</div>

        <div class="cards-2 mb-6">
            {{-- Infos --}}
            <div class="card">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:var(--text-3); margin-bottom:12px;">
                    Informations
                </div>
                @foreach([
                    ['Nom', $user->lastname],
                    ['Prénom', $user->name],
                    ['Email', $user->email],
                    ['Rôle', ucfirst($user->role)],
                    ['Compte créé', $user->created_at->format('d/m/Y')],
                ] as [$l, $v])
                    <div class="flex justify-between"
                         style="padding:7px 0; border-bottom:1px solid var(--border); font-size:13px;">
                        <span class="muted">{{ $l }}</span>
                        <span style="font-weight:600;">{{ $v }}</span>
                    </div>
                @endforeach

                <div class="flex justify-between"
                     style="padding:7px 0; font-size:13px;">
                    <span class="muted">Statut</span>
                    <span>
                        <span class="badge {{ $user->is_validated ? 'badge-green' : 'badge-amber' }}">
                            {{ $user->is_validated ? 'Validé' : 'En attente' }}
                        </span>
                    </span>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex; flex-direction:column; gap:12px;">
                {{-- Place actuelle --}}
                @if($activeReservation)
                    <div class="card" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-color:#6ee7b7;">
                        <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:var(--green-text); margin-bottom:8px;">
                            Place actuelle
                        </div>
                        <div class="spot-number" style="margin-bottom:4px;">{{ $activeReservation->parkingSpot->number }}</div>
                        <div class="text-sm" style="color:var(--green-text); margin-bottom:12px;">
                            Expire le {{ $activeReservation->expires_at->format('d/m/Y à H:i') }}
                        </div>
                        <form method="POST" action="{{ route('admin.reservation.close', $activeReservation) }}">
                            @csrf
                            <button class="btn btn-sm btn-danger w-full" style="justify-content:center;">
                                Clôturer la réservation
                            </button>
                        </form>
                    </div>
                @else
                    <div class="card" style="background:var(--surface-2);">
                        <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:var(--text-3); margin-bottom:8px;">
                            Place actuelle
                        </div>
                        <div class="muted text-sm">Aucune place active</div>
                    </div>
                @endif

                {{-- Actions admin --}}
                <div class="card">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:var(--text-3); margin-bottom:12px;">
                        Actions
                    </div>
                    <div style="display:grid; gap:8px;">
                        @if(!$user->is_validated)
                            <form method="POST" action="{{ route('admin.users.validate', $user) }}">
                                @csrf
                                <button class="btn btn-success w-full" style="justify-content:center;">
                                    Valider le compte
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                            @csrf
                            <button class="btn w-full" style="justify-content:center;">
                                Réinitialiser le mot de passe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Historique --}}
        <div class="section-header">
            <div class="section-title">Historique des places</div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Place</th>
                        <th>Début</th>
                        <th>Expiration prévue</th>
                        <th>Fin réelle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $reservation)
                        <tr>
                            <td><strong>{{ $reservation->parkingSpot->number }}</strong></td>
                            <td class="muted">{{ $reservation->starts_at->format('d/m/Y H:i') }}</td>
                            <td class="muted">{{ $reservation->expires_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($reservation->ended_at)
                                    <span class="muted">{{ $reservation->ended_at->format('d/m/Y H:i') }}</span>
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
    </div>
</div>
@endsection