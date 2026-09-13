@extends('layouts.auth')

@section('title', 'Iniciar sesion | PawConnect')
@section('page-title', 'Iniciar sesion')
@section('page-subtitle', 'Accede para gestionar tus mascotas y actividades')

@section('content')
    <div id="login-form-container">
        <form id="login-form" method="POST" action="{{ route('login.store') }}" class="space-y-4 sm:space-y-5">
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

            <div id="error-alert" class="hidden rounded-lg bg-red-50 border border-red-200 p-3 text-xs text-red-700"></div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 text-xs sm:text-sm">
                <label class="inline-flex items-center gap-2 text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                    <span>Recordarme</span>
                </label>
                <a href="{{ url('/forgot-password') }}" class="font-medium text-emerald-600 hover:text-emerald-700">Olvide mi contrasena</a>
            </div>

            <button type="submit" class="w-full rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600 active:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed">
                Entrar
            </button>
        </form>

        <p class="mt-6 text-center text-xs sm:text-sm text-slate-600">
            No tienes cuenta?
            <a href="{{ url('/register') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">Crear cuenta</a>
        </p>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('login-form').addEventListener('submit', async (e) => {
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
                throw new Error(data.message || 'Error al iniciar sesión');
            }

            // ✅ Guardar token en localStorage
            if (data.token) {
                localStorage.setItem('auth_token', data.token);
            }

            // ✅ Redirigir al dashboard
            const redirectUrl = data.redirect_url || '/dashboard';
            window.location.href = redirectUrl;

        } catch (error) {
            console.error('[Login Error]', error);
            errorAlert.textContent = error.message || 'Error al iniciar sesión. Intenta de nuevo.';
            errorAlert.classList.remove('hidden');
            submitButton.disabled = false;
        }
    });
</script>
@endpush
