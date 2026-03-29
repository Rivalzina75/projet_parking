<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /**
     * Affiche le formulaire de demande de réinitialisation du mot de passe.
     */
    public function showresetpassword()
    {
        return view('auth.reset_password');
    }

    /**
     * Génère un token de réinitialisation et affiche l'écran de confirmation.
     */
    public function askResetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $data['email']],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        return view('auth.reset_password_confirm', [
            'email' => $data['email'],
            'token' => $token,
        ]);
    }

    /**
     * Vérifie le token puis applique le nouveau mot de passe utilisateur.
     */
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:10|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/|confirmed',
        ]);

        $row = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (! $row || ! Hash::check($data['token'], $row->token)) {
            return back()->withErrors([
                'email' => 'Le lien de réinitialisation est invalide.',
            ]);
        }

        User::where('email', $data['email'])->update([
            'password' => Hash::make($data['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect('/login')->with('message', 'Mot de passe réinitialisé avec succès.');
    }
}
