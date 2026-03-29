<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Affiche le formulaire d'inscription.
     */
    public function showregister()
    {
        return view('auth.register');
    }

    /**
     * Valide les données puis crée un compte utilisateur en attente de validation admin.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:30',
            'lastname' => 'required|string|max:30',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:10|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/|confirmed',
        ]);
        try {
            User::create([
                'name' => trim($request->name),
                'lastname' => trim($request->lastname),
                'email' => trim($request->email),
                'password' => Hash::make($request->password),
                'role' => 'user',
                'is_validated' => (bool) false,
            ]);

            return redirect('/')->with('message', 'Inscription réussie, en attente de validation par l\'administrateur.');
        } catch (QueryException $e) {
            Log::error('Erreur SQL lors de l\'inscription', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'Service momentanément indisponible. Vérifiez la base de données puis réessayez.']);
        } catch (\Throwable $e) {
            Log::error('Erreur inattendue lors de l\'inscription', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'Une erreur inattendue est survenue lors de l\'inscription.']);
        }
    }
}
