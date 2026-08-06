<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin | ' . config('app.name', 'PawConnect'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'IBM Plex Sans', sans-serif;
        }

        h1, h2, h3, h4, h5, h6,
        .admin-font {
            font-family: 'Space Grotesk', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="relative min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_10%_15%,_rgba(14,165,233,0.22),_transparent_30%),radial-gradient(circle_at_85%_10%,_rgba(239,68,68,0.18),_transparent_30%)]"></div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
            <header class="mb-6 rounded-2xl border border-slate-800 bg-slate-900/70 p-4 backdrop-blur sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="admin-font text-xs uppercase tracking-[0.2em] text-sky-300">Control Center</p>
                        <h1 class="admin-font text-2xl font-semibold">@yield('page-title', 'Panel Administrador')</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="#" class="rounded-lg border border-slate-700 px-3 py-2 text-sm font-medium text-slate-200 transition hover:bg-slate-800">Ver sitio</a>
                        <a href="#" class="rounded-lg bg-rose-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-600">Alertas</a>
                    </div>
                </div>
            </header>

            <nav class="mb-6 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
                <a href="#" class="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2 text-center text-sm font-medium text-slate-200 transition hover:border-sky-400 hover:text-sky-300">Usuarios</a>
                <a href="#" class="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2 text-center text-sm font-medium text-slate-200 transition hover:border-sky-400 hover:text-sky-300">Mascotas</a>
                <a href="#" class="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2 text-center text-sm font-medium text-slate-200 transition hover:border-sky-400 hover:text-sky-300">Pedidos</a>
                <a href="#" class="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2 text-center text-sm font-medium text-slate-200 transition hover:border-sky-400 hover:text-sky-300">Productos</a>
                <a href="#" class="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2 text-center text-sm font-medium text-slate-200 transition hover:border-sky-400 hover:text-sky-300">Lugares</a>
                <a href="#" class="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2 text-center text-sm font-medium text-slate-200 transition hover:border-sky-400 hover:text-sky-300">Resenas</a>
            </nav>

            <main class="flex-1 rounded-2xl border border-slate-800 bg-slate-900/60 p-4 sm:p-6">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
