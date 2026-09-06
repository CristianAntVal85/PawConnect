@props(['name' => 'breed', 'selected' => 'all', 'label' => 'Raza'])

<div x-data="breedSelector('{{ $selected }}')" class="relative">
    <label class="mb-1 block text-xs font-medium text-gray-700">
        {{ $label }}
    </label>

    {{-- Input de búsqueda --}}
    <div class="relative">
        <input type="text"
            x-model="search"
            @focus="open = true"
            @input="open = true"
            @keydown.escape="open = false"
            placeholder="Buscar raza..."
            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 pl-9 text-sm text-gray-800
                    shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">

        <svg class="absolute left-2 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 3a7.5 7.5 0 006.15 12.65z"/>
        </svg>
    </div>

    {{-- Dropdown de sugerencias --}}
    <div x-show="open && filteredList.length > 0"
        @click.away="open = false"
        class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg max-h-60 overflow-y-auto">

        <template x-for="breed in filteredList" :key="breed">
            <div @click="selectBreed(breed)"
                class="cursor-pointer px-3 py-2 text-sm text-gray-800 hover:bg-emerald-50 hover:text-emerald-700 transition">
                <span x-text="breed"></span>
            </div>
        </template>
    </div>

    {{-- Select final (oculto pero funcional para el form) --}}
    <select name="{{ $name }}" x-model="selected" class="hidden">
        <option value="all">Todas</option>
        <template x-for="breed in breeds" :key="breed">
            <option :value="breed" x-text="breed"></option>
        </template>
    </select>
</div>
