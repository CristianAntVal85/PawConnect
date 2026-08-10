@extends('layouts.app')

@section('title', 'Lugar - ' . $place['name'])

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="{{ route('places.index') }}" class="text-blue-600 hover:text-blue-700">← Volver a lugares</a>
        </div>

        <!-- Card principal -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Foto -->
            @if($place['photo'])
                <div class="w-full h-96 overflow-hidden bg-gray-200">
                    <img src="{{ $place['photo'] }}" alt="{{ $place['name'] }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="w-full h-96 bg-gradient-to-br from-blue-200 to-blue-300 flex items-center justify-center">
                    <span class="text-8xl">
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
            <div class="p-8">
                <!-- Encabezado con título y botón favorito -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900">{{ $place['name'] }}</h1>
                        <p class="text-lg text-gray-600 mt-2">
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                                {{ ucfirst($place['category']) }}
                            </span>
                        </p>
                    </div>
                    @auth
                        <button 
                            class="favorite-toggle-btn px-6 py-3 rounded-lg text-lg font-semibold transition"
                            data-place-id="{{ $place['id'] }}"
                            :class="isFavorite ? 'bg-red-100 text-red-600 hover:bg-red-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        >
                            <span class="favorite-icon">{{ $isFavorite ? '❤️' : '🤍' }}</span>
                            <span class="ml-2">{{ $isFavorite ? 'Guardado' : 'Guardar' }}</span>
                        </button>
                    @endauth
                </div>

                <!-- Grid: Info + Mapa -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Información -->
                    <div class="space-y-6">
                        <!-- Descripción -->
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900 mb-3">Descripción</h2>
                            <p class="text-gray-700 leading-relaxed">{{ $place['description'] }}</p>
                        </div>

                        <!-- Contacto -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">📞 Contacto</h3>
                            <div class="space-y-3">
                                <!-- Dirección -->
                                <div>
                                    <p class="text-sm text-gray-600">Dirección</p>
                                    <p class="text-gray-900 font-semibold">📍 {{ $place['address'] }}</p>
                                </div>

                                <!-- Teléfono -->
                                @if($place['phone'])
                                    <div>
                                        <p class="text-sm text-gray-600">Teléfono</p>
                                        <a href="tel:{{ $place['phone'] }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                                            📱 {{ $place['phone'] }}
                                        </a>
                                    </div>
                                @endif

                                <!-- Sitio web -->
                                @if($place['website'])
                                    <div>
                                        <p class="text-sm text-gray-600">Sitio web</p>
                                        <a href="{{ $place['website'] }}" target="_blank" class="text-blue-600 hover:text-blue-700 font-semibold">
                                            🌐 {{ str_replace('https://', '', $place['website']) }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Creador -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sugerido por</h3>
                            @if(isset($place['creator']))
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($place['creator']['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $place['creator']['name'] }}</p>
                                        <p class="text-sm text-gray-600">Miembro de la comunidad</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Estado -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Estado</h3>
                            @switch($place['status'])
                                @case('approved')
                                    <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg font-semibold">
                                        ✓ Aprobado
                                    </div>
                                    @break
                                @case('pending')
                                    <div class="inline-flex items-center px-4 py-2 bg-amber-100 text-amber-700 rounded-lg font-semibold">
                                        ⏳ Pendiente de aprobación
                                    </div>
                                    @break
                                @case('rejected')
                                    <div class="inline-flex items-center px-4 py-2 bg-red-100 text-red-700 rounded-lg font-semibold">
                                        ✗ Rechazado
                                    </div>
                                    @break
                            @endswitch
                        </div>
                    </div>

                    <!-- Mapa -->
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-3">Ubicación</h2>
                        <div id="place-map" class="w-full h-96 rounded-lg shadow-md border border-gray-300"></div>
                        <p class="text-sm text-gray-600 mt-2">
                            📍 <strong>Coordenadas:</strong> {{ number_format($place['lat'], 4) }}, {{ number_format($place['lng'], 4) }}
                        </p>
                    </div>
                </div>

                <!-- Reseñas -->
                <div class="border-t pt-8" id="reviews-section">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Reseñas</h2>

                    <!-- Formulario para agregar reseña -->
                    @auth
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Deja tu reseña</h3>
                            <form id="review-form" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Calificación *</label>
                                    <div class="flex gap-2" id="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button 
                                                type="button" 
                                                class="star-btn text-4xl hover:scale-110 transition"
                                                data-rating="{{ $i }}"
                                            >
                                                ⭐
                                            </button>
                                        @endfor
                                    </div>
                                    <input type="hidden" id="rating-input" name="rating" required>
                                    <p class="text-sm text-gray-600 mt-2" id="rating-display">Selecciona una calificación</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Comentario *</label>
                                    <textarea 
                                        name="body"
                                        rows="4"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Comparte tu experiencia en este lugar..."
                                        maxlength="1000"
                                        required
                                    ></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Máximo 1000 caracteres</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto (opcional)</label>
                                    <div 
                                        class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition"
                                        id="photo-drop"
                                    >
                                        <p class="text-gray-600">📸 Arrastra una foto aquí o haz clic para seleccionar</p>
                                        <input type="file" name="photo" accept="image/*" id="photo-input" style="display: none;">
                                    </div>
                                    <img id="photo-preview" src="" alt="" class="mt-4 max-h-48 rounded-lg" style="display: none;">
                                </div>

                                <button 
                                    type="submit"
                                    class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                                >
                                    Enviar Reseña
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8 text-center">
                            <p class="text-gray-600">
                                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">Inicia sesión</a>
                                para dejar una reseña
                            </p>
                        </div>
                    @endauth

                    <!-- Lista de reseñas -->
                    <div class="space-y-4" id="reviews-list">
                        <!-- Se cargarán dinámicamente con JavaScript -->
                        <div class="text-center py-8">
                            <p class="text-gray-500">Cargando reseñas...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="mt-8 flex gap-4">
            <a 
                href="{{ route('places.index') }}" 
                class="flex-1 md:flex-none px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold text-center"
            >
                ← Volver a lugares
            </a>
            @auth
                @if(auth()->user()->id === ($place['created_by'] ?? null) || auth()->user()->role === 'admin')
                    <a 
                        href="#" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                    >
                        ✏️ Editar lugar
                    </a>
                @endif
            @endauth
        </div>
    </div>
</div>

<!-- Leaflet CSS y JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const AUTH_TOKEN = localStorage.getItem('auth_token');
    const place = @json($place);

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar mapa
        initializeMap();

        // Cargar reseñas
        loadReviews();

        // Toggle favorito
        const favBtn = document.querySelector('.favorite-toggle-btn');
        if (favBtn) {
            favBtn.addEventListener('click', function() {
                toggleFavorite(place.id, this);
            });
        }

        // Formulario de reseña
        const reviewForm = document.getElementById('review-form');
        if (reviewForm) {
            setupReviewForm();
        }
    });

    function initializeMap() {
        // Inicializar mapa
        const map = L.map('place-map').setView([place.lat, place.lng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        // Marcador del lugar
        const categoryIcons = {
            pipican: '🏊‍♂️',
            beach: '🏖️',
            park: '🌳',
            vet: '🏥',
            shop: '🛒',
            restaurant: '🍽️',
            hotel: '🏨',
        };

        const icon = categoryIcons[place.category] || '📍';
        const customIcon = L.divIcon({
            html: `<div class="text-4xl">${icon}</div>`,
            className: 'custom-marker',
            iconSize: [50, 50],
        });

        L.marker([place.lat, place.lng], { icon: customIcon })
            .bindPopup(`<strong>${place.name}</strong><br>${place.address}`)
            .addTo(map)
            .openPopup();
    }

    function toggleFavorite(placeId, btn) {
        const isFavorite = btn.classList.contains('bg-red-100');
        const method = isFavorite ? 'DELETE' : 'POST';
        const url = `/api/v1/places/${placeId}/favorite`;

        fetch(url, {
            method: method,
            headers: {
                'Authorization': `Bearer ${AUTH_TOKEN}`,
                'Content-Type': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.message) {
                // Actualizar UI
                if (isFavorite) {
                    btn.classList.remove('bg-red-100', 'text-red-600', 'hover:bg-red-200');
                    btn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    btn.innerHTML = '<span class="favorite-icon">🤍</span><span class="ml-2">Guardar</span>';
                } else {
                    btn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    btn.classList.add('bg-red-100', 'text-red-600', 'hover:bg-red-200');
                    btn.innerHTML = '<span class="favorite-icon">❤️</span><span class="ml-2">Guardado</span>';
                }
            }
        })
        .catch(err => console.error(err));
    }

    function setupReviewForm() {
        const form = document.getElementById('review-form');
        const photoDrop = document.getElementById('photo-drop');
        const photoInput = document.getElementById('photo-input');
        const photoPreview = document.getElementById('photo-preview');
        const ratingInput = document.getElementById('rating-input');
        const ratingDisplay = document.getElementById('rating-display');
        const starBtns = document.querySelectorAll('.star-btn');

        // Star rating
        starBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const rating = this.dataset.rating;
                ratingInput.value = rating;
                ratingDisplay.textContent = `${rating} ${rating == 1 ? 'estrella' : 'estrellas'}`;
                
                // Actualizar estilos de estrellas
                starBtns.forEach((b, idx) => {
                    if (idx < rating) {
                        b.style.opacity = '1';
                    } else {
                        b.style.opacity = '0.3';
                    }
                });
            });

            // Hover effect
            btn.addEventListener('mouseenter', function() {
                const rating = this.dataset.rating;
                starBtns.forEach((b, idx) => {
                    if (idx < rating) {
                        b.style.opacity = '1';
                    } else {
                        b.style.opacity = '0.3';
                    }
                });
            });
        });

        document.getElementById('rating-stars').addEventListener('mouseleave', function() {
            const rating = ratingInput.value;
            if (rating) {
                starBtns.forEach((b, idx) => {
                    if (idx < rating) {
                        b.style.opacity = '1';
                    } else {
                        b.style.opacity = '0.3';
                    }
                });
            } else {
                starBtns.forEach(b => b.style.opacity = '1');
            }
        });

        // Photo drag & drop
        photoDrop.addEventListener('click', () => photoInput.click());
        photoDrop.addEventListener('dragover', e => {
            e.preventDefault();
            photoDrop.classList.add('border-blue-500', 'bg-blue-50');
        });
        photoDrop.addEventListener('dragleave', () => {
            photoDrop.classList.remove('border-blue-500', 'bg-blue-50');
        });
        photoDrop.addEventListener('drop', e => {
            e.preventDefault();
            photoDrop.classList.remove('border-blue-500', 'bg-blue-50');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                photoInput.files = files;
                previewPhoto(files[0]);
            }
        });

        photoInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                previewPhoto(this.files[0]);
            }
        });

        function previewPhoto(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.src = e.target.result;
                photoPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            fetch(`/api/v1/places/${place.id}/reviews`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${AUTH_TOKEN}`,
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    form.reset();
                    photoPreview.style.display = 'none';
                    ratingInput.value = '';
                    ratingDisplay.textContent = 'Selecciona una calificación';
                    starBtns.forEach(b => b.style.opacity = '1');
                    loadReviews();
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enviar Reseña';
                    alert('¡Reseña enviada correctamente!');
                } else {
                    alert('Error al enviar la reseña');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error al enviar la reseña');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enviar Reseña';
            });
        });
    }

    function loadReviews() {
        fetch(`/api/v1/places/${place.id}/reviews`, {
            headers: AUTH_TOKEN ? { 'Authorization': `Bearer ${AUTH_TOKEN}` } : {}
        })
        .then(r => r.json())
        .then(data => {
            const reviewsList = document.getElementById('reviews-list');
            const reviews = data.data || [];

            if (reviews.length === 0) {
                reviewsList.innerHTML = '<div class="text-center py-8 text-gray-500">No hay reseñas aún. ¡Sé el primero en comentar!</div>';
                return;
            }

            reviewsList.innerHTML = reviews.map(review => {
                const currentUserId = AUTH_TOKEN ? getCurrentUserId() : null;
                const isAuthor = currentUserId === review.user.id;

                return `
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    ${review.user.name.substring(0, 1).toUpperCase()}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">${review.user.name}</p>
                                    <p class="text-sm text-gray-600">${new Date(review.created_at).toLocaleDateString('es-ES')}</p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                ${[1,2,3,4,5].map(i => `<span>${i <= review.rating ? '⭐' : '☆'}</span>`).join('')}
                            </div>
                        </div>

                        <p class="text-gray-700 mb-3">${review.body}</p>

                        ${review.photo ? `<img src="${review.photo}" alt="Foto de reseña" class="w-full max-h-48 object-cover rounded-lg mb-3">` : ''}

                        ${isAuthor ? `
                            <div class="flex gap-2">
                                <button class="text-sm text-red-600 hover:text-red-700 font-semibold" onclick="deleteReview(${review.id})">
                                    🗑️ Eliminar
                                </button>
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        })
        .catch(err => console.error(err));
    }

    function getCurrentUserId() {
        if (!AUTH_TOKEN) return null;
        try {
            const payload = JSON.parse(atob(AUTH_TOKEN.split('.')[1]));
            return payload.sub;
        } catch (e) {
            return null;
        }
    }

    function deleteReview(reviewId) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta reseña?')) return;

        fetch(`/api/v1/places/${place.id}/reviews/${reviewId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${AUTH_TOKEN}`,
                'Content-Type': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.message) {
                loadReviews();
                alert('Reseña eliminada correctamente');
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
</style>
@endsection
