@extends('layouts.app')

@section('title', 'Réinitialisation du mot de passe')

@section('content')
    <div class="panel form-panel">
        <h1>Réinitialisation</h1>
        <p class="muted">Jeton temporaire (simulation locale): <strong>{{ $token }}</strong></p>

        <form method="POST" action="{{ route('password.reset') }}" class="form-grid">
            @csrf

            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="token" value="{{ $token }}">

            <label>Nouveau mot de passe
                <input type="password" name="password" required>
            </label>

            <label>Confirmation
                <input type="password" name="password_confirmation" required>
            </label>

            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </form>
    </div>
@endsection
