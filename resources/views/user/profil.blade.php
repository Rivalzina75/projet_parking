@extends('layouts.app')

@section('title', 'Mon profil — ParkingPro')

@section('content')
<div class="page-panel">
    @include('partials.sidebar_user', ['active' => 'profil'])

    <div class="page-body">
        <div class="section-header mb-6">
            <div class="section-title">Mon profil</div>
        </div>

        {{-- Infos compte --}}
        <div class="card mb-6">
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:var(--text-3); margin-bottom:14px;">
                Informations du compte
            </div>

            @foreach([
                ['Prénom', $user->name],
                ['Nom', $user->lastname],
                ['Adresse email', $user->email],
                ['Rôle', ucfirst($user->role)],
            ] as [$label, $value])
                <div class="flex justify-between items-center"
                     style="padding:9px 0; border-bottom:1px solid var(--border); font-size:13.5px;">
                    <span style="color:var(--text-3); font-weight:500;">{{ $label }}</span>
                    <span style="font-weight:600;">{{ $value }}</span>
                </div>
            @endforeach
        </div>

        {{-- Changer mdp --}}
        <div style="font-size:15px; font-weight:700; letter-spacing:-0.2px; margin-bottom:16px;">
            Changer le mot de passe
        </div>

        <div class="card" style="max-width:400px;">
            <form method="POST" action="{{ route('user.password.update') }}" class="form-grid">
                @csrf

                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password"
                           required placeholder="••••••••••">
                </div>

                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password"
                           required placeholder="Min. 10 car., maj., chiffre, symbole">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           required placeholder="Répétez le nouveau mot de passe">
                </div>

                <button type="submit" class="btn btn-success" style="width:fit-content;">
                    Enregistrer le nouveau mot de passe
                </button>
            </form>
        </div>
    </div>
</div>
@endsection