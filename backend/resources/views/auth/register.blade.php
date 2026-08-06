@extends('layouts.auth')

@section('title', 'Registro | PawConnect')
@section('page-title', 'Crear cuenta')
@section('page-subtitle', 'Empieza en PawConnect en menos de un minuto')

@section('content')
    <form method="POST" action="#" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nombre completo</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                required
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200"
            >
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Correo electronico</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Contrasena</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200"
            >
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirmar contrasena</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200"
            >
        </div>

        <button type="submit" class="w-full rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
            Registrarme
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Ya tienes cuenta?
        <a href="{{ url('/login') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">Iniciar sesion</a>
    </p>
@endsection
