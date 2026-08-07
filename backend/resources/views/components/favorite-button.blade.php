{{--
    Componente de ejemplo: Botón para guardar favorito (sin recarga)
    Uso:
    <x-favorite-button :place-id="$place->id" :is-favorited="$isFavorited" />
--}}
@props(['placeId', 'isFavorited' => false])

<button
    @click="handleFavoriteClick(this, {{ $placeId }}, {{ $isFavorited ? 'true' : 'false' }})"
    class="inline-flex items-center justify-center gap-1 rounded-lg transition duration-200
        px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm
        {{ $isFavorited ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-800' }}"
    aria-label="Guardar como favorito"
    data-place-id="{{ $placeId }}"
>
    @if ($isFavorited)
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.172 15.863L4.75 18.298c-.383.214-.858-.057-.952-.459l-1.197-5.022C2.233 11.88 1 9.887 1 7.501 1 3.364 4.373 0 8.5 0c1.722 0 3.322.619 4.564 1.651.599.507 1.357.507 1.956 0 1.242-1.032 2.842-1.651 4.564-1.651 4.127 0 7.5 3.364 7.5 7.501 0 2.386-1.233 4.379-3.084 5.516l-1.197 5.022c-.094.402-.569.673-.952.459l-4.422-2.435c-.388-.213-1.08-.213-1.468 0z"/>
        </svg>
    @else
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
    @endif
    <span class="hidden sm:inline">{{ $isFavorited ? 'Guardado' : 'Guardar' }}</span>
</button>

@push('scripts')
<script>
function handleFavoriteClick(button, placeId, isFavorited) {
    window.Favorites.handleFavoriteClick(button, placeId, isFavorited);
}
</script>
@endpush
