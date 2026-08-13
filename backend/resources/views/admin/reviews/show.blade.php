@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.reviews.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">
            ← Volver a reseñas
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Reseña de {{ $review->place->name }}</h1>
    </div>

    @if ($message = session('success'))
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
        {{ $message }}
    </div>
    @endif

    @if ($message = session('error'))
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
        {{ $message }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detalles de la reseña -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información del lugar -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Lugar</h2>
                <div class="flex gap-4">
                    @if ($review->place->photo)
                        <img src="{{ Storage::url($review->place->photo) }}" alt="{{ $review->place->name }}" class="w-24 h-24 object-cover rounded-lg">
                    @else
                        <div class="w-24 h-24 bg-gray-200 rounded-lg flex items-center justify-center">
                            <span class="text-gray-400">Sin foto</span>
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $review->place->name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $review->place->category }}</p>
                        <p class="text-sm text-gray-600">{{ $review->place->address }}</p>
                    </div>
                </div>
            </div>

            <!-- Información del reseñador -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Reseñador</h2>
                <div class="flex gap-4 items-center">
                    @if ($review->user->photo)
                        <img src="{{ Storage::url($review->user->photo) }}" alt="{{ $review->user->name }}" class="w-16 h-16 object-cover rounded-full">
                    @else
                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-gray-400 text-sm">No foto</span>
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-gray-900">{{ $review->user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $review->user->email }}</p>
                        <p class="text-xs text-gray-500 mt-1">Registrado hace {{ $review->user->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <!-- Contenido de la reseña -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Contenido de la Reseña</h2>

                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Calificación:</p>
                    <div class="flex items-center">
                        @for ($i = 0; $i < $review->rating; $i++)
                            <span class="text-2xl text-yellow-400">⭐</span>
                        @endfor
                        <span class="ml-2 text-lg font-semibold text-gray-900">{{ $review->rating }}/5</span>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Comentario:</p>
                    <p class="text-gray-900 leading-relaxed">{{ $review->body ?? 'Sin comentario' }}</p>
                </div>

                @if ($review->photo)
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Foto:</p>
                        <img src="{{ Storage::url($review->photo) }}" alt="Foto de reseña" class="max-w-full h-auto rounded-lg max-h-96">
                    </div>
                @endif

                <div class="pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500">Publicado: {{ $review->created_at->format('d/m/Y H:i') }}</p>
                    @if ($review->updated_at->ne($review->created_at))
                        <p class="text-xs text-gray-500">Última actualización: {{ $review->updated_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>

            @if ($review->status === 'rejected' && $review->rejection_reason)
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <h2 class="text-lg font-bold text-red-900 mb-2">Motivo del Rechazo</h2>
                <p class="text-red-800">{{ $review->rejection_reason }}</p>
            </div>
            @endif
        </div>

        <!-- Panel de moderación -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Moderación</h2>

                <div class="mb-6 pb-6 border-b border-gray-200">
                    <p class="text-sm text-gray-600 mb-2">Estado actual:</p>
                    <span @class(['px-3 py-1 rounded-full text-sm font-semibold',
                        'bg-blue-100 text-blue-800' => $review->status === 'pending',
                        'bg-green-100 text-green-800' => $review->status === 'approved',
                        'bg-red-100 text-red-800' => $review->status === 'rejected'
                    ])>
                        {{ $statuses[$review->status] ?? $review->status }}
                    </span>
                </div>

                @if ($review->status !== 'approved')
                <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="mb-4">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                        ✅ Aprobar Reseña
                    </button>
                </form>
                @endif

                @if ($review->status !== 'rejected')
                <div>
                    <button type="button" onclick="toggleRejectForm()" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                        ❌ Rechazar Reseña
                    </button>

                    <form id="rejectForm" action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="hidden mt-4">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Motivo del rechazo</label>
                            <textarea name="rejection_reason" required rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Explica brevemente por qué se rechaza esta reseña..."></textarea>
                            @error('rejection_reason')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                                Confirmar rechazo
                            </button>
                            <button type="button" onclick="toggleRejectForm()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>

            <!-- Información del lugar -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Información del Lugar</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600">Categoría:</p>
                        <p class="text-gray-900 font-semibold">{{ $review->place->category }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Sugerido por:</p>
                        <p class="text-gray-900 font-semibold">{{ $review->place->creator->name ?? 'Desconocido' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Estado del lugar:</p>
                        <span @class(['px-2 py-1 rounded text-xs font-semibold',
                            'bg-blue-100 text-blue-800' => $review->place->status === 'pending',
                            'bg-green-100 text-green-800' => $review->place->status === 'approved',
                            'bg-red-100 text-red-800' => $review->place->status === 'rejected'
                        ])>
                            {{ $statuses[$review->place->status] ?? $review->place->status }}
                        </span>
                    </div>
                    <div>
                        <a href="{{ route('admin.places.show', $review->place) }}" class="text-blue-600 hover:underline text-sm">
                            Ver detalles del lugar →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleRejectForm() {
        const form = document.getElementById('rejectForm');
        form.classList.toggle('hidden');
    }
</script>
@endsection
