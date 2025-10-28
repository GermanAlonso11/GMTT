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

// Endpoint de salud simple
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Mutation API is running',
        'timestamp' => now()->toISOString()
    ]);
});

// Endpoint de prueba POST simple
Route::post('/test-post', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'POST endpoint working',
        'received' => request()->all()
    ]);
});

// Debug endpoint para capturar cualquier petición POST
Route::post('/debug-mutation', function () {
    return response()->json([
        'debug' => 'Direct route working',
        'data' => request()->all(),
        'headers' => request()->headers->all(),
        'method' => request()->method()
    ]);
});
