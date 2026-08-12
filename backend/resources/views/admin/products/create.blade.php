@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-30">
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Nuevo Producto</h1>
                <p class="text-sm text-slate-400">Crear un nuevo producto en el catálogo</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg font-medium transition">
                ← Atrás
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="p-6 max-w-2xl mx-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-8">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Proveedor -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Proveedor *</label>
                    <select name="provider_id" class="w-full px-4 py-2 bg-slate-800 border @error('provider_id') border-red-500 @else border-slate-700 @enderror rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">-- Seleccionar Proveedor --</option>
                        @foreach ($providers as $provider)
                            <option value="{{ $provider->id }}" @selected(old('provider_id') == $provider->id)>
                                {{ $provider->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('provider_id')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Nombre del Producto *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Ej: Alimento Premium para Perros" 
                        class="w-full px-4 py-2 bg-slate-800 border @error('name') border-red-500 @else border-slate-700 @enderror rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Categoría -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Categoría *</label>
                    <input type="text" name="category" value="{{ old('category') }}" placeholder="Ej: Alimentos, Accesorios, Medicinas" 
                        class="w-full px-4 py-2 bg-slate-800 border @error('category') border-red-500 @else border-slate-700 @enderror rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('category')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Descripción</label>
                    <textarea name="description" rows="4" placeholder="Descripción del producto..." 
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Precio y Stock -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Precio ($) *</label>
                        <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0.01" placeholder="0.00" 
                            class="w-full px-4 py-2 bg-slate-800 border @error('price') border-red-500 @else border-slate-700 @enderror rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('price')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Stock *</label>
                        <input type="number" name="stock" value="{{ old('stock') }}" min="0" placeholder="0" 
                            class="w-full px-4 py-2 bg-slate-800 border @error('stock') border-red-500 @else border-slate-700 @enderror rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('stock')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Peso Mínimo y Máximo -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Peso Mínimo (kg)</label>
                        <input type="number" name="min_weight_kg" value="{{ old('min_weight_kg') }}" step="0.1" min="0" placeholder="0.0" 
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('min_weight_kg')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Peso Máximo (kg)</label>
                        <input type="number" name="max_weight_kg" value="{{ old('max_weight_kg') }}" step="0.1" min="0" placeholder="0.0" 
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('max_weight_kg')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Razas Recomendadas -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Razas Recomendadas (separadas por coma)</label>
                    <input type="text" name="recommended_breeds" value="{{ old('recommended_breeds') }}" placeholder="Ej: Labrador, Golden Retriever, Beagle" 
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('recommended_breeds')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Foto -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Foto del Producto</label>
                    <input type="file" name="photo" accept="image/*" 
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-400 mt-1">Máximo 2MB. Formatos: JPG, PNG, WebP</p>
                    @error('photo')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 rounded-lg font-semibold transition">
                        ✓ Crear Producto
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="flex-1 px-6 py-3 bg-slate-700 hover:bg-slate-600 rounded-lg font-semibold text-center transition">
                        ✗ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
