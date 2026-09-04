<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="api-token" content="{{ auth()->user()->tokens()->first()?->plainTextToken ?? '' }}">
    @endauth

    <title>@yield('title', config('app.name', 'PawConnect'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>


    <style>
        body {
            font-family: 'Source Sans 3', sans-serif;
        }

        h1, h2, h3, h4, h5, h6,
        .brand-font {
            font-family: 'Space Grotesk', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="relative min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.2),_transparent_40%),radial-gradient(circle_at_bottom_right,_rgba(59,130,246,0.2),_transparent_35%)]"></div>

        <div class="relative flex min-h-screen">
            <aside id="main-sidebar" class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-200 bg-white/95 p-6 backdrop-blur transition-transform duration-200 lg:static lg:translate-x-0">
                <a href="{{ url('/') }}" class="mb-8 inline-flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500 text-lg text-white">PC</span>
                    <span class="brand-font text-xl font-semibold tracking-tight">PawConnect</span>
                </a>

                <nav class="space-y-2 text-sm">
                    <a href="{{ route('web.dashboard') }}" class="block rounded-lg px-3 py-2 font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">Dashboard</a>
                    <a href="{{ route('web.pets.index') }}" class="block rounded-lg px-3 py-2 font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">Mascotas</a>
                    <a href="{{ route('web.matches.swipe') }}" class="block rounded-lg px-3 py-2 font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">Match</a>
                    <a href="{{ route('web.chat.index') }}" class="block rounded-lg px-3 py-2 font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">Chat</a>
                    <a href="{{ route('web.walks.index') }}" class="block rounded-lg px-3 py-2 font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">Paseos</a>
                    <a href="{{ route('web.marketplace.index') }}" class="block rounded-lg px-3 py-2 font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">Marketplace</a>
                    <a href="{{ route('web.orders.index') }}" class="block rounded-lg px-3 py-2 font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700">Mis Pedidos</a>
                </nav>
            </aside>

            <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-900/35 lg:hidden"></div>

            <div class="flex min-h-screen flex-1 flex-col lg:pl-0">
                <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/85 backdrop-blur">
                    <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button id="open-sidebar" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-300 text-slate-700 transition hover:bg-slate-100 lg:hidden" aria-label="Abrir menu lateral">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm1 4a1 1 0 100 2h12a1 1 0 100-2H4z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div>
                                <p class="brand-font text-lg font-semibold">@yield('page-title', 'Panel')</p>
                                <p class="text-xs text-slate-500">@yield('page-subtitle', 'Gestion de tu cuenta y mascotas')</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-notification-bell class="mr-2" />
                            <a href="{{ route('web.profile.edit') }}" class="hidden rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 sm:inline-flex">Mi perfil</a>
                            <a href="{{ route('web.pets.create') }}" class="inline-flex rounded-lg bg-emerald-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600">Nueva mascota</a>
                        </div>
                    </div>
                </header>

                <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    @hasSection('content')
                        @yield('content')
                    @else
                        {{ $slot ?? '' }}
                    @endif
                </main>
            </div>
        </div>
    </div>

    <script>
        const openSidebar = document.getElementById('open-sidebar');
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        const closeSidebar = () => {
            sidebar?.classList.add('-translate-x-full');
            overlay?.classList.add('hidden');
        };

        const showSidebar = () => {
            sidebar?.classList.remove('-translate-x-full');
            overlay?.classList.remove('hidden');
        };

        openSidebar?.addEventListener('click', showSidebar);
        overlay?.addEventListener('click', closeSidebar);
    </script>

    @stack('scripts')
</body>
</html>
