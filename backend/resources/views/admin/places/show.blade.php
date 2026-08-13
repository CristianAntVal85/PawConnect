@extends('layouts.admin')

@section('title', 'Detalles del Lugar')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">{{ $place->name }}</h1>
        <a href="{{ route('admin.places.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-md transition">
            ← Volver
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información principal -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <!-- Foto -->
                @if ($place->photo)
                    <div class="mb-6">
                        <img src="{{ asset('storage/' . $place->photo) }}" alt="{{ $place->name }}"
                            class="w-full h-80 object-cover rounded-lg">
                    </div>
                @endif

                <!-- Información básica -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Categoría</p>
                        <p class="text-gray-900 font-semibold">{{ ucfirst($place->category) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Estado</p>
                        <p class="text-gray-900 font-semibold">
                            @if ($place->status === 'pending')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">⏳ Pendiente</span>
                            @elseif ($place->status === 'approved')
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">✅ Aprobado</span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">❌ Rechazado</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Creador</p>
                        <p class="text-gray-900 font-semibold">{{ $place->creator?->name ?? 'Desconocido' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Fecha de creación</p>
                        <p class="text-gray-900 font-semibold">{{ $place->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mb-6">
                    <p class="text-gray-500 text-sm font-medium mb-2">Descripción</p>
                    <p class="text-gray-700 leading-relaxed">
                        {{ $place->description ?? 'Sin descripción' }}
                    </p>
                </div>

                <!-- Contacto -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    @if ($place->address)
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Dirección</p>
                            <p class="text-gray-700">{{ $place->address }}</p>
                        </div>
                    @endif
                    @if ($place->phone)
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Teléfono</p>
                            <p class="text-gray-700">{{ $place->phone }}</p>
                        </div>
                    @endif
                    @if ($place->website)
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Sitio web</p>
                            <p class="text-blue-600">
                                <a href="{{ $place->website }}" target="_blank" rel="noopener noreferrer">
                                    {{ $place->website }} ↗
                                </a>
                            </p>
                        </div>
                    @endif
                    @if ($place->lat && $place->lng)
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Coordenadas</p>
                            <p class="text-gray-700">{{ $place->lat }}, {{ $place->lng }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Reseñas -->
            @if ($place->reviews->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-4">Reseñas ({{ $place->reviews->count() }})</h2>
                    <div class="space-y-4">
                        @foreach ($place->reviews as $review)
                            <div class="border-l-4 border-blue-500 pl-4 py-2">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-semibold text-gray-900">{{ $review->user?->name }}</p>
                                    <p class="text-yellow-500">
                                        @for ($i = 0; $i < $review->rating; $i++)
                                            ⭐
                                        @endfor
                                    </p>
                                </div>
                                <p class="text-gray-700">{{ $review->body }}</p>
                                <p class="text-gray-500 text-sm mt-2">
                                    {{ $review->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar de acciones -->
        <div>
            @if ($place->status === 'pending')
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">⏳ Decisión Pendiente</h3>
                    <p class="text-gray-600 text-sm mb-6">
                        Este lugar está esperando tu aprobación o rechazo.
                    </p>

                    <!-- Formulario Aprobar -->
                    <form action="{{ route('admin.places.approve', $place) }}" method="POST" class="mb-4">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md transition flex items-center justify-center gap-2">
                            ✅ Aprobar Lugar
                        </button>
                    </form>

                    <!-- Formulario Rechazar -->
                    <details class="mb-4">
                        <summary class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md text-center cursor-pointer transition flex items-center justify-center gap-2">
                            ❌ Rechazar Lugar
                        </summary>
                        <form action="{{ route('admin.places.reject', $place) }}" method="POST" class="mt-3 p-4 bg-red-50 rounded-md border border-red-200">
                            @csrf
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo del rechazo (obligatorio)
                            </label>
                            <textarea name="rejection_reason" id="rejection_reason" rows="4"
                                placeholder="Ej: Ubicación incorrecta, información incompleta, categoría no apropiada..."
                                class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 text-sm @error('rejection_reason') is-invalid @enderror"
                                required></textarea>

                            @error('rejection_reason')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <button type="submit" class="w-full mt-3 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md transition">
                                Confirmar rechazo
                            </button>
                        </form>
                    </details>

                    <div class="pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.places.index', ['status' => 'pending']) }}"
                            class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                            ← Siguiente lugar pendiente
                        </a>
                    </div>
                </div>
            @elseif ($place->status === 'approved')
                <div class="bg-green-50 rounded-lg shadow-md p-6 sticky top-8 border border-green-200">
                    <h3 class="text-lg font-bold text-green-900 mb-2">✅ Lugar Aprobado</h3>
                    <p class="text-green-700 text-sm">
                        Este lugar está visible para todos los usuarios de PawConnect.
                    </p>
                    <p class="text-green-600 text-xs mt-3">
                        Aprobado el {{ $place->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            @else
                <div class="bg-red-50 rounded-lg shadow-md p-6 sticky top-8 border border-red-200">
                    <h3 class="text-lg font-bold text-red-900 mb-2">❌ Lugar Rechazado</h3>
                    <p class="text-red-700 text-sm">
                        Este lugar no es visible en PawConnect.
                    </p>
                    <p class="text-red-600 text-xs mt-3">
                        Rechazado el {{ $place->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            @endif

            <!-- Estadísticas del lugar -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6 sticky top-48">
                <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Estadísticas</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <p class="text-gray-600">Reseñas</p>
                        <p class="text-gray-900 font-semibold">{{ $place->reviews->count() }}</p>
                    </div>
                    <div class="flex justify-between items-center">
                        <p class="text-gray-600">Favoritos</p>
                        <p class="text-gray-900 font-semibold">{{ $place->favoritedBy->count() }}</p>
                    </div>
                    <div class="flex justify-between items-center">
                        <p class="text-gray-600">Rating promedio</p>
                        <p class="text-gray-900 font-semibold">
                            @if ($place->reviews->count() > 0)
                                {{ number_format($place->reviews->avg('rating'), 1) }} ⭐
                            @else
                                Sin calificaciones
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
