<?php

use App\Http\Middleware\AjouterEntetesSecurite;
use App\Http\Middleware\EstAdministrateur;
use App\Http\Middleware\EstChauffeur;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ajoute les en-têtes de sécurité aux requêtes du site.
        $middleware->web(append: [AjouterEntetesSecurite::class]);

        // Donne un nom court aux vérifications de rôle utilisées dans les routes.
        $middleware->alias([
            'admin' => EstAdministrateur::class,
            'chauffeur' => EstChauffeur::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Les routes API ou les requêtes JSON reçoivent les erreurs au format JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
