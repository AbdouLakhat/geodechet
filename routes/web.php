<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarteController;
use App\Http\Controllers\SignalementController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/unites', [CarteController::class, 'unites']);
Route::view('/carte', 'carte');
Route::post('/signalements', [SignalementController::class, 'store']);
Route::get('/signalements', [SignalementController::class, 'index']);
