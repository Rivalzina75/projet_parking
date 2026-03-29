@extends('layouts.app')

@section('title', 'Accueil — ParkingPro')

@section('content')

<section class="hero" role="region" aria-label="Section d'accueil principale">
    <div class="hero-eyebrow" aria-hidden="true">🅿 Application interne</div>
    <h1 class="hero-title">Attribution des places<br>de parking</h1>
    <p class="hero-sub">
        Système sécurisé pour le personnel : obtenez une place libre immédiatement
        ou suivez votre position dans la file d'attente en temps réel.
    </p>
    <div class="hero-actions">
        <a href="{{ route('register.show') }}" class="btn-hero-primary">Demander une place</a>
        <a href="{{ route('login') }}" class="btn-hero-ghost">Se connecter</a>
    </div>
</section>

<div class="feature-grid" role="region" aria-label="Fonctionnalités principales">
    <article class="feature-card" aria-labelledby="feature-1-title">
        <div class="feature-icon" style="background:#dbeafe; color:#1d4ed8;" aria-hidden="true">①</div>
        <div class="feature-num">Étape 1</div>
        <h2 id="feature-1-title" class="feature-title">Inscription</h2>
        <p class="feature-desc">Créez un compte. Un administrateur validera votre accès avant de pouvoir réserver.</p>
    </article>
    <article class="feature-card" aria-labelledby="feature-2-title">
        <div class="feature-icon" style="background:#d1fae5; color:#059669;" aria-hidden="true">②</div>
        <div class="feature-num">Étape 2</div>
        <h2 id="feature-2-title" class="feature-title">Réservation immédiate</h2>
        <p class="feature-desc">Une place libre vous est attribuée aléatoirement. Si aucune n'est disponible, vous rejoignez la file.</p>
    </article>
    <article class="feature-card" aria-labelledby="feature-3-title">
        <div class="feature-icon" style="background:#fef3c7; color:#d97706;" aria-hidden="true">③</div>
        <div class="feature-num">Étape 3</div>
        <h2 id="feature-3-title" class="feature-title">Suivi & historique</h2>
        <p class="feature-desc">Consultez votre place, votre rang en file d'attente et l'historique complet de vos attributions.</p>
    </article>
</div>

<div class="cards-3 mt-4" role="region" aria-label="Caractéristiques de sécurité">
    <div class="metric" style="border-left: 3px solid #1d4ed8;">
        <div class="metric-label">Accès</div>
        <div class="metric-value" style="font-size:18px; font-weight:700; color:var(--text-2);">Sécurisé</div>
        <div class="text-xs muted mt-2">Protection par mot de passe et validation admin</div>
    </div>
    <div class="metric" style="border-left: 3px solid #059669;">
        <div class="metric-label">Attribution</div>
        <div class="metric-value" style="font-size:18px; font-weight:700; color:var(--text-2);">Aléatoire</div>
        <div class="text-xs muted mt-2">Équitable et immédiate dès disponibilité</div>
    </div>
    <div class="metric" style="border-left: 3px solid #d97706;">
        <div class="metric-label">Gestion</div>
        <div class="metric-value" style="font-size:18px; font-weight:700; color:var(--text-2);">Complète</div>
        <div class="text-xs muted mt-2">Back-office administrateur avancé</div>
    </div>
</div>

@endsection