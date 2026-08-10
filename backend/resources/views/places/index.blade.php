@extends('layouts.app')

@section('title', 'Lugares Pet-Friendly')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">🗺️ Lugares Pet-Friendly</h1>
                    <p class="text-gray-600 mt-2">Descubre lugares amigables para tu mascota cerca de ti</p>
                </div>
                @auth
                    <div class="flex gap-2">
                        <a href="{{ route('places.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <span class="mr-2">+</span> Sugerir lugar
                        </a>
                        <a href="{{ route('places.favorites') }}" class="inline-flex items-center px-4 py-2 bg-pink-100 text-pink-700 rounded-lg hover:bg-pink-200 transition">
                            <span class="mr-2">❤️</span> Mis favoritos
                        </a>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow p-4 mb-8">
            <form method="GET" action="{{ route('places.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Categoría -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                    <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected($selectedCategory === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Búsqueda -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        value="{{ $searchTerm }}"
                        placeholder="Nombre, descripción..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <!-- Botones -->
                <div class="flex items-end gap-2">
                    <button 
                        type="submit" 
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        Filtrar
                    </button>
                    <a 
                        href="{{ route('places.index') }}" 
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-center"
                    >
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Contenedor Principal: Mapa + Listado -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Mapa (2/3 en desktop, 1/1 en mobile) -->
            <div class="lg:col-span-2">
                <div 
                    id="map" 
                    class="w-full h-96 lg:h-screen rounded-lg shadow-lg"
                ></div>
            </div>

            <!-- Listado de Lugares (1/3 en desktop, 1/1 en mobile) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                        <h3 class="text-white font-semibold">
                            Lugares cercanos 
                            @if($pagination)
                                <span class="text-blue-100">({{ $pagination['total'] }} total)</span>
                            @endif
                        </h3>
                    </div>
                    
                    @if(count($places) > 0)
                        <div class="divide-y max-h-96 lg:max-h-screen overflow-y-auto">
                            @foreach($places as $place)
                                <div class="p-4 hover:bg-gray-50 transition cursor-pointer place-card" data-place-id="{{ $place['id'] }}">
                                    <div class="flex items-start gap-3">
                                        <!-- Icono por categoría -->
                                        <div class="text-2xl flex-shrink-0">
                                            @switch($place['category'])
                                                @case('pipican')
                                                    🏊‍♂️
                                                    @break
                                                @case('beach')
                                                    🏖️
                                                    @break
                                                @case('park')
                                                    🌳
                                                    @break
                                                @case('vet')
                                                    🏥
                                                    @break
                                                @case('shop')
                                                    🛒
                                                    @break
                                                @case('restaurant')
                                                    🍽️
                                                    @break
                                                @case('hotel')
                                                    🏨
                                                    @break
                                                @default
                                                    📍
                                            @endswitch
                                        </div>

                                        <!-- Info -->
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('places.show', $place['id']) }}" class="font-semibold text-gray-900 hover:text-blue-600 truncate">
                                                {{ $place['name'] }}
                                            </a>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ ucfirst($place['category']) }}
                                            </p>
                                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                                {{ $place['description'] }}
                                            </p>
                                            @if(isset($place['distance']))
                                                <p class="text-xs text-blue-600 font-semibold mt-1">
                                                    📍 {{ number_format($place['distance'], 1) }} km
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="flex gap-2 mt-3">
                                        <a 
                                            href="{{ route('places.show', $place['id']) }}" 
                                            class="flex-1 text-center text-xs px-2 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition"
                                        >
                                            Ver detalle
                                        </a>
                                        @auth
                                            <button 
                                                class="favorite-toggle-btn flex-1 text-xs px-2 py-1 rounded transition" 
                                                data-place-id="{{ $place['id'] }}"
                                                :class="isFavorited[{{ $place['id'] }}] ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'"
                                            >
                                                <span class="favorite-icon">❤️</span>
                                            </button>
                                        @endauth
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center text-gray-500">
                            <p class="text-lg">😢 No hay lugares en esta categoría</p>
                            <p class="text-sm mt-2">Intenta cambiar los filtros o sugerir uno nuevo</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS y JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Script del mapa -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Datos de lugares
        const places = @json($places);
        const categories = @json($categories);

        // Inicializar mapa centrado en España (aprox)
        const map = L.map('map').setView([40.0, -3.5], 6);

        // Añadir tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        // Iconos por categoría
        const categoryIcons = {
            pipican: '🏊‍♂️',
            beach: '🏖️',
            park: '🌳',
            vet: '🏥',
            shop: '🛒',
            restaurant: '🍽️',
            hotel: '🏨',
        };

        // Crear marcadores
        places.forEach(place => {
            const icon = categoryIcons[place.category] || '📍';
            
            // Crear div personalizado para el icono
            const customIcon = L.divIcon({
                html: `<div class="text-3xl">${icon}</div>`,
                className: 'custom-marker',
                iconSize: [40, 40],
            });

            const marker = L.marker([place.lat, place.lng], { icon: customIcon })
                .bindPopup(`
                    <div class="p-2">
                        <h4 class="font-bold">${place.name}</h4>
                        <p class="text-sm text-gray-600">${place.description.substring(0, 60)}...</p>
                        <div class="flex gap-2 mt-2">
                            <a href="/places/${place.id}" class="text-xs bg-blue-600 text-white px-2 py-1 rounded">Ver detalle</a>
                            <button class="text-xs bg-pink-100 text-pink-600 px-2 py-1 rounded favorite-btn" data-place-id="${place.id}">❤️ Fav</button>
                        </div>
                    </div>
                `)
                .addTo(map);

            // Evento al hacer clic en el marcador
            marker.on('click', function() {
                document.querySelector(`[data-place-id="${place.id}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });

        // Geolocalización del usuario
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;

                // Marcador azul para el usuario
                const userIcon = L.divIcon({
                    html: '<div class="text-2xl">📍</div>',
                    className: 'user-marker',
                    iconSize: [30, 30],
                });

                L.marker([userLat, userLng], { icon: userIcon })
                    .bindPopup('Tu ubicación')
                    .addTo(map);

                // Círculo de 50km alrededor del usuario
                L.circle([userLat, userLng], {
                    radius: 50000,
                    color: 'blue',
                    fillColor: 'blue',
                    fillOpacity: 0.1,
                }).addTo(map);

                // Centrar en el usuario
                map.fitBounds([[userLat - 0.5, userLng - 0.5], [userLat + 0.5, userLng + 0.5]]);
            });
        }

        // Toggle favorito
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const placeId = this.dataset.placeId;
                toggleFavorite(placeId);
            });
        });
    });

    function toggleFavorite(placeId) {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        
        fetch(`/api/v1/places/${placeId}/favorite`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                'Content-Type': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
            }
        })
        .catch(err => console.error(err));
    }
</script>

<style>
    .custom-marker {
        background: none !important;
        border: none !important;
        box-shadow: none !important;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }

    .user-marker {
        background: none !important;
        border: none !important;
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.5) !important;
    }

    .place-card {
        transition: all 0.2s ease;
    }

    .place-card:hover {
        background-color: #f3f4f6 !important;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
