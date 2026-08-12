@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-30">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Proveedores</h1>
                <p class="text-sm text-slate-400">Gestión de proveedores y empresas</p>
            </div>
            <a href="{{ route('admin.providers.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 rounded-lg font-medium transition">
                ➕ Nuevo Proveedor
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="p-6">
        <!-- Messages -->
        @if ($message = session('success'))
            <div class="mb-4 p-4 bg-emerald-900 border border-emerald-700 text-emerald-200 rounded-lg">
                ✓ {{ $message }}
            </div>
        @endif
        @if ($message = session('error'))
            <div class="mb-4 p-4 bg-red-900 border border-red-700 text-red-200 rounded-lg">
                ✗ {{ $message }}
            </div>
        @endif

        <!-- Search -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 mb-6">
            <form method="GET" action="{{ route('admin.providers.index') }}" class="flex gap-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, email o teléfono..." 
                    class="flex-1 px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium transition">
                    Buscar
                </button>
                @if (request('search'))
                    <a href="{{ route('admin.providers.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg font-medium transition">
                        Limpiar
                    </a>
                @endif
            </form>
        </div>

        <!-- Providers Grid/Table -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($providers as $provider)
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 hover:border-slate-700 transition">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold">{{ $provider->name }}</h3>
                            <p class="text-sm text-slate-400">{{ $provider->products_count }} producto{{ $provider->products_count != 1 ? 's' : '' }}</p>
                        </div>
                        <div class="relative group">
                            <button class="text-slate-400 hover:text-slate-300 p-1">⋮</button>
                            <div class="absolute right-0 mt-2 w-48 bg-slate-800 border border-slate-700 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10">
                                <a href="{{ route('admin.providers.show', $provider) }}" class="block px-4 py-2 text-sm text-blue-400 hover:bg-slate-700 first:rounded-t-lg">
                                    👁️ Ver Detalles
                                </a>
                                <a href="{{ route('admin.providers.edit', $provider) }}" class="block px-4 py-2 text-sm text-blue-400 hover:bg-slate-700">
                                    ✏️ Editar
                                </a>
                                <form method="POST" action="{{ route('admin.providers.destroy', $provider) }}" class="block" onsubmit="return confirm('¿Eliminar este proveedor? Debe no tener productos asociados.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-slate-700 last:rounded-b-lg">
                                        🗑️ Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="text-slate-400">📧</span>
                            <a href="mailto:{{ $provider->email }}" class="text-blue-400 hover:text-blue-300">
                                {{ $provider->email }}
                            </a>
                        </div>
                        @if ($provider->phone)
                            <div class="flex items-center gap-2 text-sm text-slate-300">
                                <span class="text-slate-400">📱</span>
                                {{ $provider->phone }}
                            </div>
                        @endif
                        @if ($provider->address)
                            <div class="flex items-center gap-2 text-sm text-slate-300">
                                <span class="text-slate-400">📍</span>
                                {{ $provider->address }}
                            </div>
                        @endif
                    </div>

                    <!-- Button -->
                    <a href="{{ route('admin.providers.show', $provider) }}" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg text-center font-medium transition">
                        Ver Detalles →
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-slate-400 text-lg">No hay proveedores disponibles</p>
                    <a href="{{ route('admin.providers.create') }}" class="mt-4 inline-block px-6 py-2 bg-emerald-600 hover:bg-emerald-700 rounded-lg font-medium transition">
                        Crear el primer proveedor
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($providers->hasPages())
            <div class="mt-6">
                {{ $providers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
