<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EstAdministrateur
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $utilisateur = $request->user();

        if (!$utilisateur || $utilisateur->role !== 'administrateur') {
            abort(403, 'Accès réservé à l\'administrateur.');
        }

        return $next($request);
    }
}
