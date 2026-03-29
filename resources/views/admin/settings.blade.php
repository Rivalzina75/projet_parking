@extends('layouts.app')

@section('title', 'Paramètres — Admin')

@section('content')
<div class="page-panel" role="main">
    @include('partials.sidebar_admin', ['active' => 'settings'])

    <div class="page-body">
        <section aria-label="Paramètres administrateur">
            <div class="section-header mb-6">
                <div>
                    <h1 class="section-title">Paramètres</h1>
                    <p class="section-sub">Configuration globale des actions administrateur</p>
                </div>
            </div>

            <div style="display:grid; gap:12px; margin-bottom:24px;">
                <article class="card" role="region" aria-label="Double consentement des actions admin">
                    <h2 style="font-size:13px; font-weight:700; margin-bottom:10px;">Sécurité des actions</h2>
                    <form method="POST" action="{{ route('admin.settings') }}" class="inline-form" aria-label="Formulaire d'activation du double consentement">
                        @csrf
                        <input type="hidden" name="settings_toggle" value="1">
                        <label style="display:inline-flex; align-items:center; gap:8px; font-weight:600; color:var(--text-2);">
                            <input type="checkbox" name="double_consent_enabled" value="1" {{ $doubleConsentEnabled ? 'checked' : '' }}>
                            Activer le double consentement
                        </label>
                        <button class="btn btn-sm" type="submit" data-requires-consent="true"
                                data-consent-message="Confirmer la modification du paramètre de double consentement ?">
                            Enregistrer
                        </button>
                    </form>
                    <p class="text-sm muted" style="margin-top:10px;">
                        Si activé, une confirmation est demandée avant les actions sensibles (réinitialisation, forçage, suppression, déconnexion, etc.).
                    </p>
                </article>
            </div>
        </section>
    </div>
</div>
@endsection
