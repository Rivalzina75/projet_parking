@extends('layouts.app')

@section('title', 'Admin - Fiche utilisateur')

@section('content')
<div class="panel page-panel">
    @include('partials.sidebar_admin', ['active' => 'users'])

    <div class="page-body">
        <a class="muted" href="{{ route('admin.users') }}" style="font-size:12px;color:#854F0B;display:inline-block;margin-bottom:14px;">← Retour à la liste</a>
        <div style="font-size:15px;font-weight:500;margin-bottom:18px;">Fiche utilisateur — {{ $user->name }} {{ $user->lastname }}</div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
            <div style="padding:14px 16px;background:var(--slate-100);border-radius:8px;">
                <div style="font-size:11px;color:var(--slate-500);font-weight:500;margin-bottom:10px;letter-spacing:0.4px;">INFORMATIONS</div>
                @foreach([['Nom', $user->lastname], ['Prénom', $user->name], ['Email', $user->email], ['Rôle', ucfirst($user->role)], ['Compte créé', $user->created_at->format('d/m/Y')], ['Statut', $user->is_validated ? 'Actif' : 'En attente']] as [$l, $v])
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--slate-200);font-size:12px;">
                        <span class="muted">{{ $l }}</span><span>{{ $v }}</span>
                    </div>
                @endforeach
            </div>

            <div style="display:flex;flex-direction:column;gap:12px;">
                @if($activeReservation)
                    <div style="padding:14px 16px;background:#EAF3DE;border-radius:8px;">
                        <div style="font-size:11px;color:#3B6D11;font-weight:500;margin-bottom:8px;letter-spacing:0.4px;">PLACE ACTUELLE</div>
                        <div style="font-size:30px;font-weight:500;color:#27500A;margin-bottom:2px;">{{ $activeReservation->parkingSpot->number }}</div>
                        <div style="font-size:12px;color:#3B6D11;margin-bottom:10px;">Expire le {{ $activeReservation->expires_at->format('d/m/Y H:i') }}</div>
                        <form method="POST" action="{{ route('admin.reservation.close', $activeReservation) }}">
                            @csrf
                            <button style="width:100%;padding:7px;font-size:12px;border-radius:6px;border:none;background:#3B6D11;color:white;cursor:pointer;">Clôturer la réservation</button>
                        </form>
                    </div>
                @else
                    <div style="padding:14px 16px;background:var(--slate-100);border-radius:8px;">
                        <div style="font-size:11px;color:var(--slate-500);font-weight:500;margin-bottom:8px;">PLACE ACTUELLE</div>
                        <div class="muted" style="font-size:13px;">Aucune place active</div>
                    </div>
                @endif

                <div style="padding:14px 16px;background:var(--slate-100);border-radius:8px;">
                    <div style="font-size:11px;color:var(--slate-500);font-weight:500;margin-bottom:10px;letter-spacing:0.4px;">ACTIONS</div>
                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" style="margin-bottom:7px;">
                        @csrf
                        <button style="width:100%;padding:7px;font-size:12px;border-radius:6px;border:1px solid #854F0B;background:transparent;color:#854F0B;cursor:pointer;">Réinitialiser le mot de passe</button>
                    </form>
                    @if(!$user->is_validated)
                        <form method="POST" action="{{ route('admin.users.validate', $user) }}">
                            @csrf
                            <button style="width:100%;padding:7px;font-size:12px;border-radius:6px;border:1px solid #3B6D11;background:transparent;color:#3B6D11;cursor:pointer;">Valider le compte</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div style="font-size:13px;font-weight:500;margin-bottom:10px;">Historique des places</div>
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
                        <td><strong>{{ $reservation->parkingSpot->number }}</strong></td>
                        <td>{{ $reservation->starts_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $reservation->expires_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $reservation->ended_at?->format('d/m/Y H:i') ?? 'Active' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Aucun historique.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection