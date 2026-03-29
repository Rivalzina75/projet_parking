@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
    <section class="auth-shell">
        <article class="panel soft">
            <span class="pill">Espace sécurisé</span>
            <h1>Connexion</h1>
            <p class="muted">Connectez-vous pour accéder à votre dashboard ou au back-office administrateur.</p>
        </article>

        <div class="panel form-panel">
            <form method="POST" action="{{ route('login.submit') }}" class="form-grid auth-form">
                @csrf

                <label>Email
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </label>

                <label>Mot de passe
                    <input type="password" name="password" required>
                </label>

                <button type="submit" class="btn btn-primary">Se connecter</button>
                <a href="{{ route('password.forgot') }}">Mot de passe perdu ?</a>
            </form>
        </div>
    </section>
@endsection