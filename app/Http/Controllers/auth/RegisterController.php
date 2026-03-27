<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showregister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:30',
            'lastname' => 'required|string|max:30',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:10|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
                'is_validated' => false,
            ]);

            return redirect('/')->with('message', 'Votre demande est enregistrée et en attente de validation par l\'administrateur. Vous recevrez un email de confirmation une fois votre compte validé.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer plus tard.']);
        }
    }
}
