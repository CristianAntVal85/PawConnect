@extends('layouts.auth')

@section('title', 'Iniciar sesion | PawConnect')
@section('page-title', 'Iniciar sesion')
@section('page-subtitle', 'Accede para gestionar tus mascotas y actividades')

@section('content')
    <form method="POST" action="#" class="space-y-4 sm:space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Correo electronico</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full rounded-lg border border-slate-300 px-3 py-2 sm:py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Contrasena</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 sm:py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200"
            >
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 text-xs sm:text-sm">
            <label class="inline-flex items-center gap-2 text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                <span>Recordarme</span>
            </label>
            <a href="{{ url('/forgot-password') }}" class="font-medium text-emerald-600 hover:text-emerald-700">Olvide mi contrasena</a>
        </div>

        <button type="submit" class="w-full rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600 active:bg-emerald-700">
            Entrar
        </button>
    </form>

    <p class="mt-6 text-center text-xs sm:text-sm text-slate-600">
        No tienes cuenta?
        <a href="{{ url('/register') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">Crear cuenta</a>
    </p>
@endsection
