@extends('layouts.app')

@section('title', 'Detalle de mascota | PawConnect')
@section('page-title', 'Ficha de mascota')
@section('page-subtitle', 'Informacion general y estado sanitario')

@section('content')
    @php
        $pet = $pet ?? (object) [
            'id' => 1,
            'name' => 'Luna',
            'species' => 'dog',
            'breed' => 'Labrador',
            'age' => 3,
            'weight' => 24.5,
            'size' => 'large',
            'energy_level' => 'high',
            'personality' => 'Juguetona, sociable y amante del agua.',
            'available_for_walks' => true,
        ];

        $healthRecords = $healthRecords ?? collect([
            (object) ['type' => 'vaccine', 'last_date' => now()->subMonths(10), 'next_due_date' => now()->addDays(5), 'notes' => 'Refuerzo anual'],
            (object) ['type' => 'checkup', 'last_date' => now()->subMonths(2), 'next_due_date' => now()->addMonths(4), 'notes' => 'Control general'],
        ]);
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <x-pet-card :pet="$pet" />
        </div>

        <div class="space-y-5 lg:col-span-2">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="brand-font text-lg font-semibold text-slate-900">Datos principales</h2>
                <dl class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div><dt class="text-xs uppercase text-slate-500">Nombre</dt><dd class="font-medium text-slate-900">{{ $pet->name }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Raza</dt><dd class="font-medium text-slate-900">{{ $pet->breed }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Edad</dt><dd class="font-medium text-slate-900">{{ $pet->age }} anos</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Peso</dt><dd class="font-medium text-slate-900">{{ $pet->weight }} kg</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Tamano</dt><dd class="font-medium text-slate-900">{{ ucfirst($pet->size) }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Energia</dt><dd class="font-medium text-slate-900">{{ ucfirst($pet->energy_level) }}</dd></div>
                </dl>
                <p class="mt-3 text-sm text-slate-600">{{ $pet->personality }}</p>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="brand-font text-lg font-semibold text-slate-900">Estado sanitario</h2>
                    <a href="{{ route('web.pets.health.create', $pet->id) }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">Agregar registro</a>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($healthRecords as $record)
                        <x-health-badge :record="$record" />
                    @endforeach
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex gap-2">
                    <a href="{{ url('/pets/' . $pet->id . '/edit') }}" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Editar</a>
                    <a href="{{ url('/pets') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Volver al listado</a>
                </div>
                <form action="{{ route('web.pets.destroy', $pet->id) }}"
                    method="POST"
                    onsubmit="return confirm('¿Seguro que deseas eliminar esta mascota? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')

                    <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition">
                        Eliminar mascota
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
