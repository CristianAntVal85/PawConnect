@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-30">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Productos</h1>
                <p class="text-sm text-slate-400">Gestión de catálogo de productos</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 rounded-lg font-medium transition">
                ➕ Nuevo Producto
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

        <!-- Search and Filters -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 mb-6">
            <form method="GET" action="{{ route('admin.products.index') }}" class="space-y-4">
                <!-- Search -->
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar producto..."
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Filters Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Provider Filter -->
                    <select name="provider_id" class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos los Proveedores</option>
                        @foreach ($providers as $provider)
                            <option value="{{ $provider->id }}" @selected(request('provider_id') == $provider->id)>
                                {{ $provider->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Category Filter -->
                    <select name="category" class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas las Categorías</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category') == $cat)>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Price Range -->
                    <input type="number" name="min_price" step="0.01" value="{{ request('min_price') }}" placeholder="Precio mín."
                        class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="number" name="max_price" step="0.01" value="{{ request('max_price') }}" placeholder="Precio máx."
                        class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <!-- Availability Filter -->
                    <select name="availability" class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="available" @selected(request('availability') == 'available')>Disponibles</option>
                        <option value="low" @selected(request('availability') == 'low')>Stock Bajo</option>
                        <option value="out" @selected(request('availability') == 'out')>Agotados</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium transition">
                        Filtrar
                    </button>
                    @if (request()->hasAny(['search', 'provider_id', 'category', 'min_price', 'max_price', 'availability']))
                        <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg font-medium transition">
                            Limpiar
                        </a>
                    @endif
                    <a href="{{ route('admin.products.export') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Descargar CSV
                    </a>
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800 border-b border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Producto</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Categoría</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Proveedor</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Precio</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Stock</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse ($products as $product)
                            <tr class="hover:bg-slate-800/50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($product->photo)
                                            <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                                {{ substr($product->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium">{{ $product->name }}</p>
                                            <p class="text-sm text-slate-400">ID: {{ $product->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $product->category }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('admin.providers.show', $product->provider) }}" class="text-blue-400 hover:text-blue-300">
                                        {{ $product->provider->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 font-medium">${{ number_format($product->price, 2) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($product->stock > 5)
                                        <span class="px-3 py-1 bg-emerald-900 text-emerald-200 rounded-full text-sm">
                                            ✓ {{ $product->stock }}
                                        </span>
                                    @elseif ($product->stock > 0)
                                        <span class="px-3 py-1 bg-amber-900 text-amber-200 rounded-full text-sm">
                                            ⚠ {{ $product->stock }}
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-red-900 text-red-200 rounded-full text-sm">
                                            ✗ Agotado
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.products.show', $product) }}" class="text-blue-400 hover:text-blue-300 font-medium">
                                            Ver
                                        </a>
                                        <div class="relative group">
                                            <button class="text-slate-400 hover:text-slate-300 p-1">⋮</button>
                                            <div class="absolute right-0 mt-2 w-48 bg-slate-800 border border-slate-700 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10">
                                                <a href="{{ route('admin.products.edit', $product) }}" class="block px-4 py-2 text-sm text-blue-400 hover:bg-slate-700 first:rounded-t-lg">
                                                    ✏️ Editar
                                                </a>
                                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="block" onsubmit="return confirm('¿Eliminar este producto?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-slate-700 last:rounded-b-lg">
                                                        🗑️ Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                    No hay productos disponibles
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($products->hasPages())
                <div class="px-6 py-4 border-t border-slate-700">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
