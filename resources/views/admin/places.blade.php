@extends('layouts.app')

@section('title', 'Places — Admin')

@section('content')
<div class="page-panel">
    @include('partials.sidebar_admin', ['active' => 'places'])

    <div class="page-body">
        <div class="section-header mb-6">
            <div class="section-title">Gestion des places</div>
        </div>

        {{-- Métriques --}}
        <div class="cards-3 mb-6">
            <div class="metric">
                <div class="metric-label">Total places</div>
                <div class="metric-value">{{ $total }}</div>
            </div>
            <div class="metric" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-color:#6ee7b7;">
                <div class="metric-label" style="color:var(--green-text);">Occupées</div>
                <div class="metric-value" style="color:var(--green-text);">{{ $occupied }}</div>
            </div>
            <div class="metric" style="background:linear-gradient(135deg,#fffbeb,#fef3c7); border-color:#fcd34d;">
                <div class="metric-label" style="color:var(--amber-text);">Libres</div>
                <div class="metric-value" style="color:var(--amber-text);">{{ $free }}</div>
            </div>
        </div>

        {{-- Panneaux de configuration --}}
        <div style="display:grid; gap:12px; margin-bottom:24px;">

            {{-- Durée par défaut --}}
            <div class="card">
                <div style="font-size:13px; font-weight:700; margin-bottom:10px;">
                    Durée par défaut des réservations
                </div>
                <form method="POST" action="{{ route('admin.settings') }}" class="inline-form">
                    @csrf
                    <input type="number" min="1" max="240" name="default_reservation_hours"
                           value="{{ $defaultDuration }}" required style="width:90px;">
                    <span class="muted text-sm">heures</span>
                    <button class="btn btn-sm" type="submit">Mettre à jour</button>
                </form>
            </div>

            {{-- Ajouter une place --}}
            <div class="card">
                <div style="font-size:13px; font-weight:700; margin-bottom:10px;">Ajouter une place</div>
                <form method="POST" action="{{ route('admin.places.store') }}" class="inline-form">
                    @csrf
                    <input type="text" name="number" placeholder="N° ex: P-14" required style="width:110px;">
                    <input type="text" name="location" placeholder="Emplacement ex: Bâtiment A - N-1"
                           style="flex:1; min-width:160px;">
                    <button class="btn btn-sm" type="submit"
                            style="background:var(--amber); border-color:var(--amber); color:white;">
                        + Ajouter
                    </button>
                </form>
            </div>

            {{-- Attribution manuelle --}}
            <div class="card">
                <div style="font-size:13px; font-weight:700; margin-bottom:10px;">Attribution manuelle</div>
                <form method="POST" action="{{ route('admin.places.assign') }}" class="inline-form">
                    @csrf
                    <select name="user_id" required>
                        <option value="">— Choisir un utilisateur —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} {{ $user->lastname }}</option>
                        @endforeach
                    </select>
                    <select name="spot_id" required>
                        <option value="">— Choisir une place —</option>
                        @foreach($spots as $spot)
                            <option value="{{ $spot->id }}">
                                {{ $spot->number }}{{ $spot->location ? ' — '.$spot->location : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary" type="submit">Attribuer</button>
                </form>
            </div>
        </div>

        {{-- Table des places --}}
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Emplacement</th>
                        <th>Statut</th>
                        <th>Occupée par</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($spots as $spot)
                        @php $reservation = $activeReservations->get($spot->id); @endphp
                        <tr>
                            <td><strong>{{ $spot->number }}</strong></td>
                            <td class="muted text-sm">{{ $spot->location ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $reservation ? 'badge-green' : 'badge-gray' }}">
                                    {{ $reservation ? 'Occupée' : 'Libre' }}
                                </span>
                            </td>
                            <td class="muted text-sm">
                                {{ $reservation ? $reservation->user->name.' '.$reservation->user->lastname : '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $spot->is_active ? 'badge-blue' : 'badge-red' }}">
                                    {{ $spot->is_active ? 'Oui' : 'Non' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <form method="POST" action="{{ route('admin.places.update', $spot) }}"
                                          class="inline-form">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="number" value="{{ $spot->number }}"
                                               required style="width:65px; font-size:12px; padding:4px 8px;">
                                        <input type="text" name="location" value="{{ $spot->location }}"
                                               style="width:120px; font-size:12px; padding:4px 8px;">
                                        <label style="font-size:12px; display:flex; align-items:center; gap:4px; font-weight:500; color:var(--text-2);">
                                            <input type="checkbox" name="is_active" value="1"
                                                   {{ $spot->is_active ? 'checked' : '' }}>Active
                                        </label>
                                        <button class="btn btn-sm" type="submit"
                                                style="border-color:var(--amber); color:var(--amber-text);">Modifier</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.places.delete', $spot) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection