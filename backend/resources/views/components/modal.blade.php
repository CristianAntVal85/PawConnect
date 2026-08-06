@props([
    'id' => 'modal-' . uniqid(),
    'size' => 'md',
    'title' => null,
    'footer' => null,
    'closeButton' => true,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4',
        default => 'max-w-lg',
    };
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $id }}.window="open = true"
    x-on:close-modal-{{ $id }}.window="open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    <!-- Overlay -->
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
        aria-hidden="true"
    ></div>

    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center p-4">
        <!-- Modal Panel -->
        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            @click.away="open = false"
            {{ $attributes->merge(['class' => "relative w-full $sizeClasses transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all"]) }}
        >
            <!-- Header -->
            @if($title || $closeButton)
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    @if($title)
                        <h3 class="brand-font text-lg font-semibold text-slate-900" id="modal-title">
                            {{ $title }}
                        </h3>
                    @else
                        <div></div>
                    @endif

                    @if($closeButton)
                        <button
                            type="button"
                            @click="open = false"
                            class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            aria-label="Cerrar modal"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            <!-- Body -->
            <div class="px-6 py-4">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if($footer)
                <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-6 py-4">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

@once
    @push('scripts')
    <script>
        // Helper functions para abrir/cerrar modales desde JavaScript
        window.openModal = (id) => {
            window.dispatchEvent(new CustomEvent('open-modal-' + id));
        };
        
        window.closeModal = (id) => {
            window.dispatchEvent(new CustomEvent('close-modal-' + id));
        };
    </script>
    @endpush
@endonce
