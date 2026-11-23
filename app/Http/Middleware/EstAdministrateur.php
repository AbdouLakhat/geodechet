<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EstAdministrateur
{
    /**
     * Autorise les administrateurs actifs et redirige les autres rôles vers leur espace.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $utilisateur = $request->user();

        if (! $utilisateur) {
            abort(403, 'Accès réservé à l\'administrateur.');
        }

        if (! $utilisateur->actif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Ce compte est désactivé. Contactez votre administrateur.',
            ]);
        }

        if ($utilisateur->role === 'chauffeur') {
            return to_route('chauffeur.dashboard');
        }

        if ($utilisateur->role !== 'administrateur') {
            abort(403, 'Accès réservé à l\'administrateur.');
        }

        return $next($request);
    }
}
