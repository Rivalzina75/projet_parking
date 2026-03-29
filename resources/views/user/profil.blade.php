@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
<div class="panel page-panel">
    @include('partials.sidebar_user', ['active' => 'profil'])

    <div class="page-body">
        <div style="font-size:15px;font-weight:500;margin-bottom:18px;">Mon profil</div>

        <div style="padding:14px 16px;background:var(--slate-100);border-radius:8px;margin-bottom:20px;">
            <div style="font-size:11px;color:var(--slate-500);font-weight:500;margin-bottom:10px;letter-spacing:0.4px;">INFORMATIONS DU COMPTE</div>
            @foreach([['Nom', $user->lastname], ['Prénom', $user->name], ['Email', $user->email], ['Rôle', ucfirst($user->role)]] as [$label, $value])
                <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--slate-200);font-size:13px;">
                    <span class="muted">{{ $label }}</span>
                    <span>{{ $value }}</span>
                </div>
            @endforeach
        </div>

        <div style="font-size:13px;font-weight:500;margin-bottom:12px;">Changer le mot de passe</div>
        <form method="POST" action="{{ route('user.password.update') }}" class="form-grid auth-form" style="max-width:380px;">
            @csrf

            <label>Mot de passe actuel
                <input type="password" name="current_password" required>
            </label>

            <label>Nouveau mot de passe
                <input type="password" name="password" required>
            </label>

            <label>Confirmer le nouveau mot de passe
                <input type="password" name="password_confirmation" required>
            </label>

            <button type="submit" class="btn" style="background:#3B6D11;color:white;border-color:transparent;">Enregistrer</button>
        </form>
    </div>
</div>
@endsection