@extends('layouts.admin')

@section('title', 'Gestión de Mascotas | Admin')
@section('page-title', 'Gestión de Mascotas')

@section('content')
<div class="space-y-6">
    <!-- Filtros y Búsqueda -->
    <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur p-4">
        <form method="GET" class="space-y-4">
            <!-- Búsqueda Principal -->
            <div class="flex-1">
                <label class="block text-sm font-medium text-slate-300 mb-2">Buscar mascota</label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Nombre, especie, raza, dueño..."
                    class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                >
            </div>

            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Especie</label>
                    <select name="species" class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-sky-500 focus:outline-none">
                        <option value="all">Todas</option>
                        @foreach($species as $sp)
                            <option value="{{ $sp }}" {{ request('species') === $sp ? 'selected' : '' }}>
                                {{ ucfirst($sp) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Disponible para Paseos</label>
                    <select name="available" class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-sky-500 focus:outline-none">
                        <option value="all">Todos</option>
                        <option value="yes" {{ request('available') === 'yes' ? 'selected' : '' }}>Disponibles</option>
                        <option value="no" {{ request('available') === 'no' ? 'selected' : '' }}>No Disponibles</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Tamaño</label>
                    <select name="size" class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-sky-500 focus:outline-none">
                        <option value="all">Todos</option>
                        @foreach($sizes as $sz)
                            <option value="{{ $sz }}" {{ request('size') === $sz ? 'selected' : '' }}>
                                {{ ucfirst($sz) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button 
                        type="submit" 
                        class="flex-1 px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-lg transition"
                    >
                        Filtrar
                    </button>

                    @if(request()->filled('search') || request()->filled('species') || request()->filled('available') || request()->filled('size'))
                        <a 
                            href="{{ route('admin.pets.index') }}"
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium rounded-lg transition border border-slate-700"
                        >
                            Limpiar
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de Mascotas -->
    <div class="rounded-lg border border-slate-800 bg-slate-900/80 backdrop-blur overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700">
            <h3 class="text-base font-semibold text-slate-100">
                Total: {{ $pets->total() }} mascota{{ $pets->total() !== 1 ? 's' : '' }}
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Mascota</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Especie</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Raza</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Dueño</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Edad</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Tamaño</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Paseos</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($pets as $pet)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($pet->photos->isNotEmpty())
                                        <img 
                                            src="{{ asset('storage/' . $pet->photos->first()->path) }}" 
                                            alt="{{ $pet->name }}" 
                                            class="w-8 h-8 rounded object-cover"
                                        >
                                    @else
                                        <div class="w-8 h-8 rounded bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center">
                                            <span class="text-xs font-semibold text-white">{{ substr($pet->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <span class="font-medium text-slate-100">{{ $pet->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-300">{{ ucfirst($pet->species) }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $pet->breed ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <a 
                                    href="{{ route('admin.users.show', $pet->user->id) }}"
                                    class="text-sky-400 hover:text-sky-300 font-medium"
                                >
                                    {{ $pet->user->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-300">
                                @if($pet->age)
                                    {{ $pet->age }} año{{ $pet->age !== 1 ? 's' : '' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($pet->size)
                                    <span class="px-2.5 py-1 rounded text-xs font-semibold
                                        @if($pet->size === 'small')
                                            bg-emerald-500/20 text-emerald-300
                                        @elseif($pet->size === 'medium')
                                            bg-blue-500/20 text-blue-300
                                        @elseif($pet->size === 'large')
                                            bg-orange-500/20 text-orange-300
                                        @else
                                            bg-slate-700 text-slate-300
                                        @endif
                                    ">
                                        {{ ucfirst($pet->size) }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($pet->available_for_walks)
                                    <span class="px-2.5 py-1 rounded text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        ✓ Sí
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded text-xs font-semibold bg-slate-700 text-slate-400">
                                        ✗ No
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a 
                                    href="{{ route('admin.pets.show', $pet->id) }}"
                                    class="px-3 py-1 bg-sky-600 hover:bg-sky-700 text-white text-xs font-medium rounded transition"
                                >
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                                No hay mascotas disponibles
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    @if($pets->hasPages())
        <div class="flex justify-center">
            {{ $pets->links() }}
        </div>
    @endif
</div>
