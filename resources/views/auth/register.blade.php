@extends('layouts.app')

@section('title', 'Inscription — ParkingPro')

@section('content')
<div class="auth-wrap" role="region" aria-label="Formulaire d'inscription">
    <aside class="auth-brand" aria-hidden="true">
        <div class="auth-brand-logo">
            <div class="auth-brand-logo-mark">P</div>
            ParkingPro
        </div>
        <div class="auth-brand-title">Rejoignez le système de stationnement</div>
        <div class="auth-brand-desc">
            Après votre inscription, un administrateur validera votre compte avant que vous puissiez réserver une place.
        </div>
        <nav class="auth-steps" aria-label="Étapes du processus d'inscription">
            <div class="auth-step">
                <div class="auth-step-dot" aria-hidden="true">1</div>
                <div class="auth-step-text">Remplissez le formulaire d'inscription</div>
            </div>
            <div class="auth-step">
                <div class="auth-step-dot" aria-hidden="true">2</div>
                <div class="auth-step-text">Un admin valide votre compte manuellement</div>
            </div>
            <div class="auth-step">
                <div class="auth-step-dot" aria-hidden="true">3</div>
                <div class="auth-step-text">Connectez-vous et demandez votre place</div>
            </div>
        </nav>
    </aside>

    <div class="auth-form-wrap">
        <h1 class="auth-form-title">Créer un compte</h1>
        <p class="auth-form-sub">Votre accès sera activé après vérification par l'administrateur.</p>

        <div class="notice notice-blue" style="margin-bottom:20px;" role="status">
            Votre compte sera validé manuellement. Vous recevrez un accès une fois approuvé.
        </div>

        <form method="POST" action="{{ route('register.submit') }}" class="form-grid" role="form" aria-label="Formulaire d'inscription">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Prénom</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           required maxlength="30" placeholder="Marie"
                           aria-invalid="{{ $errors->has('name') }}">
                    @if($errors->has('name'))
                        <span class="error-text" role="alert">{{ $errors->first('name') }}</span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="lastname">Nom</label>
                    <input type="text" id="lastname" name="lastname" value="{{ old('lastname') }}"
                           required maxlength="30" placeholder="Dupont"
                           aria-invalid="{{ $errors->has('lastname') }}">
                    @if($errors->has('lastname'))
                        <span class="error-text" role="alert">{{ $errors->first('lastname') }}</span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse email professionnelle</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       required placeholder="marie.dupont@entreprise.fr"
                       aria-invalid="{{ $errors->has('email') }}">
                @if($errors->has('email'))
                    <span class="error-text" role="alert">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       placeholder="Min. 10 car., majuscule, chiffre, symbole"
                       aria-invalid="{{ $errors->has('password') }}">
                @if($errors->has('password'))
                    <span class="error-text" role="alert">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmer le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       required placeholder="Répétez le mot de passe"
                       aria-invalid="{{ $errors->has('password_confirmation') }}">
                @if($errors->has('password_confirmation'))
                    <span class="error-text" role="alert">{{ $errors->first('password_confirmation') }}</span>
                @endif
            </div>

            <button type="submit" class="btn btn-primary w-full" style="justify-content:center; padding:11px;">
                Envoyer ma demande
            </button>
        </form>

        <div class="auth-link">
            Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a>
        </div>
    </div>
</div>
@endsection