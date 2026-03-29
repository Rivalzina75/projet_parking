@extends('layouts.app')

@section('title', 'Compte en attente de validation')

@section('content')
    <section class="auth-shell">
        <article class="panel soft">
            <span class="pill">Accès restreint</span>
            <h1>Votre compte n’est pas validé</h1>
            <p class="muted">Votre compte est en attente de validation par un administrateur. Vous ne pouvez pas accéder à l’application pour le moment.</p>
            <div class="card-actions">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Se déconnecter</button>
                </form>
            </div>
        </article>
    </section>
@endsection
