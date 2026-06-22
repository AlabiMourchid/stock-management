<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $recaptcha = $request->input('g-recaptcha-response');

        if (!$recaptcha) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Veuillez compléter le reCAPTCHA.']);
        }

        $verify = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => config('services.recaptcha.secret'),
            'response' => $recaptcha,
            'remoteip' => $request->ip(),
        ]);

        if (!$verify->json('success')) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'La vérification reCAPTCHA a échoué. Réessayez.']);
        }

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
