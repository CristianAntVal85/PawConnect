@extends('layouts.app')

@section('title', 'Editar mascota | PawConnect')
@section('page-title', 'Editar mascota')
@section('page-subtitle', 'Actualiza la informacion del perfil')

@section('content')
    @php
        $pet = $pet ?? (object) [
            'id' => 1,
            'name' => 'Luna',
            'species' => 'dog',
            'breed' => 'Labrador',
            'age' => 3,
            'weight' => 24.5,
            'size' => 'large',
            'energy_level' => 'high',
            'personality' => 'Juguetona y cariñosa.',
            'available_for_walks' => true,
        ];
    @endphp

    <form method="POST" action="{{ route('web.pets.update', $pet->id) }}" enctype="multipart/form-data" class="space-y-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        @method('PUT')

        @if($errors->any())
            <x-alert type="error" title="Revisa los datos del formulario">
                Se encontraron errores de validacion. Corrigelos para continuar.
            </x-alert>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nombre</label>
                <input id="name" name="name" value="{{ old('name', $pet->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="species" class="mb-1 block text-sm font-medium text-slate-700">Especie</label>
                <select id="species" name="species" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                    <option value="dog" @selected(old('species', $pet->species) === 'dog')>Perro</option>
                    <option value="cat" @selected(old('species', $pet->species) === 'cat')>Gato</option>
                    <option value="other" @selected(old('species', $pet->species) === 'other')>Otra</option>
                </select>
                @error('species') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="breed" class="mb-1 block text-sm font-medium text-slate-700">Raza</label>
                <input id="breed" name="breed" value="{{ old('breed', $pet->breed) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                @error('breed') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="age" class="mb-1 block text-sm font-medium text-slate-700">Edad</label>
                <input id="age" type="number" min="0" name="age" value="{{ old('age', $pet->age) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                @error('age') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="weight" class="mb-1 block text-sm font-medium text-slate-700">Peso</label>
                <input id="weight" type="number" min="0" step="0.01" name="weight" value="{{ old('weight', $pet->weight) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                @error('weight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="size" class="mb-1 block text-sm font-medium text-slate-700">Tamano</label>
                <select id="size" name="size" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                    <option value="small" @selected(old('size', $pet->size) === 'small')>Pequeno</option>
                    <option value="medium" @selected(old('size', $pet->size) === 'medium')>Mediano</option>
                    <option value="large" @selected(old('size', $pet->size) === 'large')>Grande</option>
                </select>
                @error('size') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="energy_level" class="mb-1 block text-sm font-medium text-slate-700">Nivel de energia</label>
                <select id="energy_level" name="energy_level" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">
                    <option value="low" @selected(old('energy_level', $pet->energy_level) === 'low')>Baja</option>
                    <option value="medium" @selected(old('energy_level', $pet->energy_level) === 'medium')>Media</option>
                    <option value="high" @selected(old('energy_level', $pet->energy_level) === 'high')>Alta</option>
                </select>
                @error('energy_level') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="photo" class="mb-1 block text-sm font-medium text-slate-700">Nueva foto (opcional)</label>
                <input id="photo" type="file" name="photo" accept="image/*" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-sm">
                @error('photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <p class="mb-1 block text-sm font-medium text-slate-700">Previsualizacion</p>
                <img id="photo-preview" src="" alt="Previsualizacion de foto" class="hidden h-40 w-full rounded-lg border border-slate-200 object-cover">
                <p id="photo-preview-placeholder" class="rounded-lg border border-dashed border-slate-300 p-4 text-xs text-slate-500">
                    Selecciona una imagen para verla antes de guardar.
                </p>
            </div>
        </div>

        <div>
            <label for="personality" class="mb-1 block text-sm font-medium text-slate-700">Personalidad</label>
            <textarea id="personality" name="personality" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-200">{{ old('personality', $pet->personality) }}</textarea>
            @error('personality') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="available_for_walks" value="1" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400" @checked(old('available_for_walks', $pet->available_for_walks))>
            Disponible para paseos
        </label>
        @error('available_for_walks') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Guardar cambios</button>
            <a href="{{ route('web.pets.show', $pet->id) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    const photoInputEdit = document.getElementById('photo');
    const photoPreviewEdit = document.getElementById('photo-preview');
    const photoPlaceholderEdit = document.getElementById('photo-preview-placeholder');

    photoInputEdit?.addEventListener('change', (event) => {
        const [file] = event.target.files || [];

        if (!file) {
            photoPreviewEdit?.classList.add('hidden');
            photoPlaceholderEdit?.classList.remove('hidden');
            return;
        }

        const previewUrl = URL.createObjectURL(file);
        if (photoPreviewEdit) {
            photoPreviewEdit.src = previewUrl;
            photoPreviewEdit.classList.remove('hidden');
        }
        photoPlaceholderEdit?.classList.add('hidden');
    });
</script>
@endpush
