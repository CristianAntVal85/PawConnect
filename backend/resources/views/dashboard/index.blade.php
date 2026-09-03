@extends('layouts.app')

@section('title', 'Dashboard | PawConnect')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Resumen general de actividad')

@section('content')
    @php
        $pets = $pets ?? collect();
        if ($pets->isEmpty()) {
            $pets = collect([
                (object) ['name' => 'Luna', 'species' => 'dog', 'breed' => 'Labrador', 'age' => 3, 'size' => 'large', 'energy_level' => 'high', 'personality' => 'Activa y sociable', 'available_for_walks' => true],
                (object) ['name' => 'Nina', 'species' => 'cat', 'breed' => 'Europeo', 'age' => 2, 'size' => 'small', 'energy_level' => 'medium', 'personality' => 'Curiosa y tranquila', 'available_for_walks' => false],
            ]);
        }
    @endphp

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Mascotas registradas</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $pets->count() }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Registros sanitarios</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">12</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Paseos esta semana</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">4</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Notificaciones</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">3</p>
        </article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="brand-font text-xl font-semibold text-slate-900">Tus mascotas</h2>
                <a href="{{ url('/pets') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">Ver todas</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($pets->take(4) as $pet)
                    <a href="{{ url('/pets/' . $pet->id) }}"
                    class="block rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden">

                        @if($pet->photos->isNotEmpty())
                            <img src="{{ asset('storage/' . $pet->photos->first()->path) }}"
                                alt="{{ $pet->name }}"
                                class="w-full h-40 object-cover">
                        @else
                            <div class="w-full h-40 flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-4xl">
                                🐾
                            </div>
                        @endif

                        <div class="p-3">
                            <x-pet-card :pet="$pet" compact />
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <aside class="space-y-4">
            <x-alert type="warning" title="Recordatorio sanitario" dismissible>
                Luna tiene una vacuna programada en 5 dias. Revisa el historial sanitario para renovarla a tiempo.
            </x-alert>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="brand-font text-lg font-semibold text-slate-900">Accesos rapidos</h3>
                <div class="mt-3 space-y-2 text-sm">
                    <a href="{{ url('/pets/create') }}" class="block rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50">Registrar mascota</a>
                    <a href="{{ url('/profile/edit') }}" class="block rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50">Editar perfil</a>
                    <a href="{{ url('/pets') }}" class="block rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50">Gestionar mascotas</a>
                </div>
            </div>
        </aside>
    </section>
@endsection
