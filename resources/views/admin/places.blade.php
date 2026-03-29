@extends('layouts.app')

@section('title', 'Admin - Places')

@section('content')
    <h1>Gestion des places</h1>
    <p class="muted">Ajout, modification, suppression et attribution manuelle.</p>

    <div class="cards three mt">
        <article class="card metric"><h3>Total</h3><p>{{ $total }}</p></article>
        <article class="card metric"><h3>Occupées</h3><p>{{ $occupied }}</p></article>
        <article class="card metric"><h3>Libres</h3><p>{{ $free }}</p></article>
    </div>

    <div class="panel mt form-panel">
        <h2>Durée par défaut des réservations</h2>
        <form method="POST" action="{{ route('admin.settings') }}" class="row gap">
            @csrf
            <input type="number" min="1" max="240" name="default_reservation_hours" value="{{ $defaultDuration }}" required>
            <button class="btn" type="submit">Mettre à jour</button>
        </form>
    </div>

    <div class="panel mt form-panel">
        <h2>Ajouter une place</h2>
        <form method="POST" action="{{ route('admin.places.store') }}" class="row gap wrap">
            @csrf
            <input type="text" name="number" placeholder="Ex: P-14" required>
            <input type="text" name="location" placeholder="Ex: Bâtiment A - N-1">
            <button class="btn btn-primary" type="submit">Ajouter</button>
        </form>
    </div>

    <div class="panel mt form-panel">
        <h2>Attribuer manuellement une place</h2>
        <form method="POST" action="{{ route('admin.places.assign') }}" class="row gap wrap">
            @csrf

            <select name="user_id" required>
                <option value="">Utilisateur validé</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->lastname }} ({{ $user->email }})</option>
                @endforeach
            </select>

            <select name="spot_id" required>
                <option value="">Place</option>
                @foreach($spots as $spot)
                    <option value="{{ $spot->id }}">{{ $spot->number }} - {{ $spot->location ?? 'Sans emplacement' }}</option>
                @endforeach
            </select>

            <button class="btn btn-primary" type="submit">Attribuer</button>
        </form>
    </div>

    <div class="panel mt">
        <table>
            <thead>
            <tr>
                <th>Numéro</th>
                <th>Emplacement</th>
                <th>Statut</th>
                <th>Occupant</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($spots as $spot)
                @php $reservation = $activeReservations->get($spot->id); @endphp
                <tr>
                    <td>{{ $spot->number }}</td>
                    <td>{{ $spot->location ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $reservation ? 'pending' : 'validated' }}">
                            {{ $reservation ? 'Occupée' : 'Libre' }}
                        </span>
                    </td>
                    <td>{{ $reservation ? $reservation->user->name.' '.$reservation->user->lastname : '-' }}</td>
                    <td class="actions">
                        <form method="POST" action="{{ route('admin.places.update', $spot) }}" class="row gap wrap">
                            @csrf
                            @method('PUT')
                            <input type="text" name="number" value="{{ $spot->number }}" required>
                            <input type="text" name="location" value="{{ $spot->location }}">
                            <label><input type="checkbox" name="is_active" value="1" {{ $spot->is_active ? 'checked' : '' }}> active</label>
                            <button class="btn" type="submit">Modifier</button>
                        </form>
                        <form method="POST" action="{{ route('admin.places.delete', $spot) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
