<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserLocationController;
use App\Http\Controllers\Api\WalkController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Autenticación (pública) ──────────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register',       [AuthController::class, 'register'])->name('register');
        Route::post('/login',          [AuthController::class, 'login'])->name('login');
        Route::post('/forgot-password',[AuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    });

    // ── Rutas públicas ──────────────────────────────────────────────────────────
    Route::apiResource('products', ProductController::class)
        ->only(['index', 'show']);

    // Lugares pet-friendly (solo listado y detalles son públicos)
    Route::get('/places', [\App\Http\Controllers\Api\PlaceController::class, 'index'])
        ->name('places.index');
    Route::get('/places/{place}', [\App\Http\Controllers\Api\PlaceController::class, 'show'])
        ->where('place', '[0-9]+')
        ->name('places.show');

    // Reseñas de lugares (lectura pública)
    Route::get('/places/{place}/reviews', [\App\Http\Controllers\Api\PlaceReviewController::class, 'index'])
        ->name('places.reviews.index');
    Route::get('/places/{place}/reviews/{review}', [\App\Http\Controllers\Api\PlaceReviewController::class, 'show'])
        ->name('places.reviews.show');

    // ── Rutas protegidas con Sanctum ─────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.v1.notifications.unread-count');
        Route::put('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('api.v1.notifications.mark-as-read');

        Route::get('/users/me', [UserController::class, 'me']);
        Route::put('/users/me', [UserController::class, 'updateMe']);
        Route::delete('/users/me', [UserController::class, 'destroyMe']);

        // Ubicaciones guardadas del usuario (estilo Wallapop)
        Route::get('/locations', [UserLocationController::class, 'index'])->name('api.v1.locations.index');
        Route::post('/locations', [UserLocationController::class, 'store'])->name('api.v1.locations.store');
        Route::patch('/locations/{location}/default', [UserLocationController::class, 'setDefault'])->name('api.v1.locations.default');
        Route::delete('/locations/{location}', [UserLocationController::class, 'destroy'])->name('api.v1.locations.destroy');

        // Pets
        Route::apiResource('pets', PetController::class);

        // Health records (anidados por mascota)
        // Veterinarios recomendados (debe ir ANTES de apiResource para evitar conflictos)
        Route::get('/pets/{pet}/health/veterinarians', [HealthController::class, 'recommendedVeterinarians'])
            ->name('api.v1.pets.health.veterinarians')
            ->where('pet', '[0-9]+');

        Route::apiResource('pets.health', HealthController::class)
            ->scoped()
            ->except(['create', 'edit']);
        // Renovar registro sanitario
        Route::post('/pets/{pet}/health/{health}/renew', [HealthController::class, 'renew'])
            ->name('api.v1.pets.health.renew');

        // Social: matches, messages y walks
        Route::get('/matches/candidates', [MatchController::class, 'index'])->name('api.v1.matches.candidates');
        Route::post('/matches/like/{petId}', [MatchController::class, 'store'])->name('api.v1.matches.like');
        Route::post('/matches/pass/{petId}', [MatchController::class, 'store'])->name('api.v1.matches.pass');
        Route::apiResource('matches', MatchController::class)->except(['create', 'edit']);
        Route::apiResource('matches.messages', MessageController::class)
            ->scoped()
            ->except(['create', 'edit']);
        Route::post('/walks/{walk}/join', [WalkController::class, 'join']);
        Route::delete('/walks/{walk}/leave', [WalkController::class, 'leave']);
        Route::apiResource('walks', WalkController::class)->except(['create', 'edit']);

        // Marketplace
        Route::get('/products/recommended/{pet}', [ProductController::class, 'recommended'])
            ->name('api.v1.products.recommended');
        Route::get('/products/by-service/{serviceType}/{petId}', [ProductController::class, 'byServiceType'])
            ->name('api.v1.products.by-service')
            ->where('petId', '[0-9]+');
        Route::get('/products/deworming-combo/{petId}', [ProductController::class, 'dewormingCombo'])
            ->name('api.v1.products.deworming-combo')
            ->where('petId', '[0-9]+');
        Route::post('/orders/payment-intent', [OrderController::class, 'createPaymentIntent'])
            ->name('api.v1.orders.payment-intent');
        Route::apiResource('orders', OrderController::class)
            ->only(['index', 'store', 'show', 'update']);

        // Pet-friendly places (CRUD protegido)
        Route::post('/places', [\App\Http\Controllers\Api\PlaceController::class, 'store'])
            ->name('places.store');
        Route::put('/places/{place}', [\App\Http\Controllers\Api\PlaceController::class, 'update'])
            ->name('places.update');
        Route::delete('/places/{place}', [\App\Http\Controllers\Api\PlaceController::class, 'destroy'])
            ->name('places.destroy');

        Route::get('/places/favorites', [\App\Http\Controllers\Api\PlaceFavoriteController::class, 'index'])
            ->name('places.favorites.index');
        Route::post('/places/{place}/favorite', [\App\Http\Controllers\Api\PlaceFavoriteController::class, 'store'])
            ->name('places.favorite');
        Route::delete('/places/{place}/favorite', [\App\Http\Controllers\Api\PlaceFavoriteController::class, 'destroy'])
            ->name('places.favorite.destroy');

        // Reseñas de lugares
        Route::post('/places/{place}/reviews', [\App\Http\Controllers\Api\PlaceReviewController::class, 'store'])
            ->name('places.reviews.store');
        Route::put('/places/{place}/reviews/{review}', [\App\Http\Controllers\Api\PlaceReviewController::class, 'update'])
            ->name('places.reviews.update');
        Route::delete('/places/{place}/reviews/{review}', [\App\Http\Controllers\Api\PlaceReviewController::class, 'destroy'])
            ->name('places.reviews.destroy');

        Route::middleware('is_admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard/stats', [StatsController::class, 'dashboard'])
                ->name('stats.dashboard');
            Route::apiResource('users', UserController::class)->except(['create', 'edit']);

            // CRUD administrativos
            Route::apiResource('products', ProductController::class)->except(['create', 'edit']);
            Route::apiResource('places', PlaceController::class)->except(['create', 'edit']);

            // Aprobar/rechazar lugares
            Route::post('/places/{place}/approve', [PlaceController::class, 'approve'])
                ->name('places.approve');
            Route::post('/places/{place}/reject', [PlaceController::class, 'reject'])
                ->name('places.reject');
        });

        // Aquí irán el resto de rutas (users, pets, health, matches, etc.)
    });
});
