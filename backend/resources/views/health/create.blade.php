@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-10">

        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            Nuevo registro sanitario para {{ $pet->name }}
        </h1>

        <form action="{{ route('web.pets.health.store', $pet->id) }}" method="POST" class="space-y-6">
            @csrf

            {{-- Tipo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <input type="text" name="type"
                       value="{{ old('type') }}"
                       class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                       required>

                @error('type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Fecha última acción --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de última acción</label>
                <input type="date" name="last_date"
                       value="{{ old('last_date') }}"
                       class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                       required>

                @error('last_date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Periodicidad --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periodicidad (días)</label>
                <input type="number" name="periodicity_days"
                       value="{{ old('periodicity_days') }}"
                       class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                       required>

                @error('periodicity_days')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notas --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <textarea name="notes"
                          class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                          rows="4">{{ old('notes') }}</textarea>

                @error('notes')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notificaciones --}}
            <div class="flex items-center gap-2">
                <input type="checkbox" name="notifications_active" value="1"
                       class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                       {{ old('notifications_active') ? 'checked' : '' }}>
                <label class="text-sm text-gray-700">Activar notificaciones</label>
            </div>

            {{-- Botón --}}
            <div>
                <button
                    class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-semibold">
                    Guardar registro sanitario
                </button>
            </div>

        </form>
    </div>
@endsection
