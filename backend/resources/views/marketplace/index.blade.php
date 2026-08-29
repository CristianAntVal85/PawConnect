@extends('layouts.app')

@section('title', 'Marketplace')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-slate-900 mb-2">🛍️ Marketplace PawConnect</h1>
            <p class="text-lg text-slate-600">Encuentra todo lo que necesitas para tu mascota</p>
        </div>

        <!-- Sección de Recomendaciones (si hay mascotas) -->
        @if($pets->isNotEmpty())
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 border-2 border-amber-200">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-1">✨ Recomendado para tu mascota</h2>
                    <p class="text-slate-600">Productos personalizados según las características de tu perro</p>
                </div>
                <div class="text-4xl">🎯</div>
            </div>

            <!-- Selector de mascota -->
            <div class="mb-6">
                <label for="pet-select" class="block text-sm font-semibold text-slate-700 mb-2">
                    Selecciona tu mascota:
                </label>
                <select id="pet-select" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Cargando mascotas --</option>
                    @foreach($pets as $pet)
                        <option value="{{ $pet->id }}">{{ $pet->name }} ({{ $pet->breed ?? 'Sin raza' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Contenedor de recomendaciones -->
            <div id="recommendations-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="col-span-full py-12 text-center">
                    <p class="text-slate-500">Selecciona una mascota para ver recomendaciones personalizadas</p>
                </div>
            </div>

            <!-- Paginación de recomendaciones -->
            <div id="recommendations-pagination" class="mt-6 flex justify-center gap-2 hidden">
                <!-- Será generada por JavaScript -->
            </div>
        </div>
        @else
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8 rounded">
            <p class="text-blue-700">
                <strong>💡 Tip:</strong>
                <a href="{{ route('web.pets.create') }}" class="font-semibold underline hover:text-blue-900">
                    Crea tu primera mascota
                </a>
                para ver recomendaciones personalizadas.
            </p>
        </div>
        @endif

        <!-- Filtros y Catálogo -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Panel de filtros (Sidebar) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                    <h3 class="text-xl font-bold text-slate-900 mb-4">🔍 Filtros</h3>

                    <!-- Filtro por Categoría -->
                    <div class="mb-6">
                        <label for="filter-category" class="block text-sm font-semibold text-slate-700 mb-2">
                            Categoría
                        </label>
                        <select id="filter-category" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Precio -->
                    <div class="mb-6">
                        <label for="filter-price" class="block text-sm font-semibold text-slate-700 mb-2">
                            Rango de precio
                        </label>
                        <input type="range" id="filter-price" min="0" max="1000" value="1000" class="w-full">
                        <div class="flex justify-between text-sm text-slate-600 mt-2">
                            <span>$0</span>
                            <span id="price-display">$1000+</span>
                        </div>
                    </div>

                    <!-- Filtro por Raza (si aplica) -->
                    <div class="mb-6">
                        <label for="filter-breed" class="block text-sm font-semibold text-slate-700 mb-2">
                            Buscar por raza
                        </label>
                        <input
                            type="text"
                            id="filter-breed"
                            placeholder="ej: Labrador"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                        >
                    </div>

                    <!-- Disponibilidad -->
                    <div class="mb-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="filter-in-stock" checked class="w-4 h-4 rounded">
                            <span class="text-sm font-semibold text-slate-700">Solo disponibles</span>
                        </label>
                    </div>

                    <!-- Botón Limpiar filtros -->
                    <button
                        id="reset-filters"
                        class="w-full px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-900 font-semibold rounded-lg transition-colors"
                    >
                        Limpiar filtros
                    </button>
                </div>
            </div>

            <!-- Grid de Productos -->
            <div class="lg:col-span-3">
                <!-- Controles superiores -->
                <div class="bg-white rounded-lg shadow-lg p-4 mb-6 flex justify-between items-center">
                    <div>
                        <p class="text-slate-700">
                            Mostrando <span id="products-count">0</span> productos
                        </p>
                    </div>
                    <div>
                        <select id="sort-by" class="px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">Ordenar por</option>
                            <option value="price-asc">Precio (menor a mayor)</option>
                            <option value="price-desc">Precio (mayor a menor)</option>
                            <option value="newest">Más recientes</option>
                        </select>
                    </div>
                </div>

                <!-- Grid de productos -->
                <div id="products-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div class="col-span-full py-12 text-center">
                        <p class="text-slate-500">Cargando productos...</p>
                    </div>
                </div>

                <!-- Paginación de productos -->
                <div id="products-pagination" class="mt-8 flex justify-center gap-2">
                    <!-- Será generada por JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para manejar filtros y recomendaciones -->
@vite(['resources/js/marketplace.js'])
@endsection
