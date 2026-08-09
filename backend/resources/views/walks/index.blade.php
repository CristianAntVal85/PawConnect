@extends('layouts.app')

@section('title', 'Paseos | PawConnect')
@section('page-title', 'Paseos caninos')
@section('page-subtitle', 'Mapa social con geolocalización y plazas disponibles')

@section('content')
    @php
        $userId = auth()->id();
        $markers = $walks->map(function ($walk) use ($eligiblePets, $userId) {
            $joinedPet = $walk->pets->firstWhere('user_id', $userId);
            $scheduledLabel = optional($walk->scheduled_at)?->translatedFormat('d M Y, H:i') ?? 'Sin fecha';
            $spotsLeft = max(0, $walk->max_pets - $walk->pets_count);

            if ($joinedPet) {
                $actionHtml = sprintf(
                    '<form method="POST" action="%s" class="mt-3 space-y-2">%s%s<input type="hidden" name="pet_id" value="%d"><button type="submit" class="w-full rounded-lg bg-red-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-600">Abandonar</button></form>',
                    route('web.walks.leave', $walk),
                    csrf_field(),
                    method_field('DELETE'),
                    $joinedPet->id,
                );
            } elseif ($eligiblePets->isEmpty()) {
                $actionHtml = '<p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">Necesitas una mascota canina disponible para paseos.</p>';
            } elseif ($spotsLeft === 0) {
                $actionHtml = '<p class="mt-3 rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-600">Plazas agotadas por ahora.</p>';
            } else {
                $options = $eligiblePets->map(fn ($pet) => sprintf('<option value="%d">%s</option>', $pet->id, e($pet->name)))->implode('');
                $actionHtml = sprintf(
                    '<form method="POST" action="%s" class="mt-3 space-y-2">%s<select name="pet_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700">%s</select><button type="submit" class="w-full rounded-lg bg-emerald-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600">Unirse</button></form>',
                    route('web.walks.join', $walk),
                    csrf_field(),
                    $options,
                );
            }

            $popup = sprintf(
                '<div class="min-w-[220px] text-slate-900"><p class="text-sm font-semibold">%s</p><p class="mt-1 text-xs text-slate-600">%s</p><p class="mt-2 text-xs text-slate-500">%s · %d/%d mascotas</p>%s</div>',
                e($walk->title),
                e($walk->description ?: 'Paseo grupal cerca de tu zona'),
                e($scheduledLabel),
                $walk->pets_count,
                $walk->max_pets,
                $actionHtml,
            );

            return [
                'lat' => (float) $walk->lat,
                'lng' => (float) $walk->lng,
                'tooltip' => $walk->title,
                'popup' => $popup,
            ];
        })->values()->all();
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <x-alert type="success" title="Acción completada">
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('error'))
            <x-alert type="warning" title="No se pudo completar la acción">
                {{ session('error') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert type="error" title="Revisa los datos enviados">
                {{ $errors->first() }}
            </x-alert>
        @endif

        <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="brand-font text-xl font-semibold text-slate-900">Mapa de paseos</h2>
                        <p class="text-sm text-slate-500">Marcadores con popup de acceso rápido y estado de plazas.</p>
                    </div>
                    <p id="walks-location-status" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                        Buscando tu ubicación para centrar el mapa...
                    </p>
                </div>

                <x-map
                    map-key="walks-map"
                    height="540px"
                    :center="$initialCenter"
                    :markers="$markers"
                    class="shadow-inner"
                />
            </article>

            <aside class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="brand-font text-lg font-semibold text-slate-900">Tus mascotas elegibles</h3>
                    <p class="mt-1 text-sm text-slate-500">Solo perros con disponibilidad activa pueden unirse.</p>

                    <div class="mt-4 space-y-2">
                        @forelse ($eligiblePets as $pet)
                            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">
                                {{ $pet->name }}
                            </div>
                        @empty
                            <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-800">
                                No tienes perros marcados como disponibles para paseos.
                            </p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="brand-font text-lg font-semibold text-slate-900">Paseos próximos</h3>
                    <p class="mt-1 text-sm text-slate-500">Puedes unirte desde aquí o desde el popup del mapa.</p>
                </div>
            </aside>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            @forelse ($walks as $walk)
                @php
                    $joinedPet = $walk->pets->firstWhere('user_id', $userId);
                    $spotsLeft = max(0, $walk->max_pets - $walk->pets_count);
                @endphp
                <article id="walk-{{ $walk->id }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="brand-font text-lg font-semibold text-slate-900">{{ $walk->title }}</h3>
                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $walk->pets_count }}/{{ $walk->max_pets }}</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">{{ $walk->description ?: 'Sin descripción adicional.' }}</p>
                            <div class="mt-3 grid gap-2 text-sm text-slate-500 sm:grid-cols-2">
                                <p>Organiza: {{ $walk->creator?->name ?? 'Comunidad PawConnect' }}</p>
                                <p>Fecha: {{ optional($walk->scheduled_at)?->translatedFormat('d M Y, H:i') ?? 'Sin fecha' }}</p>
                                <p>Ubicación: {{ number_format((float) $walk->lat, 4) }}, {{ number_format((float) $walk->lng, 4) }}</p>
                                <p>Plazas libres: {{ $spotsLeft }}</p>
                            </div>
                        </div>

                        <div class="w-full max-w-xs space-y-2">
                            @if ($joinedPet)
                                <form method="POST" action="{{ route('web.walks.leave', $walk) }}" class="space-y-2">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="pet_id" value="{{ $joinedPet->id }}">
                                    <button type="submit" class="w-full rounded-xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600">
                                        Abandonar con {{ $joinedPet->name }}
                                    </button>
                                </form>
                            @elseif ($eligiblePets->isNotEmpty() && $spotsLeft > 0)
                                <form method="POST" action="{{ route('web.walks.join', $walk) }}" class="space-y-2">
                                    @csrf
                                    <label for="pet_id_{{ $walk->id }}" class="text-sm font-medium text-slate-700">Selecciona mascota</label>
                                    <select id="pet_id_{{ $walk->id }}" name="pet_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                        @foreach ($eligiblePets as $pet)
                                            <option value="{{ $pet->id }}">{{ $pet->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="w-full rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                                        Unirse
                                    </button>
                                </form>
                            @elseif ($spotsLeft === 0)
                                <p class="rounded-xl bg-slate-100 px-4 py-3 text-sm text-slate-600">Este paseo ya está completo.</p>
                            @else
                                <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Necesitas al menos un perro disponible para participar.</p>
                            @endif
                        </div>
                    </div>

                    @if ($walk->pets->isNotEmpty())
                        <div class="mt-4 border-t border-slate-100 pt-4">
                            <p class="text-sm font-semibold text-slate-700">Participantes</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($walk->pets as $pet)
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                        {{ $pet->name }} · {{ $pet->breed ?: ucfirst($pet->species) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>
            @empty
                <article class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 shadow-sm lg:col-span-2">
                    Todavía no hay paseos con ubicación registrada. Crea uno desde la API o añade datos de prueba para verlo en el mapa.
                </article>
            @endforelse
        </section>
    </div>
@endsection

@push('scripts')
<script>
    window.addEventListener('leaflet-map:ready', function (event) {
        if (event.detail.mapKey !== 'walks-map') {
            return;
        }

        const map = event.detail.map;
        const status = document.getElementById('walks-location-status');

        if (!navigator.geolocation) {
            if (status) {
                status.textContent = 'Tu navegador no permite geolocalización.';
            }
            return;
        }

        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            L.circleMarker([lat, lng], {
                radius: 8,
                color: '#0f766e',
                fillColor: '#14b8a6',
                fillOpacity: 0.9,
                weight: 2,
            }).addTo(map).bindPopup('Tu ubicación actual');

            map.setView([lat, lng], Math.max(map.getZoom(), 13));

            if (status) {
                status.textContent = 'Mapa centrado en tu ubicación actual.';
            }
        }, function () {
            if (status) {
                status.textContent = 'No fue posible obtener tu ubicación.';
            }
        }, {
            enableHighAccuracy: true,
            timeout: 8000,
            maximumAge: 300000,
        });
    });
</script>
@endpush
