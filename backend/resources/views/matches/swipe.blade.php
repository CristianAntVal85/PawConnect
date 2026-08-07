@extends('layouts.app')

@section('title', 'Swipe | PawConnect')
@section('page-title', 'Swipe')
@section('page-subtitle', 'Descubre mascotas compatibles')

@section('content')
<div class="mx-auto flex max-w-5xl flex-col gap-6" x-data="swipePage()">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-600">Match social</p>
                <h2 class="brand-font text-2xl font-semibold text-slate-900">Encuentra a tu próximo compañero</h2>
            </div>
            <div class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700" x-text="statusText"></div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
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
                            </div>

                            <div class="mt-4 space-y-3">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700" x-text="currentPet.size || '—'"></span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700" x-text="currentPet.age ? currentPet.age + ' años' : 'Edad no disponible'"></span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700" x-text="currentPet.personality || 'Sociable'" ></span>
                                </div>
                                <p class="text-sm leading-6 text-slate-600" x-text="currentPet.personality || 'Una mascota muy sociable y alegre que encaja con tu estilo de vida.'"></p>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="loading">
                    <div class="flex h-full items-center justify-center text-slate-500">Cargando candidatos…</div>
                </template>

                <template x-if="!loading && !currentPet">
                    <div class="flex h-full items-center justify-center px-6 text-center text-slate-500">No hay más candidatos por ahora. Vuelve más tarde.</div>
                </template>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <h3 class="brand-font text-xl font-semibold text-slate-900">Acciones</h3>
                <p class="mt-2 text-sm text-slate-600">Pulsa Like si te interesa o Pass si prefieres seguir adelante.</p>

                <div class="mt-6 flex gap-3">
                    <button type="button" @click="handleDecision('pass')" class="flex-1 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">✗ Pass</button>
                    <button type="button" @click="handleDecision('like')" class="flex-1 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">♥ Like</button>
                </div>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-900">Consejo</p>
                    <p class="mt-2">Los candidatos se ordenan por compatibilidad de tamaño, energía y distancia.</p>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
function swipePage() {
    return {
        candidates: [],
        currentPet: null,
        loading: true,
        statusText: 'Preparando candidatos',
        cardClass: '',
        async init() {
            await this.loadCandidates();
        },
        async loadCandidates() {
            this.loading = true;
            this.statusText = 'Preparando candidatos';
            try {
                const response = await window.API.apiGet('/matches/candidates');
                this.candidates = response.data || [];
                this.currentPet = this.candidates[0] || null;
                this.statusText = this.currentPet ? '1 candidato disponible' : 'No hay más candidatos';
            } catch (error) {
                this.statusText = 'No se pudieron cargar los candidatos';
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
                this.statusText = this.currentPet ? 'Siguiente candidato' : 'No hay más candidatos';
                this.cardClass = '';
                if (response.data?.match) {
                    this.statusText = '¡Tienes un match!';
                }
            } catch (error) {
                this.cardClass = '';
                this.statusText = 'No se pudo registrar la decisión';
                console.error(error);
            }
        }
    };
}
</script>
@endpush
