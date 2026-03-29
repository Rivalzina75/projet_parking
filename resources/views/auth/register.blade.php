@extends('layouts.app')

@section('title', 'Inscription — ParkingPro')

@section('content')
<div class="auth-wrap">
    <div class="auth-brand">
        <div class="auth-brand-logo">
            <div class="auth-brand-logo-mark">P</div>
            ParkingPro
        </div>
        <div class="auth-brand-title">Rejoignez le système de stationnement</div>
        <div class="auth-brand-desc">
            Après votre inscription, un administrateur validera votre compte avant que vous puissiez réserver une place.
        </div>
        <div class="auth-steps">
            <div class="auth-step">
                <div class="auth-step-dot">1</div>
                <div class="auth-step-text">Remplissez le formulaire d'inscription</div>
            </div>
            <div class="auth-step">
                <div class="auth-step-dot">2</div>
                <div class="auth-step-text">Un admin valide votre compte manuellement</div>
            </div>
            <div class="auth-step">
                <div class="auth-step-dot">3</div>
                <div class="auth-step-text">Connectez-vous et demandez votre place</div>
            </div>
        </div>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-form-title">Créer un compte</div>
        <div class="auth-form-sub">Votre accès sera activé après vérification par l'administrateur.</div>

        <div class="notice notice-blue" style="margin-bottom:20px;">
            Votre compte sera validé manuellement. Vous recevrez un accès une fois approuvé.
        </div>

        <form method="POST" action="{{ route('register.submit') }}" class="form-grid">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Prénom</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           required maxlength="30" placeholder="Marie">
                </div>
                <div class="form-group">
                    <label for="lastname">Nom</label>
                    <input type="text" id="lastname" name="lastname" value="{{ old('lastname') }}"
                           required maxlength="30" placeholder="Dupont">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse email professionnelle</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       required placeholder="marie.dupont@entreprise.fr">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       placeholder="Min. 10 car., majuscule, chiffre, symbole">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmer le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       required placeholder="Répétez le mot de passe">
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