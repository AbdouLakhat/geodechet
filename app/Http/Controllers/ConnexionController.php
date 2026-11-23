<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConnexionController extends Controller
{
    /** Affiche le formulaire de connexion. */
    public function afficher(): View
    {
        return view('connexion');
    }

    /** Vérifie les identifiants et redirige l’utilisateur vers son espace. */
    public function connecter(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $identifiants = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255',
        ], [
            'email.required' => 'Entrez votre adresse e-mail.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'password.required' => 'Entrez votre mot de passe.',
        ]);

        if (Auth::attempt($identifiants)) {
            $utilisateur = Auth::user();

            // Un compte désactivé ne peut pas ouvrir une session, même avec le bon mot de passe.
            if (! $utilisateur || ! $utilisateur->actif) {
                return $this->refuserConnexion(
                    $request,
                    'Ce compte est désactivé. Contactez l’administrateur pour demander sa réactivation.'
                );
            }

            // Renouvelle l’identifiant de session après une connexion réussie.
            $request->session()->regenerate();

            return match ($utilisateur?->role) {
                'administrateur' => to_route('admin.dashboard'),
                'chauffeur' => to_route('chauffeur.dashboard'),
                default => $this->refuserConnexion($request, 'E-mail ou mot de passe incorrect.'),
            };
        }

        return $this->refuserConnexion($request, 'E-mail ou mot de passe incorrect.');
    }

    /** Ferme la session et renouvelle le jeton de sécurité du formulaire. */
    public function deconnecter(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /** Nettoie la session et conserve l’adresse e-mail dans le formulaire d’erreur. */
    private function refuserConnexion(Request $request, string $message): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()
            ->withErrors(['email' => $message])
            ->onlyInput('email');
    }
}
