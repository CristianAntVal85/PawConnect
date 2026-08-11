@extends('layouts.app')

@section('title', 'Mis Lugares Favoritos')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">❤️ Mis Lugares Favoritos</h1>
                    <p class="text-gray-600 mt-2">
                        @if(count($favorites) > 0)
                            {{ count($favorites) }} lugar{{ count($favorites) !== 1 ? 'es' : '' }} guardado{{ count($favorites) !== 1 ? 's' : '' }}
                        @else
                            Lugares que has guardado para visitar después
                        @endif
                    </p>
                </div>
                <a href="{{ route('places.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    🗺️ Ver todos los lugares
                </a>
            </div>
        </div>

        @if(count($favorites) > 0)
            <!-- Filtro de categoría (opcional) -->
            <div class="mb-6 flex flex-wrap gap-2">
                <a href="?page=1" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm font-semibold">
                    Todos
                </a>
                <a href="?page=1&category=park" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm font-semibold">
                    🌳 Parques
                </a>
                <a href="?page=1&category=vet" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm font-semibold">
                    🏥 Veterinarias
                </a>
                <a href="?page=1&category=beach" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm font-semibold">
                    🏖️ Playas
                </a>
            </div>

            <!-- Grid de lugares favoritos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($favorites as $place)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group favorite-card" data-place-id="{{ $place['id'] }}">
                        <!-- Imagen -->
                        @if($place['photo'])
                            <div class="w-full h-48 overflow-hidden bg-gray-200 relative">
                                <img src="{{ $place['photo'] }}" alt="{{ $place['name'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                            </div>
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-blue-200 to-blue-300 flex items-center justify-center group-hover:from-blue-300 group-hover:to-blue-400 transition-all duration-300">
                                <span class="text-6xl group-hover:scale-110 transition-transform duration-300">
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
                                </span>
                            </div>
                        @endif

                        <!-- Contenido -->
                        <div class="p-4">
                            <!-- Encabezado -->
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-900 line-clamp-2 group-hover:text-blue-600 transition">
                                        {{ $place['name'] }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded font-semibold capitalize">
                                            {{ ucfirst($place['category']) }}
                                        </span>
                                    </p>
                                </div>
                                <button 
                                    class="remove-favorite-btn flex-shrink-0 text-2xl hover:scale-125 transition-transform duration-200 active:scale-90 favorite-heart" 
                                    data-place-id="{{ $place['id'] }}"
                                    title="Quitar de favoritos"
                                >
                                    ❤️
                                </button>
                            </div>

                            <!-- Descripción -->
                            <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                {{ $place['description'] }}
                            </p>

                            <!-- Dirección -->
                            <p class="text-xs text-gray-500 mb-4">
                                📍 {{ $place['address'] }}
                            </p>

                            <!-- Botones de acción -->
                            <div class="flex gap-2">
                                <a 
                                    href="{{ route('places.show', $place['id']) }}" 
                                    class="flex-1 text-center text-sm px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                                >
                                    Ver detalle
                                </a>
                                @if($place['phone'])
                                    <a 
                                        href="tel:{{ $place['phone'] }}" 
                                        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                                        title="Llamar"
                                    >
                                        📱
                                    </a>
                                @endif
                                @if($place['website'])
                                    <a 
                                        href="{{ $place['website'] }}" 
                                        target="_blank"
                                        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                                        title="Visitar sitio web"
                                    >
                                        🌐
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Paginación -->
            @if($pagination && $pagination['last_page'] > 1)
                <div class="mt-12 flex justify-center items-center gap-2">
                    @if($pagination['current_page'] > 1)
                        <a 
                            href="?page={{ $pagination['current_page'] - 1 }}" 
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition font-semibold"
                        >
                            ← Anterior
                        </a>
                    @endif

                    <div class="flex gap-1">
                        @for($i = 1; $i <= $pagination['last_page']; $i++)
                            <a 
                                href="?page={{ $i }}"
                                class="px-3 py-2 rounded-lg font-semibold transition @if($i === $pagination['current_page']) bg-blue-600 text-white @else bg-white border border-gray-300 hover:bg-gray-50 @endif"
                            >
                                {{ $i }}
                            </a>
                        @endfor
                    </div>

                    @if($pagination['current_page'] < $pagination['last_page'])
                        <a 
                            href="?page={{ $pagination['current_page'] + 1 }}" 
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition font-semibold"
                        >
                            Siguiente →
                        </a>
                    @endif
                </div>
            @endif
        @else
            <!-- Estado vacío -->
            <div class="bg-white rounded-lg shadow-lg p-12 text-center max-w-2xl mx-auto">
                <div class="text-8xl mb-4 animate-bounce">📭</div>
                <h2 class="text-3xl font-bold text-gray-900 mb-3">No hay lugares favoritos</h2>
                <p class="text-gray-600 mb-8 text-lg">Explora nuestro mapa y guarda lugares para acceder a ellos rápidamente después.</p>
                <a 
                    href="{{ route('places.index') }}" 
                    class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-lg"
                >
                    🗺️ Ir al mapa de lugares
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.95);
        }
    }

    .favorite-card.removing {
        animation: fadeOut 0.3s ease-out forwards;
    }

    .favorite-heart {
        display: inline-block;
    }

    .favorite-heart.pulse {
        animation: pulse 0.6s ease-out;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.3);
        }
        100% {
            transform: scale(1);
        }
    }
</style>

<script>
    const AUTH_TOKEN = localStorage.getItem('auth_token');

    // Remover favoritos
    document.querySelectorAll('.remove-favorite-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const placeId = this.dataset.placeId;
            const card = this.closest('.favorite-card');
            const heart = this;

            // Animación del corazón
            heart.classList.add('pulse');

            // Confirmación
            setTimeout(() => {
                if (confirm('¿Quieres quitar este lugar de favoritos?')) {
                    // Enviar petición
                    fetch(`/api/v1/places/${placeId}/favorite`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${AUTH_TOKEN}`,
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.message) {
                            // Animación de salida
                            card.classList.add('removing');
                            setTimeout(() => {
                                // Remover del DOM y rechazar si está vacío
                                card.remove();
                                
                                // Si no hay más tarjetas, recargar página
                                const remainingCards = document.querySelectorAll('.favorite-card');
                                if (remainingCards.length === 0) {
                                    window.location.reload();
                                }
                            }, 300);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Hubo un error al quitar el favorito');
                        heart.classList.remove('pulse');
                    });
                } else {
                    heart.classList.remove('pulse');
                }
            }, 300);
        });
    });
</script>

        });
    });
</script>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
