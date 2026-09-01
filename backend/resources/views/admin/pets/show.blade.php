@extends('layouts.admin')

@section('title', 'Mascota: ' . $pet->name . ' | Admin')
@section('page-title', $pet->name)

@section('content')
<div class="space-y-6">
    <!-- Información General -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Perfil de Mascota -->
        <div class="lg:col-span-1 rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
            <div class="text-center">
                @if($pet->photos->isNotEmpty())
                    <img
                        src="{{ asset('storage/' . $pet->photos->first()->path) }}"
                        alt="{{ $pet->name }}"
                        class="w-32 h-32 rounded-lg object-cover mx-auto mb-4"
                    >
                @else
                    <div class="w-32 h-32 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center mx-auto mb-4">
                        <span class="text-6xl font-semibold text-white">{{ substr($pet->name, 0, 1) }}</span>
                    </div>
                @endif

                <h2 class="text-2xl font-bold text-slate-100 mb-1">{{ $pet->name }}</h2>
                <p class="text-slate-400 mb-4">{{ ucfirst($pet->species) }}</p>

                <div class="space-y-2 mb-6 text-left border-t border-slate-700 pt-4">
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Especie</p>
                        <p class="text-sm text-slate-200">{{ ucfirst($pet->species) }}</p>
                    </div>

                    @if($pet->breed)
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Raza</p>
                            <p class="text-sm text-slate-200">{{ $pet->breed }}</p>
                        </div>
                    @endif

                    @if($pet->age)
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Edad</p>
                            <p class="text-sm text-slate-200">{{ $pet->age }} año{{ $pet->age !== 1 ? 's' : '' }}</p>
                        </div>
                    @endif

                    @if($pet->weight)
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Peso</p>
                            <p class="text-sm text-slate-200">{{ $pet->weight }} kg</p>
                        </div>
                    @endif

                    @if($pet->size)
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Tamaño</p>
                            <p class="text-sm text-slate-200">{{ ucfirst($pet->size) }}</p>
                        </div>
                    @endif

                    @if($pet->energy_level)
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Nivel de Energía</p>
                            <p class="text-sm text-slate-200">{{ ucfirst($pet->energy_level) }}</p>
                        </div>
                    @endif

                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Disponible para Paseos</p>
                        <p class="text-sm">
                            @if($pet->available_for_walks)
                                <span class="px-2.5 py-1 rounded text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    ✓ Sí
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded text-xs font-semibold bg-slate-700 text-slate-400">
                                    ✗ No
                                </span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Registrada</p>
                        <p class="text-sm text-slate-200">{{ $pet->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <a
                    href="{{ route('admin.users.show', $pet->user->id) }}"
                    class="block px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-lg transition text-center"
                >
                    Ver Dueño
                </a>
            </div>
        </div>

        <!-- Información Completa -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Dueño -->
            <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
                <h3 class="text-lg font-semibold text-slate-100 mb-4">Dueño</h3>
                <div class="flex items-center gap-4">
                    @if($pet->user->photo)
                        <img
                            src="{{ asset('storage/' . $pet->user->photo) }}"
                            alt="{{ $pet->user->name }}"
                            class="w-12 h-12 rounded-full object-cover"
                        >
                    @else
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center">
                            <span class="text-sm font-semibold text-white">{{ substr($pet->user->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-slate-100">{{ $pet->user->name }}</p>
                        <p class="text-sm text-slate-400">{{ $pet->user->email }}</p>
                        <p class="text-xs text-slate-500 mt-1">Registrado: {{ $pet->user->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Personalidad -->
            @if($pet->personality)
                <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
                    <h3 class="text-lg font-semibold text-slate-100 mb-4">Personalidad</h3>
                    <p class="text-slate-300">{{ $pet->personality }}</p>
                </div>
            @endif

            <!-- Fotos -->
            @if($pet->photos->isNotEmpty())
                <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
                    <h3 class="text-lg font-semibold text-slate-100 mb-4">Fotos ({{ $pet->photos->count() }})</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach($pet->photos as $photo)
                            <img
                                src="{{ asset('storage/' . $photo->path) }}"
                                alt="{{ $pet->name }}"
                                class="rounded-lg object-cover w-full h-32 hover:opacity-80 transition cursor-pointer"
                                onclick="window.open('{{ asset('storage/' . $photo->path) }}', '_blank')"
                            >
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Historiales y Actividad -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Historiales Sanitarios -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
            <h3 class="text-lg font-semibold text-slate-100 mb-4">Registros Sanitarios ({{ $pet->healthRecords->count() }})</h3>

            @if($pet->healthRecords->isNotEmpty())
                <div class="space-y-3">
                    @foreach($pet->healthRecords->take(5) as $record)
                        <div class="rounded p-3 bg-slate-800/50 border border-slate-700">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-slate-200">{{ ucfirst($record->type) }}</span>
                                @if($record->next_due_date)
                                    @if($record->next_due_date->isPast())
                                        <span class="text-xs px-2 py-1 bg-red-500/20 text-red-300 rounded">Vencido</span>
                                    @elseif($record->next_due_date->diffInDays(now()) <= 7)
                                        <span class="text-xs px-2 py-1 bg-amber-500/20 text-amber-300 rounded">Próximo</span>
                                    @else
                                        <span class="text-xs px-2 py-1 bg-emerald-500/20 text-emerald-300 rounded">Activo</span>
                                    @endif
                                @endif
                            </div>
                            @if($record->last_date)
                                <p class="text-xs text-slate-400">Última: {{ $record->last_date->format('d/m/Y') }}</p>
                            @endif
                            @if($record->next_due_date)
                                <p class="text-xs text-slate-400">Próxima: {{ $record->next_due_date->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($pet->healthRecords->count() > 5)
                    <p class="text-xs text-slate-400 mt-3">
                        Mostrando 5 de {{ $pet->healthRecords->count() }} registros
                    </p>
                @endif
            @else
                <p class="text-slate-400">No tiene registros sanitarios</p>
            @endif
        </div>

        <!-- Actividad -->
        <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-6">
            <h3 class="text-lg font-semibold text-slate-100 mb-4">Actividad</h3>

            <div class="space-y-4 text-sm">
                <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                    <span class="text-slate-300">Matches realizados</span>
                    <span class="font-semibold text-slate-100">
                        {{ $pet->matchesAsPetA->count() + $pet->matchesAsPetB->count() }}
                    </span>
                </div>

                <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                    <span class="text-slate-300">Paseos participados</span>
                    <span class="font-semibold text-slate-100">{{ $pet->walks->count() }}</span>
                </div>

                <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                    <span class="text-slate-300">Pedidos realizados</span>
                    <span class="font-semibold text-slate-100">{{ $pet->orders->count() }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-300">Registros sanitarios</span>
                    <span class="font-semibold text-slate-100">{{ $pet->healthRecords->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Volver -->
    <div>
        <a href="{{ route('admin.pets.index') }}" class="inline-flex items-center gap-2 text-sky-400 hover:text-sky-300 font-medium transition">
            ← Volver a mascotas
        </a>
    </div>
</div>
@endsection
