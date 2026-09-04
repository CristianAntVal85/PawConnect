@extends('layouts.app')

@section('title', 'Swipe | PawConnect')
@section('page-title', 'Swipe')
@section('page-subtitle', 'Descubre mascotas compatibles')

@section('content')
<form method="GET" action="{{ route('web.matches.swipe') }}"
      class="mb-6 rounded-lg border border-gray-200 bg-white px-4 py-4 shadow-sm">

    <h3 class="mb-4 text-sm font-semibold text-gray-800">
        Filtros de búsqueda
    </h3>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        <x-breed-selector />




        {{-- Filtro de energía --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-700">
                Energía
            </label>
            <select name="energy"
                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800
                           shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                <option value="all">Cualquiera</option>
                <option value="low">Baja</option>
                <option value="medium">Media</option>
                <option value="high">Alta</option>
            </select>
        </div>

        {{-- Filtro de edad --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-700">
                Edad
            </label>
            <select name="age" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800
                        shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                <option value="all">Todas</option>
                <option value="young">Joven (0-2 años)</option>
                <option value="adult">Adulto (2-7 años)</option>
                <option value="senior">Senior (+7 años)</option>
            </select>

        </div>

    </div>

    <div class="mt-4 flex justify-end">
        <button type="submit"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium
                       text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-sky-500">
            Aplicar filtros
        </button>
    </div>
</form>


<div class="mx-auto flex max-w-5xl flex-col gap-6" x-data="swipePage()">

    {{-- ── Banner de ubicación de búsqueda ─────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
        <div class="flex items-center gap-2 text-sm text-slate-600 min-w-0">
            <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
            <span class="truncate font-medium text-slate-800" x-text="locationLabel"></span>
            <template x-if="radiusKm">
                <span class="shrink-0 text-slate-400">· radio <span x-text="radiusKm"></span> km</span>
            </template>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            {{-- Selector de radio --}}
            <select x-model.number="radiusKm" @change="loadCandidates()"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                <option value="5">5 km</option>
                <option value="10">10 km</option>
                <option value="25" selected>25 km</option>
                <option value="50">50 km</option>
                <option value="100">100 km</option>
            </select>

            {{-- Botón GPS --}}
            <button type="button" @click="useGPS()"
                    :disabled="gpsLoading"
                    class="flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 disabled:opacity-50">
                <svg class="h-4 w-4" :class="gpsLoading ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <span x-text="gpsLoading ? 'Localizando…' : 'Mi ubicación'"></span>
            </button>

            {{-- Picker de ubicaciones guardadas --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open; if(open) $root.loadSavedLocations()"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                    Guardadas
                </button>

                <template x-if="open">
                    <div class="absolute right-0 z-20 mt-1 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-lg">
                        <template x-if="savedLocations.length === 0">
                            <p class="px-3 py-2 text-sm text-slate-400">Sin ubicaciones guardadas</p>
                        </template>
                        <template x-for="loc in savedLocations" :key="loc.id">
                            <button type="button"
                                    @click="selectSavedLocation(loc); open = false"
                                    class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700"
                                    :class="activeSavedLocationId === loc.id ? 'bg-emerald-50 text-emerald-700 font-medium' : ''">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                                <span x-text="loc.name"></span>
                                <template x-if="loc.is_default">
                                    <span class="ml-auto rounded-full bg-emerald-100 px-1.5 py-0.5 text-xs text-emerald-700">predeterminada</span>
                                </template>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ── Panel principal ──────────────────────────────────────────────── --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-600">Match social</p>
                <h2 class="brand-font text-2xl font-semibold text-slate-900">Encuentra a tu próximo compañero</h2>
            </div>
            <div class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700" x-text="statusText"></div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">

            {{-- Tarjeta candidato --}}
            <div class="relative min-h-[440px] overflow-hidden rounded-3xl border border-slate-200 bg-slate-50">
                <template x-if="!loading && currentPet">
                    <div class="absolute inset-0 flex items-center justify-center p-4">
                        <div class="card-swipe w-full max-w-md rounded-[2rem] border border-slate-200 bg-white p-4 shadow-xl transition-transform duration-200" :class="cardClass">
                            <div class="relative h-72 overflow-hidden rounded-[1.5rem] bg-slate-100">
                                <img :src="currentPet.photo || 'https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=1000&q=80'" alt="Mascota" class="h-full w-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-slate-900/10 to-transparent"></div>
                                <div class="absolute bottom-4 left-4 right-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-2xl font-semibold text-white" x-text="currentPet.name"></h3>
                                            <p class="text-sm text-slate-100" x-text="currentPet.breed || 'Sin raza registrada'"></p>
                                        </div>
                                        <span class="rounded-full bg-white/20 px-3 py-1 text-sm font-medium text-white" x-text="currentPet.energy_level || '—'"></span>
                                    </div>
                                </div>
                                {{-- Badge de distancia --}}
                                <template x-if="currentPet.distance_km !== undefined">
                                    <div class="absolute right-3 top-3 flex items-center gap-1 rounded-full bg-black/50 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                        </svg>
                                        <span x-text="currentPet.distance_km + ' km'"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4 space-y-3">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700" x-text="currentPet.size || '—'"></span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700" x-text="currentPet.age ? currentPet.age + ' años' : 'Edad no disponible'"></span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700" x-text="currentPet.personality || 'Sociable'"></span>
                                </div>
                                <p class="text-sm leading-6 text-slate-600" x-text="currentPet.personality || 'Una mascota muy sociable y alegre que encaja con tu estilo de vida.'"></p>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="loading">
                    <div class="flex h-full items-center justify-center text-slate-500">Cargando candidatos…</div>
                </template>

                <template x-if="!loading && !currentPet && locationError">
                    <div class="flex h-full flex-col items-center justify-center gap-4 px-8 text-center">
                        <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        <p class="font-medium text-slate-600">Necesitamos tu ubicación para mostrarte candidatos cercanos.</p>
                        <button type="button" @click="useGPS()" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Usar mi ubicación actual
                        </button>
                    </div>
                </template>

                <template x-if="!loading && !currentPet && !locationError">
                    <div class="flex h-full items-center justify-center px-6 text-center text-slate-500">
                        No hay más candidatos cerca. Prueba a ampliar el radio o cambia tu ubicación.
                    </div>
                </template>
            </div>

            {{-- Panel lateral --}}
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <h3 class="brand-font text-xl font-semibold text-slate-900">Acciones</h3>
                <p class="mt-2 text-sm text-slate-600">Pulsa Like si te interesa o Pass si prefieres seguir adelante.</p>

                <div class="mt-6 flex gap-3">
                    <button type="button" @click="handleDecision('pass')"
                            :disabled="!currentPet || loading"
                            class="flex-1 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 disabled:opacity-40">
                        ✗ Pass
                    </button>
                    <button type="button" @click="handleDecision('like')"
                            :disabled="!currentPet || loading"
                            class="flex-1 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">
                        ♥ Like
                    </button>
                </div>

                <div class="mt-6 space-y-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-600">
                        <p class="font-semibold text-slate-900">Ordenación</p>
                        <p class="mt-1">Los candidatos se ordenan por distancia desde tu ubicación. La compatibilidad de tamaño y energía actúa como criterio secundario.</p>
                    </div>

                    <template x-if="currentPet && currentPet.distance_km !== undefined">
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm">
                            <p class="font-semibold text-emerald-800">Candidato actual</p>
                            <p class="mt-1 text-emerald-700">
                                <span class="font-bold" x-text="currentPet.distance_km"></span> km desde tu ubicación de búsqueda
                            </p>
                            <p class="mt-1 text-emerald-600" x-text="candidates.length + ' candidato(s) disponibles'"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function swipePage() {
    return {
        candidates: [],
        currentPet: null,
        loading: true,
        locationError: false,
        statusText: 'Preparando candidatos',
        cardClass: '',
        // Ubicación de búsqueda activa
        searchLat: null,
        searchLng: null,
        locationLabel: 'Detectando ubicación…',
        radiusKm: 25,
        gpsLoading: false,
        // Ubicaciones guardadas
        savedLocations: [],
        activeSavedLocationId: null,

        async init() {
            // Solicitar geolocalización primero
            await this.requestLocation();
            // Luego cargar candidatos
            await this.loadCandidates();
            this.requestGPS();
        },
        async requestLocation() {
            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    console.warn('Geolocalización no disponible');
                    this.statusText = 'La geolocalización no está disponible';
                    resolve();
                    return;
                }

                this.statusText = 'Solicitando permiso de ubicación...';

                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        try {
                            await fetch('/update-location', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude
                                })
                            });
                            console.log('Ubicación guardada:', position.coords.latitude, position.coords.longitude);
                        } catch (error) {
                            console.error('Error guardando ubicación:', error);
                        }
                        resolve();
                    },
                    (error) => {
                        console.warn('Error obteniendo geolocalización:', error);
                        this.statusText = 'No se pudo obtener la ubicación';
                        resolve();
                    },
                    {
                        enableHighAccuracy: false,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        },
        async loadCandidates() {
            this.loading = true;
            this.locationError = false;
            this.statusText = 'Buscando candidatos…';
            try {
                let endpoint = `/matches/candidates?radius=${this.radiusKm}`;
                if (this.searchLat !== null && this.searchLng !== null) {
                    endpoint += `&lat=${this.searchLat}&lng=${this.searchLng}`;
                }
                const response = await window.API.apiGet(endpoint);
                this.candidates = response.data || [];
                this.currentPet = this.candidates[0] || null;
                const count = this.candidates.length;
                this.statusText = count > 0
                    ? `${count} candidato${count !== 1 ? 's' : ''} cerca`
                    : 'Sin candidatos cercanos';

                // Actualizar etiqueta si la respuesta fue con ubicación por defecto del servidor
                if (this.searchLat === null && this.locationLabel === 'Detectando ubicación…') {
                    this.locationLabel = 'Ubicación predeterminada guardada';
                }
            } catch (error) {
                if (error.status === 422) {
                    this.locationError = true;
                    this.statusText = 'Ubicación requerida';
                } else {
                    this.statusText = 'No se pudieron cargar los candidatos';
                }
                this.candidates = [];
                this.currentPet = null;
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async handleDecision(action) {
            if (!this.currentPet) return;

            this.cardClass = action === 'like' ? 'translate-x-full opacity-0' : '-translate-x-full opacity-0';
            try {
                const response = await window.API.apiPost(`/matches/${action}/${this.currentPet.id}`);
                this.candidates.shift();
                this.currentPet = this.candidates[0] || null;
                const count = this.candidates.length;
                this.statusText = count > 0
                    ? `${count} candidato${count !== 1 ? 's' : ''} cerca`
                    : 'Sin candidatos cercanos';
                this.cardClass = '';
                if (response.data?.match) {
                    this.statusText = '¡Tienes un match!';
                }
            } catch (error) {
                this.cardClass = '';
                this.statusText = 'No se pudo registrar la decisión';
                console.error(error);
            }
        },
    };
}
</script>
@endpush
