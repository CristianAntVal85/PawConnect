{{-- 
    Componente: Botón para unirse/abandonar paseo (sin recarga)
    Uso:
    <x-join-walk-button :walk-id="$walk->id" :pet-id="$userPet->id" :is-joined="$isUserInWalk" />
--}}
@props(['walkId', 'petId', 'isJoined' => false])

<button
    @click="handleWalkToggleClick(this, {{ $walkId }}, {{ $petId }}, {{ $isJoined ? 'true' : 'false' }})"
    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition {{ $isJoined ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-blue-500 hover:bg-blue-600 text-white' }}"
    data-walk-id="{{ $walkId }}"
    data-pet-id="{{ $petId }}"
    data-joined="{{ $isJoined ? 'true' : 'false' }}"
>
    @if ($isJoined)
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
        <span>Abandonar</span>
    @else
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Unirse</span>
    @endif
</button>

@push('scripts')
<script>
function handleWalkToggleClick(button, walkId, petId, isJoined) {
    window.Walks.handleWalkToggleClick(button, walkId, petId, isJoined);
}
</script>
@endpush
