@extends('layouts.auth')

@section('title', 'Recuperar contrasena | PawConnect')
@section('page-title', 'Recuperar contrasena')
@section('page-subtitle', 'Te enviaremos un enlace para restablecerla')

@section('content')
    <x-alert type="info" title="Necesitas ayuda?" class="mb-4">
        Ingresa tu correo y recibirás instrucciones para recuperar tu acceso.
    </x-alert>

    <form method="POST" action="#" class="space-y-4">
        @csrf

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

        <button type="submit" class="w-full rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
            Enviar enlace de recuperacion
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Volver a
        <a href="{{ url('/login') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">iniciar sesion</a>
    </p>
@endsection
