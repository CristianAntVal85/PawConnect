<?php

use App\Http\Controllers\Web\ChatController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HealthController;
use App\Http\Controllers\Web\MarketplaceController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\PetController;
use App\Http\Controllers\Web\PlaceController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\WalkController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');

// Rutas públicas de lugares
Route::get('/places', [PlaceController::class, 'index'])->name('places.index');
Route::get('/places/{id}', [PlaceController::class, 'show'])->name('places.show');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('web.dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('web.notifications.index');
    Route::get('/chat/{match?}', [ChatController::class, 'index'])
        ->whereNumber('match')
        ->name('web.chat.index');
    Route::get('/matches/swipe', function () {
        return view('matches.swipe');
    })->name('web.matches.swipe');
    Route::get('/walks', [WalkController::class, 'index'])->name('web.walks.index');
    Route::post('/walks/{walk}/join', [WalkController::class, 'join'])->name('web.walks.join');
    Route::delete('/walks/{walk}/leave', [WalkController::class, 'leave'])->name('web.walks.leave');

    Route::get('/pets', [PetController::class, 'index'])->name('web.pets.index');
    Route::get('/pets/create', [PetController::class, 'create'])->name('web.pets.create');
    Route::post('/pets', [PetController::class, 'store'])->name('web.pets.store');
    Route::get('/pets/{pet}', [PetController::class, 'show'])->name('web.pets.show');
    Route::get('/pets/{pet}/edit', [PetController::class, 'edit'])->name('web.pets.edit');
    Route::put('/pets/{pet}', [PetController::class, 'update'])->name('web.pets.update');

    // Health records
    Route::get('/pets/{pet}/health', [HealthController::class, 'timeline'])->name('web.pets.health.timeline');

    // Marketplace
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('web.marketplace.index');
    Route::get('/marketplace/{product}', [MarketplaceController::class, 'show'])->name('web.marketplace.show');

    // Lugares Pet-Friendly
    Route::get('/places/create', [PlaceController::class, 'create'])->name('places.create');
    Route::post('/places', [PlaceController::class, 'store'])->name('places.store');
    Route::get('/places/favorites', [PlaceController::class, 'favorites'])->name('places.favorites');

    // Pedidos
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('web.checkout');
    Route::get('/orders', [OrderController::class, 'index'])->name('web.orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('web.orders.show');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('web.profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('web.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('web.profile.password.update');

    // Admin routes
    Route::middleware('is_admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Gestión de Usuarios
        Route::get('/users', [\App\Http\Controllers\Web\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [\App\Http\Controllers\Web\Admin\UserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/role', [\App\Http\Controllers\Web\Admin\UserController::class, 'updateRole'])->name('users.updateRole');
        Route::post('/users/{user}/block', [\App\Http\Controllers\Web\Admin\UserController::class, 'block'])->name('users.block');
        Route::post('/users/{user}/unblock', [\App\Http\Controllers\Web\Admin\UserController::class, 'unblock'])->name('users.unblock');

        // Gestión de Mascotas
        Route::get('/pets', [\App\Http\Controllers\Web\Admin\PetController::class, 'index'])->name('pets.index');
        Route::get('/pets/{pet}', [\App\Http\Controllers\Web\Admin\PetController::class, 'show'])->name('pets.show');
                // Gestión de Proveedores y Productos
        Route::resource('providers', \App\Http\Controllers\Web\Admin\ProviderController::class, ['as' => 'admin']);
        Route::resource('products', \App\Http\Controllers\Web\Admin\ProductController::class, ['as' => 'admin']);
                Route::get('/orders', [\App\Http\Controllers\Web\Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [\App\Http\Controllers\Web\Admin\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [\App\Http\Controllers\Web\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');

        // Gestión de Lugares
        Route::get('/places', [\App\Http\Controllers\Web\Admin\PlaceController::class, 'index'])->name('places.index');
        Route::get('/places/{place}', [\App\Http\Controllers\Web\Admin\PlaceController::class, 'show'])->name('places.show');
        Route::post('/places/{place}/approve', [\App\Http\Controllers\Web\Admin\PlaceController::class, 'approve'])->name('places.approve');
        Route::post('/places/{place}/reject', [\App\Http\Controllers\Web\Admin\PlaceController::class, 'reject'])->name('places.reject');
    });
});
