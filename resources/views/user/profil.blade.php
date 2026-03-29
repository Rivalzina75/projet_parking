@extends('layouts.app')

@section('title', 'Mon profil — ParkingPro')

@section('content')
<div class="page-panel" role="main">
    @include('partials.sidebar_user', ['active' => 'profil'])

    <div class="page-body">
        <section aria-label="Informations du profil utilisateur">
            <div class="section-header mb-6">
                <h1 class="section-title">Mon profil</h1>
            </div>

            {{-- Infos compte --}}
            <article class="card mb-6" role="region" aria-label="Informations du compte">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:var(--text-3); margin-bottom:14px;">
                    Informations du compte
                </div>

                <dl>
                    @foreach([
                        ['Prénom', $user->name],
                        ['Nom', $user->lastname],
                        ['Adresse email', $user->email],
                        ['Rôle', ucfirst($user->role)],
                    ] as [$label, $value])
                        <div class="flex justify-between items-center"
                             style="padding:9px 0; border-bottom:1px solid var(--border); font-size:13.5px;">
                            <dt style="color:var(--text-3); font-weight:500;">{{ $label }}</dt>
                            <dd style="font-weight:600; margin:0;">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </article>

            {{-- Changer mdp --}}
            <section aria-label="Modification du mot de passe">
                <h2 style="font-size:15px; font-weight:700; letter-spacing:-0.2px; margin-bottom:16px;">
                    Changer le mot de passe
                </h2>

                <article class="card" style="max-width:400px;">
                    <form method="POST" action="{{ route('user.password.update') }}" class="form-grid" role="form" aria-label="Formulaire de modification du mot de passe">
                        @csrf

                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel</label>
                            <input type="password" id="current_password" name="current_password"
                                   required placeholder="••••••••••"
                                   aria-invalid="{{ $errors->has('current_password') }}">
                            @if($errors->has('current_password'))
                                <span class="error-text" role="alert">{{ $errors->first('current_password') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password">Nouveau mot de passe</label>
                            <input type="password" id="password" name="password"
                                   required placeholder="Min. 10 car., maj., chiffre, symbole"
                                   aria-invalid="{{ $errors->has('password') }}">
                            @if($errors->has('password'))
                                <span class="error-text" role="alert">{{ $errors->first('password') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   required placeholder="Répétez le nouveau mot de passe"
                                   aria-invalid="{{ $errors->has('password_confirmation') }}">
                            @if($errors->has('password_confirmation'))
                                <span class="error-text" role="alert">{{ $errors->first('password_confirmation') }}</span>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-success" style="width:fit-content;" data-requires-consent="true"
                                data-consent-message="Êtes-vous sûr de vouloir modifier votre mot de passe ?">
                            Enregistrer le nouveau mot de passe
                        </button>
                    </form>
                </article>
            </section>
        </section>
    </div>
</div>
@endsection