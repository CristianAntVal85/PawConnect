@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-30">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ $provider->name }}</h1>
                <p class="text-sm text-slate-400">Detalles del proveedor</p>
            </div>
            <a href="{{ route('admin.providers.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg font-medium transition">
                ← Atrás
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="p-6 max-w-4xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Provider Card -->
            <div class="lg:col-span-1">
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 sticky top-24">
                    <!-- Avatar -->
                    <div class="w-full h-32 bg-gradient-to-br from-purple-400 to-pink-600 rounded-lg mb-4 flex items-center justify-center">
                        <span class="text-5xl font-bold text-white">{{ substr($provider->name, 0, 1) }}</span>
                    </div>

                    <!-- Info Box -->
                    <div class="space-y-3">
                        <div class="bg-slate-800 p-4 rounded-lg">
                            <p class="text-slate-400 text-sm">Productos</p>
                            <p class="text-2xl font-bold">{{ $provider->products_count ?? 0 }}</p>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-lg">
                            <p class="text-slate-400 text-sm">Email</p>
                            <a href="mailto:{{ $provider->email }}" class="text-blue-400 hover:text-blue-300 font-semibold break-all">
                                {{ $provider->email }}
                            </a>
                        </div>

                        @if ($provider->phone)
                            <div class="bg-slate-800 p-4 rounded-lg">
                                <p class="text-slate-400 text-sm">Teléfono</p>
                                <a href="tel:{{ $provider->phone }}" class="text-blue-400 hover:text-blue-300 font-semibold">
                                    {{ $provider->phone }}
                                </a>
                            </div>
                        @endif

                        @if ($provider->address)
                            <div class="bg-slate-800 p-4 rounded-lg">
                                <p class="text-slate-400 text-sm">Dirección</p>
                                <p class="font-semibold text-sm">{{ $provider->address }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 mt-4">
                        <a href="{{ route('admin.providers.edit', $provider) }}" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium text-center transition">
                            ✏️ Editar
                        </a>
                        <form method="POST" action="{{ route('admin.providers.destroy', $provider) }}" class="flex-1" onsubmit="return confirm('¿Eliminar este proveedor? Solo es posible si no tiene productos asociados.')">
                            @csrf
                            @method('DELETE')
                            <button class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg font-medium transition">
                                🗑️ Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Products -->
            <div class="lg:col-span-2">
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Productos ({{ $provider->products_count ?? 0 }})</h2>

                    @if ($products->count() > 0)
                        <div class="space-y-3">
                            @foreach ($products as $product)
                                <div class="bg-slate-800 p-4 rounded-lg flex items-center justify-between hover:bg-slate-700 transition">
                                    <div class="flex items-center gap-3">
                                        @if ($product->photo)
                                            <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="w-12 h-12 rounded object-cover">
                                        @else
                                            <div class="w-12 h-12 rounded bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white font-bold">
                                                {{ substr($product->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold">{{ $product->name }}</p>
                                            <p class="text-sm text-slate-400">{{ $product->category }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-emerald-400">${{ number_format($product->price, 2) }}</p>
                                        <p class="text-sm text-slate-400">
                                            @if ($product->stock > 5)
                                                <span class="text-emerald-400">✓ {{ $product->stock }} un</span>
                                            @elseif ($product->stock > 0)
                                                <span class="text-amber-400">⚠ {{ $product->stock }} un</span>
                                            @else
                                                <span class="text-red-400">✗ Agotado</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.products.show', $product) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                                        Ver producto →
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if ($products->hasPages())
                            <div class="mt-6 border-t border-slate-700 pt-6">
                                {{ $products->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <p class="text-slate-400">Este proveedor no tiene productos aún</p>
                            <a href="{{ route('admin.products.create') }}" class="mt-4 inline-block text-blue-400 hover:text-blue-300">
                                Crear primer producto →
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Provider Information -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 mt-6">
                    <h2 class="text-xl font-bold mb-4">Información de Control</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-slate-800">
                            <span class="text-slate-400">ID</span>
                            <span class="font-monospace text-slate-300">{{ $provider->id }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-800">
                            <span class="text-slate-400">Creado</span>
                            <span class="text-slate-300">{{ $provider->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-slate-400">Última Actualización</span>
                            <span class="text-slate-300">{{ $provider->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
