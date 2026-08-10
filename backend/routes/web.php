<?php

use App\Http\Controllers\Web\ChatController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HealthController;
use App\Http\Controllers\Web\MarketplaceController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\PetController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\WalkController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');

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

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('web.profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('web.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('web.profile.password.update');
});
