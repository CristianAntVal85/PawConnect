<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ChatController;
use App\Http\Controllers\Web\PetController;
use App\Http\Controllers\Web\ProfileController;
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
    Route::get('/chat/{match?}', [ChatController::class, 'index'])
        ->whereNumber('match')
        ->name('web.chat.index');
    Route::get('/matches/swipe', function () {
        return view('matches.swipe');
    })->name('web.matches.swipe');

    Route::get('/pets', [PetController::class, 'index'])->name('web.pets.index');
    Route::get('/pets/create', [PetController::class, 'create'])->name('web.pets.create');
    Route::post('/pets', [PetController::class, 'store'])->name('web.pets.store');
    Route::get('/pets/{pet}', [PetController::class, 'show'])->name('web.pets.show');
    Route::get('/pets/{pet}/edit', [PetController::class, 'edit'])->name('web.pets.edit');
    Route::put('/pets/{pet}', [PetController::class, 'update'])->name('web.pets.update');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('web.profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('web.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('web.profile.password.update');
});
