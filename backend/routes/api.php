<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WalkController;
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

        Route::get('/users/me', [UserController::class, 'me']);
        Route::put('/users/me', [UserController::class, 'updateMe']);
        Route::delete('/users/me', [UserController::class, 'destroyMe']);

        // Pets
        Route::apiResource('pets', PetController::class);

        // Health records (anidados por mascota)
        Route::apiResource('pets.health', HealthController::class)
            ->scoped()
            ->except(['create', 'edit']);

        // Social: matches, messages y walks
        Route::get('/matches/candidates', [MatchController::class, 'index'])->name('api.v1.matches.candidates');
        Route::post('/matches/like/{petId}', [MatchController::class, 'store'])->name('api.v1.matches.like');
        Route::post('/matches/pass/{petId}', [MatchController::class, 'store'])->name('api.v1.matches.pass');
        Route::apiResource('matches', MatchController::class)->except(['create', 'edit']);
        Route::apiResource('matches.messages', MessageController::class)
            ->scoped()
            ->except(['create', 'edit']);
        Route::apiResource('walks', WalkController::class)->except(['create', 'edit']);

        // Marketplace
        Route::apiResource('products', ProductController::class)
            ->only(['index', 'show']);
        Route::apiResource('orders', OrderController::class)
            ->only(['index', 'store', 'show']);

        // Pet-friendly places
        Route::apiResource('places', PlaceController::class)->except(['create', 'edit']);

        // Endpoints especiales que se implementarán en 3.x
        Route::post('/walks/{walk}/join', [WalkController::class, 'store']);
        Route::post('/walks/{walk}/leave', [WalkController::class, 'destroy']);
        Route::get('/products/recommended/{petId}', [ProductController::class, 'index']);

        Route::middleware('is_admin')->prefix('admin')->name('admin.')->group(function () {
            Route::apiResource('users', UserController::class)->except(['create', 'edit']);

            // CRUD administrativos
            Route::apiResource('products', ProductController::class)->except(['create', 'edit']);
            Route::apiResource('places', PlaceController::class)->except(['create', 'edit']);
        });

        // Aquí irán el resto de rutas (users, pets, health, matches, etc.)
    });
});
