@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-30">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
                <p class="text-sm text-slate-400">Detalles del producto</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg font-medium transition">
                ← Atrás
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="p-6 max-w-4xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Product Card -->
            <div class="lg:col-span-1">
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 sticky top-24">
                    <!-- Foto -->
                    @if ($product->photo)
                        <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="w-full h-64 object-cover rounded-lg mb-4">
                    @else
                        <div class="w-full h-64 bg-gradient-to-br from-cyan-400 to-blue-600 rounded-lg mb-4 flex items-center justify-center">
                            <span class="text-4xl font-bold text-white">{{ substr($product->name, 0, 1) }}</span>
                        </div>
                    @endif

                    <!-- Info Box -->
                    <div class="space-y-3">
                        <div class="bg-slate-800 p-4 rounded-lg">
                            <p class="text-slate-400 text-sm">Precio</p>
                            <p class="text-2xl font-bold text-emerald-400">${{ number_format($product->price, 2) }}</p>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-lg">
                            <p class="text-slate-400 text-sm">Stock</p>
                            <div class="flex items-center justify-between">
                                <p class="text-2xl font-bold">{{ $product->stock }}</p>
                                @if ($product->stock > 5)
                                    <span class="px-3 py-1 bg-emerald-900 text-emerald-200 rounded-full text-sm">✓ Disponible</span>
                                @elseif ($product->stock > 0)
                                    <span class="px-3 py-1 bg-amber-900 text-amber-200 rounded-full text-sm">⚠ Bajo</span>
                                @else
                                    <span class="px-3 py-1 bg-red-900 text-red-200 rounded-full text-sm">✗ Agotado</span>
                                @endif
                            </div>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-lg">
                            <p class="text-slate-400 text-sm">Proveedor</p>
                            <a href="{{ route('admin.providers.show', $product->provider) }}" class="text-blue-400 hover:text-blue-300 font-semibold">
                                {{ $product->provider->name }}
                            </a>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-lg">
                            <p class="text-slate-400 text-sm">Categoría</p>
                            <p class="font-semibold">{{ $product->category }}</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 mt-4">
                        <a href="{{ route('admin.products.edit', $product) }}" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium text-center transition">
                            ✏️ Editar
                        </a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="flex-1" onsubmit="return confirm('¿Eliminar este producto? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg font-medium transition">
                                🗑️ Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Descripción -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Descripción</h2>
                    @if ($product->description)
                        <p class="text-slate-300 leading-relaxed">{{ $product->description }}</p>
                    @else
                        <p class="text-slate-400 italic">Sin descripción</p>
                    @endif
                </div>

                <!-- Especificaciones -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Especificaciones</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-slate-800">
                            <span class="text-slate-400">Peso Mínimo</span>
                            <span class="font-semibold">{{ $product->min_weight_kg ? $product->min_weight_kg . ' kg' : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-800">
                            <span class="text-slate-400">Peso Máximo</span>
                            <span class="font-semibold">{{ $product->max_weight_kg ? $product->max_weight_kg . ' kg' : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-slate-400">Razas Recomendadas</span>
                            <span class="font-semibold">{{ $product->recommended_breeds_string ?? 'Todas' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Información de Control -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Información de Control</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-slate-800">
                            <span class="text-slate-400">ID del Producto</span>
                            <span class="font-monospace text-slate-300">{{ $product->id }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-800">
                            <span class="text-slate-400">Creado</span>
                            <span class="text-slate-300">{{ $product->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-slate-400">Última Actualización</span>
                            <span class="text-slate-300">{{ $product->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Información del Proveedor -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Proveedor</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-slate-800">
                            <span class="text-slate-400">Nombre</span>
                            <a href="{{ route('admin.providers.show', $product->provider) }}" class="text-blue-400 hover:text-blue-300 font-semibold">
                                {{ $product->provider->name }}
                            </a>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-800">
                            <span class="text-slate-400">Email</span>
                            <span class="text-slate-300">{{ $product->provider->email }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-800">
                            <span class="text-slate-400">Teléfono</span>
                            <span class="text-slate-300">{{ $product->provider->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-slate-400">Dirección</span>
                            <span class="text-slate-300">{{ $product->provider->address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
