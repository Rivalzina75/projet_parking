@extends('layouts.app')

@section('title', 'Réinitialisation du mot de passe')

@section('content')
    <article class="panel form-panel" role="region" aria-label="Formulaire de réinitialisation de mot de passe">
        <h1 style="margin-bottom: 6px;">Réinitialisation</h1>
        <p class="muted" style="margin-bottom: 16px;">Jeton temporaire (simulation locale): <strong>{{ $token }}</strong></p>

        <form method="POST" action="{{ route('password.reset') }}" class="form-grid" role="form" aria-label="Formulaire de confirmation de réinitialisation">
            @csrf

            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Min. 10 car., maj., chiffre, symbole"
                       aria-invalid="{{ $errors->has('password') }}">
                @if($errors->has('password'))
                    <span class="error-text" role="alert">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmation</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       placeholder="Répétez le nouveau mot de passe"
                       aria-invalid="{{ $errors->has('password_confirmation') }}">
                @if($errors->has('password_confirmation'))
                    <span class="error-text" role="alert">{{ $errors->first('password_confirmation') }}</span>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </form>
    </article>
@endsection
