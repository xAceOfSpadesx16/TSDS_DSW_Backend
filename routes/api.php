<?php

use App\Modules\Auth\AuthController;
use App\Modules\History\HistoryController;
use App\Modules\Legal\LegalController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

// Rutas de la API montadas bajo /api.

/** Health check público. */
Route::get('/health', function (): JsonResponse {
    return response()->json([
        'status' => 'ok',
        'service' => config('app.name'),
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Auth: registro, login y refresh públicos; logout y me con JWT.
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Historial: el cliente envía el sorteo ya calculado y el backend lo persiste.
Route::middleware('auth:api')->group(function () {
    Route::post('/history', [HistoryController::class, 'store']);
    Route::get('/histories', [HistoryController::class, 'index']);
    Route::delete('/history/{id}', [HistoryController::class, 'destroy'])
        ->whereUuid('id');
    Route::delete('/histories', [HistoryController::class, 'clear']);
});

// Legal: disclaimer público.
Route::get('/legal/disclaimer', [LegalController::class, 'disclaimer']);