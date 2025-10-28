<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MutationController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para la API de detección de mutaciones
Route::post('/mutation', [MutationController::class, 'detectMutation']);
Route::post('/mutation/analyze', [MutationController::class, 'analyzeDna']);
Route::get('/mutation/test', [MutationController::class, 'test']);
