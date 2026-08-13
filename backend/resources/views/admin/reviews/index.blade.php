@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Moderación de Reseñas</h1>
        <p class="text-gray-600">Revisa y modera las reseñas de lugares sugeridos</p>
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

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los estados</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Calificación</label>
                <select name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas las calificaciones</option>
                    <option value="1" @selected(request('rating') === '1')>⭐ 1 estrella</option>
                    <option value="2" @selected(request('rating') === '2')>⭐ 2 estrellas</option>
                    <option value="3" @selected(request('rating') === '3')>⭐ 3 estrellas</option>
                    <option value="4" @selected(request('rating') === '4')>⭐ 4 estrellas</option>
                    <option value="5" @selected(request('rating') === '5')>⭐ 5 estrellas</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Lugar o usuario..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Filtrar
                </button>

                @if (request()->hasAny(['status', 'rating', 'search', 'date_from', 'date_to']))
                    <a href="{{ route('admin.reviews.index') }}" class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 text-center transition">
                        Limpiar
                    </a>
                @endif

                <a href="{{ route('admin.reviews.export') }}" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-center transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Descargar CSV
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de reseñas -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if ($reviews->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lugar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reseñador</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Calificación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($reviews as $review)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.reviews.show', $review) }}" class="text-blue-600 hover:underline">
                                    {{ $review->place->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $review->user->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center">
                                    @for ($i = 0; $i < $review->rating; $i++)
                                        <span class="text-yellow-400">⭐</span>
                                    @endfor
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span @class(['px-3 py-1 rounded-full text-xs font-semibold',
                                    'bg-blue-100 text-blue-800' => $review->status === 'pending',
                                    'bg-green-100 text-green-800' => $review->status === 'approved',
                                    'bg-red-100 text-red-800' => $review->status === 'rejected'
                                ])>
                                    {{ $statuses[$review->status] ?? $review->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $review->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.reviews.show', $review) }}" class="text-blue-600 hover:underline font-medium">
                                    Ver detalles
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No hay reseñas para mostrar
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Paginación -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $reviews->links() }}
            </div>
        @else
            <div class="p-8 text-center text-gray-500">
                <p class="text-lg">No hay reseñas para mostrar</p>
            </div>
        @endif
    </div>
</div>
@endsection
