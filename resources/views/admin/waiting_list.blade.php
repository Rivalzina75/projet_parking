@extends('layouts.app')

@section('title', "Admin - Liste d'attente")

@section('content')
<div class="panel page-panel">
    @include('partials.sidebar_admin', ['active' => 'waiting'])

    <div class="page-body">
        <div style="font-size:15px;font-weight:500;margin-bottom:4px;">Liste d'attente</div>
        <p class="muted" style="font-size:12px;margin-bottom:18px;">{{ $waiting->count() }} utilisateur(s) en attente · Réorganisez les positions.</p>

        <table>
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>En attente depuis</th>
                    <th>Changer position</th>
                </tr>
            </thead>
            <tbody>
                @forelse($waiting as $entry)
                    <tr>
                        <td><span class="status-badge pending">#{{ $entry->position }}</span></td>
                        <td><strong>{{ $entry->user->name }} {{ $entry->user->lastname }}</strong></td>
                        <td class="muted">{{ $entry->user->email }}</td>
                        <td class="muted" style="font-size:12px;">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.waiting.move', $entry) }}" class="row gap">
                                @csrf
                                <input type="number" min="1" name="position" value="{{ $entry->position }}" required style="width:60px;font-size:12px;padding:4px 8px;">
                                <button class="btn" type="submit" style="font-size:12px;padding:4px 9px;">Mettre à jour</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted" style="text-align:center;padding:24px;">Aucun utilisateur en attente.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection