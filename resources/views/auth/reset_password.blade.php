@extends('layouts.app')

@section('title', 'Mot de passe perdu')

@section('content')
    <div class="panel form-panel">
        <h1>Mot de passe perdu</h1>
        <p class="muted">Saisissez votre email pour générer un jeton de réinitialisation.</p>

        <form method="POST" action="{{ route('password.ask') }}" class="form-grid">
            @csrf

            <label>Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>

            <button type="submit" class="btn btn-primary">Continuer</button>
        </form>
    </div>
@endsection
