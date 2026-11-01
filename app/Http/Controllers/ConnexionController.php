<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnexionController extends Controller
{
    public function afficher()
    {
        return view('connexion');
    }

    public function connecter(Request $request)
    {
        $identifiants = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255',
        ], [
            'email.required' => 'Entrez votre adresse e-mail.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'password.required' => 'Entrez votre mot de passe.',
        ]);

        if (Auth::attempt($identifiants)) {
            $request->session()->regenerate();
            return redirect('/admin');
        }

        return back()
            ->withErrors(['email' => 'E-mail ou mot de passe incorrect.'])
            ->onlyInput('email');
    }

    public function deconnecter(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
