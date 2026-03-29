@extends('layouts.app')

@section('title', 'Dashboard utilisateur')

@section('content')
<div class="panel page-panel">
    @include('partials.sidebar_user', ['active' => 'dashboard'])

    <div class="page-body">
        <div style="margin-bottom:18px;">
            <div style="font-size:15px;font-weight:500;color:var(--slate-900);">Bonjour, {{ auth()->user()->name }}</div>
            <div class="muted" style="font-size:12px;">{{ \Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</div>
        </div>

        @if($activeReservation)
            <div style="padding:18px;background:#EAF3DE;border-radius:10px;display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <div>
                    <div style="font-size:10px;color:#3B6D11;font-weight:500;letter-spacing:0.5px;margin-bottom:4px;">MA PLACE ACTUELLE</div>
                    <div style="font-size:36px;font-weight:500;color:#27500A;margin-bottom:2px;">{{ $activeReservation->parkingSpot->number }}</div>
                    <div style="font-size:12px;color:#3B6D11;">
                        {{ $activeReservation->parkingSpot->location ?? 'Emplacement non renseigné' }}
                        · Expire le {{ $activeReservation->expires_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div style="width:56px;height:56px;background:#3B6D11;border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-size:22px;font-weight:500;flex-shrink:0;">P</div>
            </div>
            <form method="POST" action="{{ route('user.reservation.close', $activeReservation) }}" style="margin-bottom:20px;">
                @csrf
                <button class="btn" type="submit">Clôturer ma réservation</button>
            </form>

        @elseif($waitingEntry)
            <div style="padding:18px;background:#FAEEDA;border-radius:10px;margin-bottom:20px;">
                <div style="font-size:10px;color:#854F0B;font-weight:500;letter-spacing:0.5px;margin-bottom:4px;">EN FILE D'ATTENTE</div>
                <div style="font-size:28px;font-weight:500;color:#633806;margin-bottom:4px;">Rang {{ $waitingEntry->position }}</div>
                <div style="font-size:12px;color:#854F0B;">En attente depuis le {{ $waitingEntry->created_at->format('d/m/Y H:i') }}</div>
            </div>

        @else
            <div style="padding:18px;background:var(--slate-100);border-radius:10px;margin-bottom:20px;border:1px solid var(--slate-200);">
                <div style="font-size:13px;font-weight:500;margin-bottom:8px;">Aucune réservation active</div>
                <p class="muted" style="font-size:12px;margin-bottom:14px;">Une place libre vous sera attribuée immédiatement. Si aucune n'est disponible, vous serez placé en file d'attente.</p>
                <form method="POST" action="{{ route('user.reservation.request') }}">
                    @csrf
                    <button class="btn btn-primary" type="submit">Faire une demande de réservation</button>
                </form>
            </div>
        @endif

        <div style="font-size:13px;font-weight:500;color:var(--slate-900);margin-bottom:10px;">Historique des attributions</div>
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
                        <td>
                            @if($reservation->ended_at)
                                {{ $reservation->ended_at->format('d/m/Y H:i') }}
                            @else
                                <span class="status-badge validated">Active</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Aucun historique.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection