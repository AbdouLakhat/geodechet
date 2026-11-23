<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EstChauffeur
{
    /** Autorise les chauffeurs actifs et redirige les autres rôles vers leur espace. */
    public function handle(Request $request, Closure $next): Response
    {
        $utilisateur = $request->user();

        if (! $utilisateur) {
            abort(403, 'Accès réservé aux chauffeurs actifs.');
        }

        if (! $utilisateur->actif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Ce compte est désactivé. Contactez votre administrateur.',
            ]);
        }

        if ($utilisateur->role === 'administrateur') {
            return to_route('admin.dashboard');
        }

        if ($utilisateur->role !== 'chauffeur') {
            abort(403, 'Accès réservé aux chauffeurs actifs.');
        }

        return $next($request);
    }
}
