@extends('layouts.app')

@section('title', 'Admin - Places')

@section('content')
<div class="panel page-panel">
    @include('partials.sidebar_admin', ['active' => 'places'])

    <div class="page-body">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div style="font-size:15px;font-weight:500;">Gestion des places</div>
        </div>

        <div class="cards three" style="margin-bottom:18px;">
            <article class="card metric"><h3>Total</h3><p>{{ $total }}</p></article>
            <article class="card metric" style="background:#EAF3DE;border-color:#bce3c8;"><h3 style="color:#3B6D11;">Occupées</h3><p style="color:#27500A;">{{ $occupied }}</p></article>
            <article class="card metric" style="background:#FAEEDA;border-color:#f5d7a1;"><h3 style="color:#854F0B;">Libres</h3><p style="color:#633806;">{{ $free }}</p></article>
        </div>

        <div style="display:grid;gap:14px;margin-bottom:20px;">
            <div style="padding:14px 16px;background:var(--slate-100);border-radius:8px;">
                <div style="font-size:13px;font-weight:500;margin-bottom:10px;">Durée par défaut des réservations</div>
                <form method="POST" action="{{ route('admin.settings') }}" class="row gap">
                    @csrf
                    <input type="number" min="1" max="240" name="default_reservation_hours" value="{{ $defaultDuration }}" required style="width:100px;">
                    <span class="muted" style="font-size:13px;align-self:center;">heures</span>
                    <button class="btn" type="submit">Mettre à jour</button>
                </form>
            </div>

            <div style="padding:14px 16px;background:var(--slate-100);border-radius:8px;">
                <div style="font-size:13px;font-weight:500;margin-bottom:10px;">Ajouter une place</div>
                <form method="POST" action="{{ route('admin.places.store') }}" class="row gap wrap">
                    @csrf
                    <input type="text" name="number" placeholder="Ex: P-14" required style="width:120px;">
                    <input type="text" name="location" placeholder="Ex: Bâtiment A - N-1" style="flex:1;min-width:180px;">
                    <button class="btn" style="background:#854F0B;color:white;border-color:transparent;" type="submit">+ Ajouter</button>
                </form>
            </div>

            <div style="padding:14px 16px;background:var(--slate-100);border-radius:8px;">
                <div style="font-size:13px;font-weight:500;margin-bottom:10px;">Attribuer manuellement une place</div>
                <form method="POST" action="{{ route('admin.places.assign') }}" class="row gap wrap">
                    @csrf
                    <select name="user_id" required>
                        <option value="">Choisir un utilisateur</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} {{ $user->lastname }}</option>
                        @endforeach
                    </select>
                    <select name="spot_id" required>
                        <option value="">Choisir une place</option>
                        @foreach($spots as $spot)
                            <option value="{{ $spot->id }}">{{ $spot->number }}{{ $spot->location ? ' — '.$spot->location : '' }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary" type="submit">Attribuer</button>
                </form>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Emplacement</th>
                    <th>Statut</th>
                    <th>Occupée par</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($spots as $spot)
                    @php $reservation = $activeReservations->get($spot->id); @endphp
                    <tr>
                        <td><strong>{{ $spot->number }}</strong></td>
                        <td class="muted" style="font-size:12px;">{{ $spot->location ?? '-' }}</td>
                        <td>
                            <span class="status-badge {{ $reservation ? 'pending' : 'validated' }}">
                                {{ $reservation ? 'Occupée' : 'Libre' }}
                            </span>
                        </td>
                        <td class="muted">{{ $reservation ? $reservation->user->name.' '.$reservation->user->lastname : '—' }}</td>
                        <td>
                            <div class="actions">
                                <form method="POST" action="{{ route('admin.places.update', $spot) }}" class="row gap wrap">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="number" value="{{ $spot->number }}" required style="width:80px;font-size:12px;padding:4px 8px;">
                                    <input type="text" name="location" value="{{ $spot->location }}" style="width:130px;font-size:12px;padding:4px 8px;">
                                    <label style="font-size:12px;display:flex;align-items:center;gap:4px;">
                                        <input type="checkbox" name="is_active" value="1" {{ $spot->is_active ? 'checked' : '' }}>active
                                    </label>
                                    <button class="btn" type="submit" style="font-size:12px;padding:4px 9px;border-color:#854F0B;color:#854F0B;">Modifier</button>
                                </form>
                                <form method="POST" action="{{ route('admin.places.delete', $spot) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn" type="submit" style="font-size:12px;padding:4px 9px;border-color:#dc2626;color:#dc2626;">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection