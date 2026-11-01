<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarteController;
use App\Http\Controllers\SignalementController;
use App\Http\Controllers\AccueilController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\AdminController;

Route::get('/', [AccueilController::class, 'index']);
Route::get('/unites', [CarteController::class, 'unites']);
Route::view('/carte', 'carte');
Route::post('/signalements', [SignalementController::class, 'store']);
Route::get('/signalements', [SignalementController::class, 'index']);
Route::get('/connexion', [ConnexionController::class, 'afficher'])->name('login');
Route::post('/connexion', [ConnexionController::class, 'connecter'])->middleware('throttle:5,1');
Route::post('/deconnexion', [ConnexionController::class, 'deconnecter']);

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'tableau']);
    Route::get('/signalements', [AdminController::class, 'signalements']);
    Route::patch('/signalements/{id}/statut', [AdminController::class, 'changerStatut'])->whereNumber('id');
    Route::delete('/signalements/{id}', [AdminController::class, 'supprimer'])->whereNumber('id');
    Route::get('/carte', [AdminController::class, 'carte']);
});
