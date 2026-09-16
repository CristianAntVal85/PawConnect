@extends('layouts.auth')

@section('title', 'Registro | PawConnect')
@section('page-title', 'Crear cuenta')
@section('page-subtitle', 'Empieza en PawConnect en menos de un minuto')

@section('content')
    <form id="register-form" method="POST" action="{{ route('register.store') }}" class="space-y-4">
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

        <x-photo-upload
            name="photo"
            label="Foto principal"
        />


        <div id="error-alert" class="hidden rounded-lg bg-red-50 border border-red-200 p-3 text-xs text-red-700"></div>

        <button type="submit" class="w-full rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600 disabled:opacity-60 disabled:cursor-not-allowed">
            Registrarme
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Ya tienes cuenta?
        <a href="{{ url('/login') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">Iniciar sesion</a>
    </p>
@endsection

@push('scripts')
<script>
    document.getElementById('register-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const errorAlert = document.getElementById('error-alert');

        // Deshabilitar botón durante la request
        submitButton.disabled = true;
        errorAlert.classList.add('hidden');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error al registrarse');
            }

            // ✅ Guardar token en localStorage
            if (data.token) {
                localStorage.setItem('auth_token', data.token);
            }

            // ✅ Redirigir al dashboard
            window.location.href = '/dashboard';

        } catch (error) {
            console.error('[Register Error]', error);
            errorAlert.textContent = error.message || 'Error al registrarse. Intenta de nuevo.';
            errorAlert.classList.remove('hidden');
            submitButton.disabled = false;
        }
    });
</script>
@endpush
