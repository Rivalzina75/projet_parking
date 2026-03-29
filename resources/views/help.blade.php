@extends('layouts.app')

@section('title', 'Aide utilisateur')

@section('content')
    <article class="panel" role="article">
        <h1>Aide utilisateur</h1>
        <section aria-label="Instructions d'utilisation" role="region">
            <ol style="margin-top: 20px;">
                <li style="margin: 8px 0;">Créer un compte via la page <strong>Inscription</strong>.</li>
                <li style="margin: 8px 0;">Attendre la validation du compte par un administrateur.</li>
                <li style="margin: 8px 0;">Se connecter et demander une réservation depuis le <strong>dashboard</strong>.</li>
                <li style="margin: 8px 0;">Si aucune place n'est libre, vérifier votre rang dans la file d'attente.</li>
                <li style="margin: 8px 0;">Clôturer la réservation quand vous libérez la place.</li>
            </ol>
        </section>
    </article>
@endsection
