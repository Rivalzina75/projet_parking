@extends('layouts.app')

@section('title', 'Mentions légales')

@section('content')
    <article class="panel" role="article">
        <h1>Mentions légales</h1>
        <section style="margin-top: 20px;">
            <p style="margin: 12px 0; line-height: 1.6;">
                <strong>Application interne</strong> de gestion des places de parking.
            </p>
            <p style="margin: 12px 0; line-height: 1.6;">
                <strong>Contact administrateur :</strong> <a href="mailto:admin@parking.local">admin@parking.local</a>
            </p>
            <p style="margin: 12px 0; line-height: 1.6;">
                <strong>Protection des données :</strong> Les données collectées sont utilisées uniquement pour l'attribution des places de parking. Aucune donnée n'est partagée avec des tiers.
            </p>
            <p style="margin: 12px 0; line-height: 1.6;">
                <strong>Responsabilité :</strong> Cette application est réservée aux salariés autorisés. L'accès non autorisé est interdit.
            </p>
        </section>
    </article>
@endsection
