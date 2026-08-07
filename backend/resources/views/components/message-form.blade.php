{{--
    Componente: Formulario para enviar mensajes (sin recarga)
    Uso:
    <x-message-form :match-id="$match->id" :user-id="auth()->id()" />
--}}
@props(['matchId', 'userId'])

<form
    @submit.prevent="handleMessageSubmit(this, {{ $matchId }}, document.getElementById('messages-container-{{ $matchId }}'))"
    class="flex flex-col sm:flex-row gap-2 p-3 sm:p-4 bg-gray-100 rounded-lg"
    method="POST"
>
    <textarea
        name="body"
        placeholder="Escribe tu mensaje..."
        rows="2"
        class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
        required
    ></textarea>

    <button
        type="submit"
        class="px-3 sm:px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition duration-200 self-end sm:self-auto"
        title="Enviar mensaje"
    >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5.951-1.488 5.951 1.488a1 1 0 001.169-1.409l-7-14z"/>
        </svg>
    </button>
</form>

{{-- Contenedor de mensajes --}}
<div
    id="messages-container-{{ $matchId }}"
    class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-3 sm:space-y-4"
    data-user-id="{{ $userId }}"
>
    {{-- Los mensajes se cargarán vía JavaScript --}}
</div>

@push('scripts')
<script>
function handleMessageSubmit(form, matchId, container) {
    window.Messages.handleMessageSubmit(form, matchId, container);
}
</script>
@endpush
