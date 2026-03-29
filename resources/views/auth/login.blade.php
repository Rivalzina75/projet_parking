@extends('layouts.app')

@section('title', 'Connexion — ParkingPro')

@section('content')
<div class="auth-wrap">
    <div class="auth-brand">
        <div class="auth-brand-logo">
            <div class="auth-brand-logo-mark">P</div>
            ParkingPro
        </div>
        <div class="auth-brand-title">Votre espace de stationnement</div>
        <div class="auth-brand-desc">
            Accédez à votre tableau de bord pour gérer votre place ou suivre votre position dans la file d'attente.
        </div>
        <div class="auth-steps">
            <div class="auth-step">
                <div class="auth-step-dot">1</div>
                <div class="auth-step-text">Connexion sécurisée par email et mot de passe</div>
            </div>
            <div class="auth-step">
                <div class="auth-step-dot">2</div>
                <div class="auth-step-text">Accès au tableau de bord selon votre rôle</div>
            </div>
            <div class="auth-step">
                <div class="auth-step-dot">3</div>
                <div class="auth-step-text">Gestion de vos réservations en temps réel</div>
            </div>
        </div>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-form-title">Connexion</div>
        <div class="auth-form-sub">Entrez vos identifiants pour accéder à votre espace.</div>

        <form method="POST" action="{{ route('login.submit') }}" class="form-grid">
            @csrf

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       placeholder="vous@entreprise.fr" autofocus>
            </div>

            <div class="form-group">
                <div class="flex justify-between items-center">
                    <label for="password">Mot de passe</label>
                    <a href="{{ route('password.forgot') }}" style="font-size:12px; color:var(--blue); font-weight:600;">Mot de passe oublié ?</a>
                </div>
                <input type="password" id="password" name="password" required placeholder="••••••••••">
            </div>

            <button type="submit" class="btn btn-primary w-full" style="justify-content:center; padding:11px;">
                Se connecter
            </button>
        </form>

        <div class="auth-link">
            Pas encore de compte ? <a href="{{ route('register.show') }}">Créer un compte</a>
        </div>
    </div>
</div>
@endsection