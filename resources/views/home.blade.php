@extends('layouts.app')

@section('title', 'Accueil - ParkingPro')

@section('content')
    <section class="hero hero-grid">
        <div>
            <span class="pill">Application interne</span>
            <h1>Attribution de places de parking</h1>
            <p>Service sécurisé pour le personnel des ligues : attribution immédiate d'une place libre ou mise en file d'attente avec suivi du rang.</p>
            <div class="row gap wrap mt">
                <a class="btn btn-primary" href="{{ route('register.show') }}">Demander une inscription</a>
                <a class="btn" href="{{ route('login') }}">Se connecter</a>
            </div>
        </div>
        <aside class="panel soft">
            <h3>Fonctionnalités clés</h3>
            <ul class="clean-list">
                <li>Validation des inscriptions par administrateur</li>
                <li>Attribution aléatoire immédiate</li>
                <li>File d'attente et historique complet</li>
            </ul>
        </aside>
    </section>

    <section class="cards three mt">
        <article class="card feature-card">
            <h3>1. Inscription</h3>
            <p>Le compte est créé puis validé par l'administrateur avant accès.</p>
        </article>
        <article class="card feature-card">
            <h3>2. Réservation</h3>
            <p>Place libre attribuée automatiquement, sinon file d'attente.</p>
        </article>
        <article class="card feature-card">
            <h3>3. Suivi</h3>
            <p>Consultation de la place active, du rang d'attente et de l'historique.</p>
        </article>
    </section>

    <section class="cards three mt">
        <article class="card metric"><h3>Accès</h3><p>Front-office sécurisé</p></article>
        <article class="card metric"><h3>Gestion</h3><p>Back-office administrateur</p></article>
        <article class="card metric"><h3>Réseau</h3><p>Prêt pour déploiement local</p></article>
    </section>
@endsection