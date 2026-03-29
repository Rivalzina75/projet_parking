@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
    <section class="auth-shell">
        <article class="panel soft">
            <span class="pill">Nouveau compte</span>
            <h1>Inscription</h1>
            <p class="muted">Votre compte sera activé après validation par un administrateur. Une fois validé, vous pourrez demander une réservation immédiate.</p>
        </article>

        <div class="panel form-panel">
        <form method="POST" action="{{ route('register.submit') }}" class="form-grid auth-form">
            @csrf

            <label>Nom
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="30">
            </label>

            <label>Prénom
                <input type="text" name="lastname" value="{{ old('lastname') }}" required maxlength="30">
            </label>

            <label>Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>

            <label>Mot de passe
                <input type="password" name="password" required>
            </label>

            <label>Confirmer le mot de passe
                <input type="password" name="password_confirmation" required>
            </label>

            <button type="submit" class="btn btn-primary">Envoyer ma demande</button>
        </form>
        </div>
    </section>
@endsection
