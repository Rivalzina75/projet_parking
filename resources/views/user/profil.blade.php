@extends('layouts.app')

@section('title', 'Profil utilisateur')

@section('content')
    <h1>Mon profil</h1>
    <p class="muted">Informations personnelles et sécurité du compte.</p>

    <div class="panel">
        <div class="cards three">
            <article class="card metric"><h3>Nom</h3><p>{{ $user->name }}</p></article>
            <article class="card metric"><h3>Prénom</h3><p>{{ $user->lastname }}</p></article>
            <article class="card metric"><h3>Email</h3><p>{{ $user->email }}</p></article>
        </div>
    </div>

    <div class="panel mt form-panel">
        <h2>Changer le mot de passe</h2>
        <form method="POST" action="{{ route('user.password.update') }}" class="form-grid">
            @csrf

            <label>Mot de passe actuel
                <input type="password" name="current_password" required>
            </label>

            <label>Nouveau mot de passe
                <input type="password" name="password" required>
            </label>

            <label>Confirmation
                <input type="password" name="password_confirmation" required>
            </label>

            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
@endsection
