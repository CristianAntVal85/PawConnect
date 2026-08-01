<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Autenticación (pública) ──────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/register',       [AuthController::class, 'register']);
        Route::post('/login',          [AuthController::class, 'login']);
        Route::post('/forgot-password',[AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // ── Rutas protegidas con Sanctum ─────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Aquí irán el resto de rutas (users, pets, health, matches, etc.)
    });
});
