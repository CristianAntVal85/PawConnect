@extends('layouts.app')

@section('title', 'Editar perfil | PawConnect')
@section('page-title', 'Mi perfil')
@section('page-subtitle', 'Actualiza tus datos personales y seguridad')

@section('content')
    @php
        $user = $user ?? auth()->user() ?? (object) [
            'name' => 'Usuario Demo',
            'email' => 'demo@pawconnect.test',
            'address' => 'Madrid, Espana',
            'billing_address' => 'Madrid, Espana',
        ];
    @endphp

    @if(session('success'))
        <x-alert type="success" class="mb-4" dismissible>{{ session('success') }}</x-alert>
    @endif

    @if(session('password_success'))
        <x-alert type="success" class="mb-4" dismissible>{{ session('password_success') }}</x-alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('web.profile.update') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            @method('PUT')

            <h2 class="brand-font text-lg font-semibold text-slate-900">Datos de cuenta</h2>

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nombre</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200" required>
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Correo</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200" required>
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="address" class="mb-1 block text-sm font-medium text-slate-700">Direccion</label>
                <input id="address" name="address" value="{{ old('address', $user->address) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="billing_address" class="mb-1 block text-sm font-medium text-slate-700">Direccion de facturacion</label>
                <input id="billing_address" name="billing_address" value="{{ old('billing_address', $user->billing_address) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                @error('billing_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Guardar perfil</button>
        </form>

        <form method="POST" action="{{ route('web.profile.password.update') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            @method('PUT')

            <h2 class="brand-font text-lg font-semibold text-slate-900">Seguridad</h2>

            <div>
                <label for="current_password" class="mb-1 block text-sm font-medium text-slate-700">Contrasena actual</label>
                <input id="current_password" name="current_password" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Nueva contrasena</label>
                <input id="password" name="password" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirmar nueva contrasena</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                @error('password_confirmation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Actualizar contrasena</button>
        </form>
    </div>
@endsection
