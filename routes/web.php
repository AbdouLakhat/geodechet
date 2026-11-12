<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarteController;
use App\Http\Controllers\SignalementController;
use App\Http\Controllers\AccueilController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChauffeurController;

Route::get('/', [AccueilController::class, 'index']);
Route::get('/unites', [CarteController::class, 'unites']);
Route::view('/carte', 'carte');
Route::post('/signalements', [SignalementController::class, 'store'])->middleware('throttle:10,1');
Route::get('/connexion', [ConnexionController::class, 'afficher'])->name('login');
Route::post('/connexion', [ConnexionController::class, 'connecter'])->middleware('throttle:5,1');
Route::post('/deconnexion', [ConnexionController::class, 'deconnecter']);

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'tableau']);
    Route::get('/signalements', [AdminController::class, 'signalements']);
    Route::get('/donnees/signalements', [AdminController::class, 'donneesSignalements']);
    Route::patch('/signalements/{id}/statut', [AdminController::class, 'changerStatut'])->whereNumber('id');
    Route::get('/signalements/{id}/modifier', [AdminController::class, 'modifier'])->whereNumber('id');
    Route::put('/signalements/{id}', [AdminController::class, 'mettreAJour'])->whereNumber('id');
    Route::delete('/signalements/{id}', [AdminController::class, 'supprimer'])->whereNumber('id');
    Route::get('/signalements/{id}', [AdminController::class, 'detail'])->whereNumber('id');
    Route::get('/carte', [AdminController::class, 'carte']);
    Route::get('/chauffeurs', [ChauffeurController::class, 'index']);
    Route::get('/chauffeurs/creer', [ChauffeurController::class, 'creer']);
    Route::post('/chauffeurs', [ChauffeurController::class, 'enregistrer']);
    Route::get('/chauffeurs/{id}/modifier', [ChauffeurController::class, 'modifier'])->whereNumber('id');
    Route::put('/chauffeurs/{id}', [ChauffeurController::class, 'mettreAJour'])->whereNumber('id');
    Route::patch('/chauffeurs/{id}/activation', [ChauffeurController::class, 'basculer'])->whereNumber('id');
    Route::delete('/chauffeurs/{id}', [ChauffeurController::class, 'supprimer'])->whereNumber('id');
});
