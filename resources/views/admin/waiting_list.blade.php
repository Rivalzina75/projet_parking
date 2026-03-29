@extends('layouts.app')

@section('title', "File d'attente — Admin")

@section('content')
<div class="page-panel" role="main">
    @include('partials.sidebar_admin', ['active' => 'waiting'])

    <div class="page-body">
        <section aria-label="Gestion de la file d'attente">
            <div class="section-header mb-6">
                <div>
                    <h1 class="section-title">File d'attente</h1>
                    <p class="section-sub" role="status">
                        {{ $waiting->total() }} utilisateur(s) en attente · Les positions sont attribuées par ordre d'arrivée
                    </p>
                </div>
            </div>

            @if($waiting->count() === 0)
                <article class="card text-center" style="padding:48px; color:var(--text-3);" role="status">
                    <div style="font-size:32px; margin-bottom:12px;" aria-hidden="true">🎉</div>
                    <h2 style="font-weight:600; font-size:15px; color:var(--text-2); margin:0 0 8px;">File d'attente vide</h2>
                    <p class="text-sm muted" style="margin:0;">Tous les utilisateurs ont une place attribuée.</p>
                </article>
            @else
                <div class="table-wrap" role="region" aria-label="Tableau de la file d'attente">
                    <table role="table" aria-label="Liste ordonnée des utilisateurs en attente">
                        <thead>
                            <tr>
                                <th scope="col">Rang</th>
                                <th scope="col">Utilisateur</th>
                                <th scope="col">Email</th>
                                <th scope="col">En attente depuis</th>
                                <th scope="col">Modifier la position</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($waiting as $entry)
                                <tr role="row">
                                    <td>
                                        <span class="badge badge-amber" style="font-size:13px; padding:4px 12px;"
                                              role="status" aria-label="Position {{ $entry->position }}">
                                            #{{ $entry->position }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong role="rowheader">{{ $entry->user->name }} {{ $entry->user->lastname }}</strong>
                                    </td>
                                    <td class="muted text-sm">{{ $entry->user->email }}</td>
                                    <td class="muted text-sm">
                                        <time datetime="{{ $entry->created_at->toIso8601String() }}"
                                              aria-label="Ajouté le {{ $entry->created_at->format('d/m/Y à H:i') }}">
                                            {{ $entry->created_at->format('d/m/Y à H:i') }}
                                        </time>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.waiting.move', $entry) }}"
                                              class="inline-form"
                                              aria-label="Éditer la position pour {{ $entry->user->name }}">
                                            @csrf
                                            <label for="position-{{ $entry->id }}" class="sr-only">Nouvelle position</label>
                                            <input type="number" id="position-{{ $entry->id }}" min="1" name="position"
                                                   value="{{ $entry->position }}" required
                                                   style="width:65px; font-size:12px; padding:4px 8px;"
                                                   aria-label="Position actuelle: {{ $entry->position }}">
                                            <button class="btn btn-sm" type="submit" data-requires-consent="true"
                                                    data-consent-message="Confirmer le déplacement de {{ $entry->user->name }} {{ $entry->user->lastname }} à la position {{ $entry->position }} ?"
                                                    aria-label="Mettre à jour la position de {{ $entry->user->name }}">
                                                Mettre à jour
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pagination-panel" role="region" aria-label="Navigation des pages de la file d'attente">
                    <p class="muted text-sm">
                        Page {{ $waiting->currentPage() }} / {{ $waiting->lastPage() }}
                    </p>
                    {{ $waiting->onEachSide(1)->links() }}
                    <form method="GET" action="{{ route('admin.waiting') }}" class="inline-form pagination-jump" aria-label="Aller à une page précise de la file d'attente">
                        <label for="waiting-page-input" class="sr-only">Numéro de page file d'attente</label>
                        <input id="waiting-page-input" type="number" name="page" min="1" max="{{ $waiting->lastPage() }}"
                               value="{{ $waiting->currentPage() }}" style="width:90px;" aria-label="Numéro de page">
                        <button type="submit" class="btn btn-sm">Aller</button>
                    </form>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection