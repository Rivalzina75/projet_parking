@extends('layouts.app')

@section('title', "File d'attente — Admin")

@section('content')
<div class="page-panel">
    @include('partials.sidebar_admin', ['active' => 'waiting'])

    <div class="page-body">
        <div class="section-header mb-6">
            <div>
                <div class="section-title">File d'attente</div>
                <div class="section-sub">
                    {{ $waiting->count() }} utilisateur(s) en attente · Les positions sont attribuées par ordre d'arrivée
                </div>
            </div>
        </div>

        @if($waiting->isEmpty())
            <div class="card text-center" style="padding:48px; color:var(--text-3);">
                <div style="font-size:32px; margin-bottom:12px;">🎉</div>
                <div style="font-weight:600; font-size:15px; color:var(--text-2);">File d'attente vide</div>
                <div class="text-sm muted mt-2">Tous les utilisateurs ont une place attribuée.</div>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>En attente depuis</th>
                            <th>Modifier la position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($waiting as $entry)
                            <tr>
                                <td>
                                    <span class="badge badge-amber" style="font-size:13px; padding:4px 12px;">
                                        #{{ $entry->position }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $entry->user->name }} {{ $entry->user->lastname }}</div>
                                </td>
                                <td class="muted text-sm">{{ $entry->user->email }}</td>
                                <td class="muted text-sm">{{ $entry->created_at->format('d/m/Y à H:i') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.waiting.move', $entry) }}"
                                          class="inline-form">
                                        @csrf
                                        <input type="number" min="1" name="position"
                                               value="{{ $entry->position }}" required
                                               style="width:65px; font-size:12px; padding:4px 8px;">
                                        <button class="btn btn-sm" type="submit">
                                            Mettre à jour
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection