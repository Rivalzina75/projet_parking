@extends('layouts.app')

@section('title', 'Places — Admin')

@section('content')
<div class="page-panel" role="main">
    @include('partials.sidebar_admin', ['active' => 'places'])

    <div class="page-body">
        <section aria-label="Gestion des places de parking">
            <div class="section-header mb-6">
                <h1 class="section-title">Gestion des places</h1>
            </div>

            {{-- Métriques --}}
            <div class="cards-3 mb-6" role="region" aria-label="Statistiques des places">
                <article class="metric" role="article">
                    <div class="metric-label">Total places</div>
                    <div class="metric-value" aria-label="Total de places : {{ $total }}">{{ $total }}</div>
                </article>
                <article class="metric" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-color:#6ee7b7;">
                    <div class="metric-label" style="color:var(--green-text);">Occupées</div>
                    <div class="metric-value" style="color:var(--green-text);" aria-label="Places occupées : {{ $occupied }}">{{ $occupied }}</div>
                </article>
                <article class="metric" style="background:linear-gradient(135deg,#fffbeb,#fef3c7); border-color:#fcd34d;">
                    <div class="metric-label" style="color:var(--amber-text);">Libres</div>
                    <div class="metric-value" style="color:var(--amber-text);" aria-label="Places libres : {{ $free }}">{{ $free }}</div>
                </article>
            </div>

            {{-- Panneaux de configuration --}}
            <div style="display:grid; gap:12px; margin-bottom:24px;">

                {{-- Durée par défaut --}}
                <article class="card" role="region" aria-label="Configuration de la durée des réservations">
                    <h2 style="font-size:13px; font-weight:700; margin-bottom:10px;">
                        Durée par défaut des réservations
                    </h2>
                    <form method="POST" action="{{ route('admin.settings') }}" class="inline-form" aria-label="Formulaire de durée par défaut">
                        @csrf
                        <label for="default-duration" class="sr-only">Durée en heures</label>
                        <input type="number" id="default-duration" min="1" max="240" name="default_reservation_hours"
                               value="{{ $defaultDuration }}" required style="width:90px;" aria-label="Durée par défaut en heures">
                        <span class="muted text-sm">heures</span>
                        <button class="btn btn-sm" type="submit">Mettre à jour</button>
                    </form>
                </article>

                {{-- Ajouter une place --}}
                <article class="card" role="region" aria-label="Ajout d'une nouvelle place">
                    <h2 style="font-size:13px; font-weight:700; margin-bottom:10px;">Ajouter une place</h2>
                    <form method="POST" action="{{ route('admin.places.store') }}" class="inline-form" aria-label="Formulaire d'ajout de place">
                        @csrf
                        <label for="place-number" class="sr-only">Numéro de place</label>
                        <input type="text" id="place-number" name="number" placeholder="N° ex: P-14" required maxlength="30" style="width:110px;" aria-label="Numéro de place">
                        
                        <label for="place-location" class="sr-only">Emplacement</label>
                        <input type="text" id="place-location" name="location" placeholder="Emplacement ex: Bâtiment A - N-1"
                               maxlength="255" style="flex:1; min-width:160px;" aria-label="Emplacement de la place">
                        <button class="btn btn-sm" type="submit"
                                style="background:var(--amber); border-color:var(--amber); color:white;">
                            + Ajouter
                        </button>
                    </form>
                </article>

                {{-- Attribution manuelle --}}
                <article class="card" role="region" aria-label="Attribution manuelle d'une place">
                    <h2 style="font-size:13px; font-weight:700; margin-bottom:10px;">Attribution manuelle</h2>
                    <form method="POST" action="{{ route('admin.places.assign') }}" class="inline-form" aria-label="Formulaire d'attribution manuelle">
                        @csrf
                        <label for="user-select" class="sr-only">Sélectionner un utilisateur</label>
                        <select id="user-select" name="user_id" required aria-label="Utilisateur">
                            <option value="">— Choisir un utilisateur —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} {{ $user->lastname }}</option>
                            @endforeach
                        </select>
                        
                        <label for="spot-select" class="sr-only">Sélectionner une place</label>
                        <select id="spot-select" name="spot_id" required aria-label="Place de parking">
                            <option value="">— Choisir une place —</option>
                            @foreach($spots as $spot)
                                <option value="{{ $spot->id }}">
                                    {{ $spot->number }}{{ $spot->location ? ' — '.$spot->location : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-primary" type="submit">Attribuer</button>
                    </form>
                </article>
            </div>

            {{-- Table des places --}}
            <div class="table-wrap" role="region" aria-label="Tableau des places de parking">
                <table role="table" aria-label="Liste complète des places de parking">
                    <thead>
                        <tr>
                            <th scope="col">Numéro</th>
                            <th scope="col">Emplacement</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Occupée par</th>
                            <th scope="col">Active</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($spots as $spot)
                            @php $reservation = $activeReservations->get($spot->id); @endphp
                            <tr>
                                <td><strong>{{ $spot->number }}</strong></td>
                                <td class="muted text-sm">{{ $spot->location ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $reservation ? 'badge-green' : 'badge-gray' }}"
                                          role="status" aria-label="{{ $reservation ? 'Occupée' : 'Libre' }}">
                                        {{ $reservation ? 'Occupée' : 'Libre' }}
                                    </span>
                                </td>
                                <td class="muted text-sm">
                                    {{ $reservation ? $reservation->user->name.' '.$reservation->user->lastname : '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $spot->is_active ? 'badge-blue' : 'badge-red' }}"
                                          role="status" aria-label="{{ $spot->is_active ? 'Place active' : 'Place inactive' }}">
                                        {{ $spot->is_active ? 'Oui' : 'Non' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions" role="toolbar" aria-label="Actions pour cette place">
                                        <form method="POST" action="{{ route('admin.places.update', $spot) }}"
                                              class="inline-form" aria-label="Formulaire de modification de place">
                                            @csrf
                                            @method('PUT')
                                            <label for="number-{{ $spot->id }}" class="sr-only">Numéro</label>
                                            <input type="text" id="number-{{ $spot->id }}" name="number" value="{{ $spot->number }}"
                                                   required style="width:65px; font-size:12px; padding:4px 8px;" aria-label="Numéro de place">
                                            
                                            <label for="location-{{ $spot->id }}" class="sr-only">Emplacement</label>
                                            <input type="text" id="location-{{ $spot->id }}" name="location" value="{{ $spot->location }}"
                                                   style="width:120px; font-size:12px; padding:4px 8px;" aria-label="Emplacement">
                                            
                                            <label style="font-size:12px; display:flex; align-items:center; gap:4px; font-weight:500; color:var(--text-2);">
                                                <input type="checkbox" name="is_active" value="1"
                                                       {{ $spot->is_active ? 'checked' : '' }} aria-label="Activer cette place">
                                                Active
                                            </label>
                                            <button class="btn btn-sm" type="submit"
                                                    style="border-color:var(--amber); color:var(--amber-text);"
                                                    aria-label="Modifier la place {{ $spot->number }}">
                                                Modifier
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.places.delete', $spot) }}" style="display: inline;"
                                              aria-label="Formulaire de suppression de place">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" type="submit"
                                                    aria-label="Supprimer la place {{ $spot->number }}">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection