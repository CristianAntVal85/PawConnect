@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header con nombre de mascota y estado general -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Historial Sanitario de {{ $pet->name }}
                        </h1>
                        <p class="text-gray-600 mt-2">
                            {{ ucfirst($pet->species) }} · {{ $pet->breed }}
                        </p>
                    </div>
                    <a href="{{ route('web.pets.show', $pet) }}"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                        Volver a mascota
                    </a>
                </div>

                <!-- Estado General (Semáforo) -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">Estado de Salud General</h3>
                            <p class="text-gray-600 mt-1">
                                @switch($generalStatus)
                                    @case('green')
                                        ✓ Todo al día
                                    @break

                                    @case('yellow')
                                        ⏰ Hay registros próximos a vencer
                                    @break

                                    @case('red')
                                        ⚠️ Hay registros vencidos
                                    @break
                                @endswitch
                            </p>
                        </div>
                        <div class="text-4xl">
                            @if ($generalStatus === 'green')
                                🟢
                            @elseif ($generalStatus === 'yellow')
                                🟡
                            @else
                                🔴
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Veterinarios Recomendados (si hay registros críticos) -->
            @if ($recommendedVeterinarians->isNotEmpty())
                <div class="mt-8">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg shadow-md p-6">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="text-3xl">🏥</div>
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900">Veterinarios Cercanos Recomendados</h2>
                                <p class="text-gray-700 mt-1">
                                    Encontramos veterinarios cercanos que pueden ayudarte con los registros próximos a vencer o vencidos.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                            @foreach ($recommendedVeterinarians as $vet)
                                @php
                                    $place = $vet->place;
                                    $urgencyEmoji = $veterinarianService->getUrgencyEmoji($vet->urgency);
                                    $servicesDesc = $veterinarianService->getServicesDescription($vet->needed_services);
                                @endphp
                                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden group">
                                    <!-- Imagen o icono -->
                                    @if ($place->photo)
                                        <div class="w-full h-32 overflow-hidden bg-gray-200 group-hover:opacity-90 transition">
                                            <img src="{{ $place->photo }}" alt="{{ $place->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                        </div>
                                    @else
                                        <div class="w-full h-32 bg-gradient-to-br from-blue-300 to-blue-500 flex items-center justify-center group-hover:from-blue-400 group-hover:to-blue-600 transition">
                                            <span class="text-5xl">🏥</span>
                                        </div>
                                    @endif

                                    <!-- Contenido -->
                                    <div class="p-4">
                                        <!-- Nombre y urgencia -->
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="flex-1">
                                                <h3 class="text-lg font-bold text-gray-900 line-clamp-2">{{ $place->name }}</h3>
                                                <p class="text-xs text-blue-600 font-semibold mt-1">
                                                    {{ number_format($vet->distance, 1) }} km de distancia
                                                </p>
                                            </div>
                                            <div class="text-2xl flex-shrink-0">{{ $urgencyEmoji }}</div>
                                        </div>

                                        <!-- Servicios necesarios -->
                                        <div class="mb-3 p-2 bg-blue-50 rounded">
                                            <p class="text-xs text-gray-600 uppercase tracking-wide font-semibold">Servicios Necesarios</p>
                                            <p class="text-sm text-gray-900 mt-1">{{ $servicesDesc }}</p>
                                        </div>

                                        <!-- Descripción -->
                                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $place->description }}</p>

                                        <!-- Contacto -->
                                        <div class="space-y-2 mb-4 text-sm">
                                            @if ($place->phone)
                                                <div class="flex items-center gap-2 text-gray-700">
                                                    <span>📱</span>
                                                    <a href="tel:{{ $place->phone }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                                                        {{ $place->phone }}
                                                    </a>
                                                </div>
                                            @endif
                                            @if ($place->website)
                                                <div class="flex items-center gap-2 text-gray-700">
                                                    <span>🌐</span>
                                                    <a href="{{ $place->website }}" target="_blank" class="text-blue-600 hover:text-blue-700 font-semibold truncate">
                                                        Sitio web
                                                    </a>
                                                </div>
                                            @endif
                                            <div class="flex items-center gap-2 text-gray-700">
                                                <span>📍</span>
                                                <span class="text-sm">{{ $place->address }}</span>
                                            </div>
                                        </div>

                                        <!-- Botones -->
                                        <div class="flex gap-2">
                                            <a href="{{ route('places.show', $place) }}"
                                                class="flex-1 text-center text-sm px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                                                Ver detalles
                                            </a>
                                            <button type="button"
                                                class="favorite-toggle-btn text-2xl hover:scale-110 transition"
                                                data-place-id="{{ $place->id }}">
                                                🤍
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Nota de información -->
                        <div class="mt-6 p-4 bg-white bg-opacity-70 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <span class="font-semibold">💡 Consejo:</span> Puedes guardar estos veterinarios en favoritos para acceder a ellos fácilmente más adelante.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Timeline de registros -->
            <div class="mt-8">
                @if ($records->isEmpty())
                    <div class="p-8 text-center">
                        <p class="text-gray-500 text-lg">
                            No hay registros sanitarios. Crea uno para comenzar a hacer seguimiento.
                        </p>
                        <a href="{{ route('web.pets.show', $pet) }}"
                            class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Agregar Registro
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach ($records as $record)
                            @php
                                $status = $record['health_status'];
                                $statusColor = match ($status['status']) {
                                    'green' => 'bg-green-50 border-l-4 border-green-500',
                                    'yellow' => 'bg-yellow-50 border-l-4 border-yellow-500',
                                    'red' => 'bg-red-50 border-l-4 border-red-500',
                                };
                                $iconEmoji = match ($status['status']) {
                                    'green' => '✓',
                                    'yellow' => '⏰',
                                    'red' => '⚠️',
                                };
                                $typeLabel = match ($record['type']) {
                                    'vaccination' => 'Vacunación',
                                    'deworming' => 'Desparasitación',
                                    'checkup' => 'Chequeo',
                                    'surgery' => 'Cirugía',
                                    'medication' => 'Medicamento',
                                    default => ucfirst($record['type']),
                                };
                            @endphp

                            <div class="{{ $statusColor }} p-6 hover:bg-opacity-75 transition">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <!-- Info del registro -->
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-2xl">{{ $iconEmoji }}</span>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $typeLabel }}</h3>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-600">Última revisión</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $record['last_date'] ? \Carbon\Carbon::parse($record['last_date'])->format('d/m/Y') : 'Sin registrar' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600">Próxima revisión</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $record['next_due_date'] ? \Carbon\Carbon::parse($record['next_due_date'])->format('d/m/Y') : 'Sin registrar' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600">Periodicidad</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $record['periodicity_days'] ?? '-' }} días
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600">Estado</p>
                                                <p class="font-medium text-gray-900">{{ $status['message'] }}</p>
                                            </div>
                                        </div>

                                        @if ($record['notes'])
                                            <div class="mt-3 p-3 bg-white bg-opacity-50 rounded text-gray-700">
                                                <p class="text-xs text-gray-600 uppercase tracking-wide">Notas</p>
                                                <p>{{ $record['notes'] }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Acciones -->
                                    <div class="flex flex-col gap-2">

                                        {{-- Renovar --}}
                                        <form action="{{ route('web.pets.health.renew', [$pet->id, $record['id']]) }}"
                                            method="POST">
                                            @csrf
                                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                                🔄 Renovar
                                            </button>
                                        </form>

                                        {{-- Editar --}}
                                        <a href="{{ route('web.pets.health.edit', [$pet->id, $record['id']]) }}"
                                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-medium text-center">
                                            ✏️ Editar
                                        </a>

                                        {{-- Eliminar --}}
                                        <form action="{{ route('web.pets.health.destroy', [$pet->id, $record['id']]) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Seguro que deseas eliminar este registro sanitario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                                                🗑️ Eliminar
                                            </button>
                                        </form>

                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Leyenda de colores -->
            <div class="mt-8 grid grid-cols-3 gap-4">
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <p class="text-green-900 font-semibold">🟢 Al día</p>
                    <p class="text-green-700 text-sm">Faltan más de 7 días</p>
                </div>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <p class="text-yellow-900 font-semibold">🟡 Próximo a vencer</p>
                    <p class="text-yellow-700 text-sm">Faltan 0-7 días</p>
                </div>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <p class="text-red-900 font-semibold">🔴 Vencido</p>
                    <p class="text-red-700 text-sm">Revisión pendiente</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="renewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md mx-4">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Renovar registro sanitario</h2>
            <p class="text-gray-600 mb-6">¿Deseas renovar este registro? La fecha de última revisión se actualizará a hoy.</p>
            <div class="flex gap-4">
                <button type="button" id="confirmRenew"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    Renovar
                </button>
                <button type="button" id="cancelRenew"
                    class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-medium">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de productos relacionados -->
    <div id="productsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-4xl mx-4 my-8 w-full">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900" id="productsTitle">Productos relacionados</h2>
                    <p class="text-gray-600 text-sm mt-1" id="productsSubtitle">Recomendados para tu mascota</p>
                </div>
                <button type="button" id="closeProducts"
                    class="text-2xl text-gray-500 hover:text-gray-700">
                    ✕
                </button>
            </div>

            <div id="productsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto mb-6">
                <!-- Productos cargados via AJAX -->
            </div>

            <div class="border-t pt-4">
                <button type="button" id="visitMarketplace"
                    class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    🛒 Ver todos en marketplace
                </button>
            </div>
        </div>
    </div>

    <script>
        let renewingPetId = null;
        let renewingHealthId = null;

        document.querySelectorAll('.renew-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                renewingPetId = this.dataset.petId;
                renewingHealthId = this.dataset.healthId;
                document.getElementById('renewModal').classList.remove('hidden');
            });
        });

        document.getElementById('cancelRenew').addEventListener('click', function() {
            document.getElementById('renewModal').classList.add('hidden');
        });

        document.getElementById('confirmRenew').addEventListener('click', async function() {
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Renovando...';

            try {
                const response = await fetch(`/api/v1/pets/${renewingPetId}/health/${renewingHealthId}/renew`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    // Mostrar mensaje de éxito
                    alert('✓ Registro renovado correctamente');
                    // Recargar la página
                    location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Error al renovar el registro');
                    btn.disabled = false;
                    btn.textContent = 'Renovar';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión');
                btn.disabled = false;
                btn.textContent = 'Renovar';
            }

            document.getElementById('renewModal').classList.add('hidden');
        });

        // Toggle favoritos de veterinarios
        const AUTH_TOKEN = localStorage.getItem('auth_token');
        document.querySelectorAll('.favorite-toggle-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const placeId = this.dataset.placeId;
                const isFavorite = this.textContent === '❤️';
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
                        this.textContent = isFavorite ? '🤍' : '❤️';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error al actualizar favorito');
                });
            });
        });

        // Productos relacionados por tipo de servicio
        let currentPetId = null;
        let currentServiceType = null;
        let currentIsDeworming = false;

        document.querySelectorAll('.products-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                currentPetId = this.dataset.petId;
                currentServiceType = this.dataset.serviceType;
                currentIsDeworming = this.dataset.isDeworming === 'true';
                if (currentIsDeworming) {
                    loadDewormingCombo();
                } else {
                    loadRelatedProducts();
                }
            });
        });

        async function loadDewormingCombo() {
            const url = `/api/v1/products/deworming-combo/${currentPetId}`;

            try {
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${AUTH_TOKEN}`,
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) throw new Error('Error al cargar combo');

                const data = await response.json();
                const pet = data.pet;
                const items = data.combination;

                document.getElementById('productsTitle').textContent = `💊 Combo antiparasitario para ${pet.name}`;
                document.getElementById('productsSubtitle').textContent =
                    `Peso: ${pet.weight_kg} kg — Dosis total cubierta: ${data.total_dosage_kg} kg`;

                const container = document.getElementById('productsContainer');
                container.innerHTML = '';
                container.className = 'space-y-3 max-h-96 overflow-y-auto mb-6';

                if (items.length === 0) {
                    container.innerHTML = '<p class="text-center text-gray-500 py-8">No hay pastillas antiparasitarias disponibles para el peso de tu mascota.<br>Visita el marketplace para más opciones.</p>';
                } else {
                    // Cabecera explicativa
                    const intro = document.createElement('div');
                    intro.className = 'bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800';
                    intro.innerHTML = data.covered
                        ? `✅ Combinación óptima para cubrir los <strong>${pet.weight_kg} kg</strong> de ${pet.name}. Cada pastilla cubre una fracción del peso total.`
                        : `⚠️ No se encontraron suficientes pastillas para cubrir los ${pet.weight_kg} kg de ${pet.name}. Te mostramos lo disponible.`;
                    container.appendChild(intro);

                    let totalPrice = 0;
                    items.forEach(item => {
                        const product = item.product;
                        totalPrice += item.subtotal;
                        const row = document.createElement('div');
                        row.className = 'flex items-center gap-4 border rounded-lg p-3 hover:bg-gray-50';
                        row.innerHTML = `
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-2xl flex-shrink-0">💊</div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">${product.name}</p>
                                <p class="text-xs text-gray-500">Cubre <strong>${product.dosage_kg} kg</strong> de peso corporal por pastilla</p>
                            </div>
                            <div class="text-center flex-shrink-0">
                                <span class="block text-xl font-bold text-green-700">×${item.quantity}</span>
                                <span class="text-xs text-gray-500">uds.</span>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="block font-bold text-blue-600">${parseFloat(item.subtotal).toFixed(2)}€</span>
                                <a href="/marketplace/${product.id}" class="text-xs text-blue-500 hover:underline">Ver</a>
                            </div>
                        `;
                        container.appendChild(row);
                    });

                    // Línea de total
                    const total = document.createElement('div');
                    total.className = 'flex justify-between items-center border-t pt-3 font-semibold text-gray-900';
                    total.innerHTML = `<span>Total estimado</span><span class="text-blue-700 text-lg">${totalPrice.toFixed(2)}€</span>`;
                    container.appendChild(total);
                }

                document.getElementById('productsModal').classList.remove('hidden');
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar el combo de pastillas');
            }
        }

        async function loadRelatedProducts() {
            const url = `/api/v1/products/by-service/${currentServiceType}/${currentPetId}?per_page=6`;

            try {
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${AUTH_TOKEN}`,
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) throw new Error('Error al cargar productos');

                const data = await response.json();
                const service = data.service;
                const products = data.data;

                document.getElementById('productsTitle').textContent = `${service.emoji} ${service.label}`;
                document.getElementById('productsSubtitle').textContent = `Productos recomendados para ${data.pet.name}`;

                const container = document.getElementById('productsContainer');
                container.innerHTML = '';
                container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto mb-6';

                if (products.length === 0) {
                    container.innerHTML = '<p class="col-span-full text-center text-gray-500 py-8">No hay productos disponibles en este momento.</p>';
                } else {
                    products.forEach(product => {
                        const card = document.createElement('div');
                        card.className = 'border rounded-lg overflow-hidden hover:shadow-lg transition';
                        card.innerHTML = `
                            <div class="bg-gray-200 h-32 flex items-center justify-center">
                                ${product.photo ? `<img src="${product.photo}" alt="${product.name}" class="w-full h-full object-cover">` : '<span class="text-4xl">📦</span>'}
                            </div>
                            <div class="p-3">
                                <h4 class="font-bold text-gray-900 text-sm line-clamp-2">${product.name}</h4>
                                <p class="text-xs text-gray-600 mt-1">${product.category}</p>
                                <div class="flex items-center justify-between mt-3">
                                    <span class="text-lg font-bold text-blue-600">${parseFloat(product.price).toFixed(2)}€</span>
                                    <span class="text-xs bg-gray-100 px-2 py-1 rounded">${product.stock} en stock</span>
                                </div>
                                <a href="/marketplace/${product.id}" class="block mt-2 text-center text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                    Ver detalle
                                </a>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                }

                document.getElementById('productsModal').classList.remove('hidden');
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar productos');
            }
        }

        // Cerrar modal de productos
        document.getElementById('closeProducts').addEventListener('click', () => {
            document.getElementById('productsModal').classList.add('hidden');
        });

        document.getElementById('visitMarketplace').addEventListener('click', () => {
            window.location.href = '/marketplace?recommended_for=' + currentPetId;
        });

        // Cerrar modal al hacer click fuera
        document.getElementById('productsModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('productsModal')) {
                document.getElementById('productsModal').classList.add('hidden');
            }
        });
    </script>
@endsection
