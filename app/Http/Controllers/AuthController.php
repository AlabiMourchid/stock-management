<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Identifiants incorrects.']);
        }

        // Vérifie que le compte est actif
        if (!Auth::user()->actif) {
            Auth::logout();
            return back()->withErrors(['email' => 'Ce compte est désactivé. Contactez l\'administrateur.']);
        }

        $request->session()->regenerate();

        // Redirection selon le rôle
        return redirect()->intended(match(Auth::user()->role) {
            'cuisinier' => route('cuisine.index'),
            'caissier'  => route('ventes.pos'),
            default     => route('dashboard'),
        });
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
