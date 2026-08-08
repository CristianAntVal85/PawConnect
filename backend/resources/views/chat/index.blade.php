@extends('layouts.app')

@section('title', 'Chat | PawConnect')
@section('page-title', 'Chat')
@section('page-subtitle', 'Conversaciones en tiempo real con tus matches')

@section('content')
<div
    class="grid min-h-[72vh] grid-cols-1 gap-4 lg:grid-cols-[320px_minmax(0,1fr)]"
    data-chat-root
    data-user-id="{{ $currentUserId }}"
    data-user-name="{{ auth()->user()->name }}"
    data-active-match-id="{{ $activeMatchId ?? '' }}"
>
    <aside class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
        <div class="mb-3 px-1">
            <h2 class="brand-font text-lg font-semibold text-slate-900">Conversaciones</h2>
            <p class="text-xs text-slate-500">Selecciona un match para abrir el chat.</p>
        </div>

        <div class="space-y-2">
            @forelse ($conversations as $conversation)
                <button
                    type="button"
                    data-conversation-id="{{ $conversation['id'] }}"
                    data-conversation-title="{{ $conversation['title'] }}"
                    data-conversation-subtitle="{{ $conversation['subtitle'] }}"
                    class="w-full rounded-xl border px-3 py-3 text-left transition {{ (int) ($activeMatchId ?? 0) === (int) $conversation['id'] ? 'border-emerald-500 bg-emerald-50' : 'border-transparent bg-white hover:border-slate-200 hover:bg-slate-50' }}"
                >
                    <div class="flex items-start gap-3">
                        @if ($conversation['avatar'])
                            <img src="{{ $conversation['avatar'] }}" alt="{{ $conversation['title'] }}" class="h-11 w-11 rounded-xl object-cover">
                        @else
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-slate-200 text-sm font-semibold text-slate-600">
                                {{ \Illuminate\Support\Str::of($conversation['title'])->substr(0, 1)->upper() }}
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $conversation['title'] }}</p>
                                <span
                                    data-unread-badge
                                    class="{{ $conversation['unread_count'] > 0 ? '' : 'hidden' }} inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-emerald-500 px-1.5 text-[11px] font-bold text-white"
                                >
                                    {{ $conversation['unread_count'] }}
                                </span>
                            </div>
                            <p class="truncate text-xs text-slate-500">{{ $conversation['subtitle'] }}</p>
                            <p class="mt-1 truncate text-xs text-slate-600">{{ $conversation['last_message'] ?? 'Sin mensajes todavia.' }}</p>
                        </div>
                    </div>
                </button>
            @empty
                <p class="rounded-xl border border-dashed border-slate-300 px-3 py-4 text-sm text-slate-500">
                    Aun no tienes conversaciones activas.
                </p>
            @endforelse
        </div>
    </aside>

    <section class="flex min-h-[72vh] flex-col rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-200 px-5 py-4">
            <h3 class="brand-font text-lg font-semibold text-slate-900" data-chat-header-name>
                {{ $conversations->firstWhere('id', $activeMatchId)['title'] ?? 'Selecciona una conversacion' }}
            </h3>
            <p class="text-xs text-slate-500" data-chat-header-meta>
                {{ $conversations->firstWhere('id', $activeMatchId)['subtitle'] ?? 'Solo los participantes del match pueden escribir.' }}
            </p>
        </header>

        <div class="flex-1 overflow-y-auto px-5 py-4" data-chat-messages>
            @if (! $activeMatchId)
                <div class="flex h-full items-center justify-center rounded-2xl border border-dashed border-slate-300 text-sm text-slate-500">
                    Elige una conversacion del sidebar para cargar los mensajes.
                </div>
            @endif
        </div>

        <div class="px-5 pb-2">
            <div data-typing-indicator class="hidden text-xs text-emerald-700">
                <span data-typing-text>Escribiendo...</span>
            </div>
        </div>

        <form data-chat-form class="border-t border-slate-200 p-4">
            <div class="flex items-end gap-2">
                <textarea
                    name="body"
                    rows="2"
                    required
                    placeholder="Escribe un mensaje..."
                    class="min-h-[52px] flex-1 resize-none rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                ></textarea>
                <button
                    type="submit"
                    class="rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    Enviar
                </button>
            </div>
            <p class="mt-2 text-[11px] text-slate-500">Tip: al escribir se emite client-typing en el canal privado del match.</p>
        </form>
    </section>
</div>
@endsection
