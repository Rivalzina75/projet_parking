@extends('layouts.app')

@section('title', 'Mot de passe perdu')

@section('content')
    <article class="panel form-panel" role="region" aria-label="Formulaire de réinitialisation de mot de passe">
        <h1 style="margin-bottom: 6px;">Mot de passe perdu</h1>
        <p class="muted" style="margin-bottom: 24px;">Saisissez votre email pour générer un jeton de réinitialisation.</p>

        <form method="POST" action="{{ route('password.ask') }}" class="form-grid" role="form" aria-label="Formulaire de demande de réinitialisation">
            @csrf

            <div class="form-group">
                <label for="reset-email">Adresse email</label>
                <input type="email" id="reset-email" name="email" value="{{ old('email') }}" required 
                       placeholder="vous@entreprise.fr"
                       aria-invalid="{{ $errors->has('email') }}">
                @if($errors->has('email'))
                    <span class="error-text" role="alert">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Continuer</button>
        </form>
    </article>
@endsection