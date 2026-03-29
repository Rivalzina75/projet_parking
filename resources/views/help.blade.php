@extends('layouts.app')

@section('title', 'Aide utilisateur')

@section('content')
    <h1>Aide utilisateur</h1>
    <div class="panel mt">
        <ol>
            <li>Créer un compte via la page Inscription.</li>
            <li>Attendre la validation du compte par un administrateur.</li>
            <li>Se connecter et demander une réservation depuis le dashboard.</li>
            <li>Si aucune place n’est libre, vérifier votre rang dans la file d’attente.</li>
            <li>Clôturer la réservation quand vous libérez la place.</li>
        </ol>
    </div>
@endsection
