@props([
    'name' => 'photo',
    'label' => 'Foto',
    'current' => null,   // URL de la foto actual del usuario
])

{{-- FOTO ACTUAL --}}
<div class="mb-4">
    <p class="mb-1 block text-sm font-medium text-slate-700">Foto actual</p>

    @if($current)
        <img
            src="{{ $current ? Storage::url($current) : '' }}"
            alt="Foto actual"
            class="h-40 w-full rounded-lg border border-slate-200 object-cover"
        >
    @else
        <p class="rounded-lg border border-dashed border-slate-300 p-4 text-xs text-slate-500">
            No tienes una foto de perfil aún.
        </p>
    @endif
</div>

{{-- INPUT PARA SUBIR NUEVA FOTO --}}
<div>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-slate-700">
        {{ $label }}
    </label>

    <input
        id="{{ $name }}"
        type="file"
        name="{{ $name }}"
        accept="image/*"
        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
               file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-sm"
    >

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- PREVISUALIZACIÓN DE NUEVA FOTO --}}
<div class="mt-3">
    <p class="mb-1 block text-sm font-medium text-slate-700">Nueva foto (previsualización)</p>

    <img
        id="{{ $name }}-preview"
        src=""
        alt="Previsualización"
        class="hidden h-40 w-full rounded-lg border border-slate-200 object-cover"
    >

    <p
        id="{{ $name }}-placeholder"
        class="rounded-lg border border-dashed border-slate-300 p-4 text-xs text-slate-500"
    >
        Selecciona una imagen para verla antes de guardar.
    </p>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('{{ $name }}');
    const preview = document.getElementById('{{ $name }}-preview');
    const placeholder = document.getElementById('{{ $name }}-placeholder');

    if (!input) return;

    input.addEventListener('change', function(e) {
        const file = e.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                preview.src = event.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    });
});
</script>
@endpush
