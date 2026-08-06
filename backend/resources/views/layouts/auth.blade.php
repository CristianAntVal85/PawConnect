<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Auth | ' . config('app.name', 'PawConnect'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="relative flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,_rgba(251,191,36,0.26),_transparent_30%),radial-gradient(circle_at_80%_0%,_rgba(16,185,129,0.20),_transparent_25%),radial-gradient(circle_at_80%_80%,_rgba(59,130,246,0.20),_transparent_25%)]"></div>

        <div class="relative w-full max-w-md rounded-2xl border border-white/70 bg-white/85 p-6 shadow-2xl backdrop-blur sm:p-8">
            <a href="{{ url('/') }}" class="mb-6 inline-flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500 text-white">PC</span>
                <span class="text-lg font-semibold tracking-tight">PawConnect</span>
            </a>

            <div class="mb-6">
                <h1 class="text-2xl font-semibold tracking-tight">@yield('page-title', 'Bienvenido de vuelta')</h1>
                <p class="mt-1 text-sm text-slate-500">@yield('page-subtitle', 'Ingresa para continuar con tu cuenta')</p>
            </div>

            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif

            <p class="mt-8 text-center text-xs text-slate-500">@yield('auth-footer', 'PawConnect - Tu red para mascotas y cuidadores')</p>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
