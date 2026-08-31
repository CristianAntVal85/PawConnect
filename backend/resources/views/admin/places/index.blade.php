@extends('layouts.admin')

@section('title', 'Gestión de Lugares')

@section('page-title', 'Admin')
@section('page-subtitle', 'Gestión de lugares')

@section('content')
<div class="max-w-7xl mx-auto py-4 text-slate-200">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-200">Lugares</h2>
        <span class="bg-slate-800 text-slate-200 px-3 py-1 rounded-full text-sm font-semibold border border-slate-700">
            Total: {{ $places->total() }}
        </span>
    </div>

    {{-- Filtros --}}
    <div class="bg-slate-900 rounded-lg border border-slate-800 p-4 mb-6">
        <form method="GET" action="{{ route('admin.places.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Búsqueda</label>
                <input type="text" name="search" placeholder="Nombre, ciudad..."
                       value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-slate-700 rounded-lg text-sm bg-slate-800 text-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Estado</label>
                <select name="status"
                        class="w-full px-3 py-2 border border-slate-700 rounded-lg text-sm bg-slate-800 text-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Todos</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pendiente</option>
                    <option value="approved" @selected(request('status') === 'approved')>Aprobado</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rechazado</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Ciudad</label>
                <input type="text" name="city" value="{{ request('city') }}"
                       class="w-full px-3 py-2 border border-slate-700 rounded-lg text-sm bg-slate-800 text-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold rounded-lg text-sm transition-colors border border-slate-700">
                    Filtrar
                </button>

                <a href="{{ route('admin.places.index') }}"
                   class="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-center text-slate-200 font-semibold rounded-lg text-sm transition-colors border border-slate-700">
                    Limpiar
                </a>

                <a href="{{ route('admin.places.export') }}"
                   class="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-center text-slate-200 font-semibold rounded-lg text-sm transition-colors border border-slate-700 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Descargar CSV
                </a>
            </div>
        </form>
    </div>

    @if ($places->count())
        <div class="bg-slate-900 rounded-lg border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800 border-b border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-300">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-300">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-300">Ciudad</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-300">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-300">Fecha</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-slate-300">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($places as $place)
                            <tr class="border-b border-slate-800 hover:bg-slate-800 transition-colors">
                                <td class="px-6 py-4 text-sm font-mono font-bold text-sky-400">#{{ $place->id }}</td>
                                <td class="px-6 py-4 text-sm text-slate-200 font-medium">{{ $place->name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-200">{{ $place->city }}</td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-200 border border-slate-700">
                                        @switch($place->status)
                                            @case('pending') ⏳ Pendiente @break
                                            @case('approved') ✅ Aprobado @break
                                            @case('rejected') ❌ Rechazado @break
                                        @endswitch
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-400">
                                    {{ $place->created_at->format('d M Y') }}
                                </td>

                                <td class="px-6 py-4 text-center flex items-center justify-center gap-3">

                                    {{-- Ver --}}
                                    <a href="{{ route('admin.places.show', $place->id) }}"
                                       class="text-sky-400 hover:text-sky-300 font-medium text-sm">
                                        Ver →
                                    </a>

                                    {{-- Aprobar --}}
                                    @if ($place->status === 'pending')
                                        <form method="POST" action="{{ route('admin.places.approve', $place->id) }}">
                                            @csrf
                                            <button class="text-green-400 hover:text-green-300 text-sm font-medium">
                                                Aprobar
                                            </button>
                                        </form>

                                        {{-- Rechazar --}}
                                        <form method="POST" action="{{ route('admin.places.reject', $place->id) }}">
                                            @csrf
                                            <button class="text-red-400 hover:text-red-300 text-sm font-medium">
                                                Rechazar
                                            </button>
                                        </form>
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        <div class="mt-6">
            {{ $places->links() }}
        </div>
    @else
        <div class="bg-slate-900 rounded-lg border border-slate-800 p-12 text-center">
            <p class="text-slate-400 text-lg">📭 No hay lugares para mostrar.</p>
        </div>
    @endif

</div>
@endsection
