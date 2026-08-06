<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PetController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('web.dashboard');

Route::get('/pets', [PetController::class, 'index'])->name('web.pets.index');
Route::get('/pets/create', [PetController::class, 'create'])->name('web.pets.create');
Route::get('/pets/{pet}', [PetController::class, 'show'])->name('web.pets.show');
Route::get('/pets/{pet}/edit', [PetController::class, 'edit'])->name('web.pets.edit');

Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('web.profile.edit');
