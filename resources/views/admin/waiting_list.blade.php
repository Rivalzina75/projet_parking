@extends('layouts.app')

@section('title', 'Admin - Liste d\'attente')

@section('content')
    <h1>Liste d’attente</h1>
    <p class="muted">Consultez l’ordre de priorité et réorganisez les positions.</p>

    <div class="panel mt">
        <table>
            <thead>
            <tr>
                <th>Position</th>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>Changer position</th>
            </tr>
            </thead>
            <tbody>
            @forelse($waiting as $entry)
                <tr>
                    <td><span class="status-badge pending">#{{ $entry->position }}</span></td>
                    <td>{{ $entry->user->name }} {{ $entry->user->lastname }}</td>
                    <td>{{ $entry->user->email }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.waiting.move', $entry) }}" class="row gap">
                            @csrf
                            <input type="number" min="1" name="position" value="{{ $entry->position }}" required>
                            <button class="btn" type="submit">Mettre à jour</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Aucun utilisateur en attente.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
