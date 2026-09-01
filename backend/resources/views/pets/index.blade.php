@extends('layouts.app')

@section('title', 'Mascotas | PawConnect')
@section('page-title', 'Mascotas')
@section('page-subtitle', 'Gestiona el perfil de tus companeros')

@section('content')
    @php
        $pets = $pets ?? collect();
    @endphp

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <form action="{{ route('web.pets.index') }}" method="GET" class="flex w-full flex-wrap gap-2 sm:w-auto">

            <input type="text" name="search" placeholder="Buscar por nombre o raza" class="min-w-[240px] flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">

            <select name="species" class="rounded-lg border border-slate-300 px-3 py-2 text-sm min-w-[160px]">
                <option value="">Todas las especies</option>
                <option value="dog">Perro</option>
                <option value="cat">Gato</option>
                <option value="other">Otra</option>
            </select>


            <button type="submit" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                Filtrar
            </button>
        </form>

        <a href="{{ url('/pets/create') }}" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600">
            Nueva mascota
        </a>
    </div>

    @if(session('success'))
        <x-alert type="success" class="mb-4" dismissible>{{ session('success') }}</x-alert>
    @endif

    @if($pets->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
            <h3 class="brand-font text-xl font-semibold text-slate-900">Aun no tienes mascotas registradas</h3>
            <p class="mt-2 text-sm text-slate-600">Comienza creando el perfil de tu primera mascota para acceder a match, salud y paseos.</p>
            <a href="{{ url('/pets/create') }}" class="mt-4 inline-flex rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Crear primera mascota</a>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($pets as $pet)
                <a href="{{ url('/pets/' . $pet->id) }}" class="block">
                    <x-pet-card :pet="$pet" />
                </a>
            @endforeach
        </div>
    @endif
@endsection
