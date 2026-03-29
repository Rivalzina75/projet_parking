@extends('layouts.app')

@section('title', 'Compte en attente de validation')

@section('content')
    <section class="auth-shell" role="region" aria-label="Statut du compte">
        <article class="panel soft">
            <div class="pill" role="status" aria-label="État du compte">Accès restreint</div>
            <h1 style="margin: 14px 0;">Votre compte n'est pas validé</h1>
            <p class="muted">Votre compte est en attente de validation par un administrateur. Vous ne pouvez pas accéder à l'application pour le moment.</p>
            <div class="card-actions" style="margin-top: 24px;">
                <form method="POST" action="{{ route('logout') }}" aria-label="Formulaire de déconnexion">
                    @csrf
                    <button type="submit" class="btn btn-primary">Se déconnecter</button>
                </form>
            </div>
        </article>
    </section>
@endsection
