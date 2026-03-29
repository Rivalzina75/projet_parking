<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function showlogin()
    {
        return view('auth.login');
    }

    /**
     * Authentifie l'utilisateur puis redirige selon son rôle et son statut de validation.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Identifiants invalides.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if (! Auth::user()->is_validated) {
            return redirect()->route('account.pending');
        }

        if (Auth::user()->role === 'admin') {
            return redirect('/admin/utilisateurs');
        }

        return redirect('/utilisateur/dashboard');
    }

    /**
     * Déconnecte l'utilisateur et invalide sa session.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message', 'Vous êtes déconnecté.');
    }
}
