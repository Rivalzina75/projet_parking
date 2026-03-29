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
                        <button class="btn btn-sm" type="submit" data-requires-consent="true"
                                data-consent-message="Confirmer la mise à jour de la durée par défaut des réservations ?">
                            Mettre à jour
                        </button>
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
                        <button class="btn btn-sm" type="submit" data-requires-consent="true"
                                data-consent-message="Confirmer l'ajout de cette nouvelle place ?"
                                style="background:var(--amber); border-color:var(--amber); color:white;">
                            + Ajouter
                        </button>
                    </form>
                </article>

                {{-- Attribution manuelle --}}
                <article class="card" role="region" aria-label="Attribution manuelle d'une place">
                    <h2 style="font-size:13px; font-weight:700; margin-bottom:10px;">Attribution manuelle</h2>
                    <div class="inline-form" style="margin-bottom:10px;" aria-label="Recherche utilisateur par nom">
                        <label for="user-search" class="sr-only">Rechercher un utilisateur</label>
                        <input type="text" id="user-search" name="user_search"
                               placeholder="Rechercher par prénom ou nom" style="min-width:220px;" aria-label="Recherche utilisateur">
                    </div>

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
                            @foreach($allSpotsForAssign as $spot)
                                <option value="{{ $spot->id }}">
                                    {{ $spot->number }}{{ $spot->location ? ' — '.$spot->location : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-primary" type="submit" data-requires-consent="true"
                                data-consent-message="Confirmer l'attribution manuelle de cette place ?">
                            Attribuer
                        </button>
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
                            <th scope="col">Historique</th>
                            <th scope="col">Suppression</th>
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
                                    <a href="{{ route('admin.places.history', $spot) }}" class="btn btn-sm">Voir tout</a>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.places.delete', $spot) }}" style="display: inline;"
                                          aria-label="Formulaire de suppression de place">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" data-requires-consent="true"
                                                data-consent-message="Êtes-vous sûr de vouloir supprimer la place {{ $spot->number }} ?"
                                                aria-label="Supprimer la place {{ $spot->number }}">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-panel" role="region" aria-label="Navigation des pages des places">
                <p class="muted text-sm">
                    Page {{ $spots->currentPage() }} / {{ $spots->lastPage() }}
                </p>
                {{ $spots->onEachSide(1)->links() }}
                <form method="GET" action="{{ route('admin.places') }}" class="inline-form pagination-jump" aria-label="Aller à une page précise des places">
                    <label for="places-page-input" class="sr-only">Numéro de page places</label>
                    <input id="places-page-input" type="number" name="page" min="1" max="{{ $spots->lastPage() }}"
                           value="{{ $spots->currentPage() }}" style="width:90px;" aria-label="Numéro de page">
                    <button type="submit" class="btn btn-sm">Aller</button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection