<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarteController;
use App\Http\Controllers\SignalementController;
use App\Http\Controllers\AccueilController;

Route::get('/', [AccueilController::class, 'index']);
Route::get('/unites', [CarteController::class, 'unites']);
Route::view('/carte', 'carte');
Route::post('/signalements', [SignalementController::class, 'store']);
Route::get('/signalements', [SignalementController::class, 'index']);
