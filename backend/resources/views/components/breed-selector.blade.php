@props(['name' => 'breed', 'selected' => 'all', 'label' => 'Raza'])

<div x-data="breedSelector(@json($selected))" class="relative">
    <label class="mb-1 block text-xs font-medium text-gray-700">
        {{ $label }}
    </label>

    {{-- Input de búsqueda --}}
    <div class="relative">
        <input type="text"
            x-model="search"
            @focus="open = true"
            @input="open = true; filterBreeds()"
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

<script>
    function breedSelector(initialSelected = 'all') {
        return {
            search: '',
            selected: initialSelected,
            open: false,
            filteredList: [],

            breeds: [
                'Affenpinscher', 'Afgano', 'Airedale Terrier', 'Akita', 'Alaskan Malamute',
                'American Staffordshire Terrier', 'Appenzeller Sennenhund', 'Ariegeois', 'Artois Hound', 'Azawakh',
                'Basenji', 'Basset Artesien Normand', 'Basset Hound', 'Beagle', 'Bearded Collie',
                'Beaucerons', 'Bedlington Terrier', 'Bichon Frise', 'Bichon Maltes', 'Biewer Terrier',
                'Bloodhound', 'Bobtail (Old English Sheepdog)', 'Boerboel', 'Border Collie', 'Border Terrier',
                'Borzoi', 'Boston Terrier', 'Bouvier Des Flandres', 'Boxer', 'Boykin Spaniel',
                'Braco Aleman', 'Braco De Weimar', 'Briard', 'Brittany', 'Bulldog Frances',
                'Bulldog Ingles', 'Bull Terrier', 'Bullmastiff', 'Cairn Terrier', 'Caniche',
                'Carlino (Pug)', 'Catahoula Leopard Dog', 'Catalan Sheepdog', 'Caucasian Ovcharka', 'Cavalier King Charles',
                'Cesky Terrier', 'Chesapeake Bay Retriever', 'Chihuahua', 'Chin Japones', 'Chow Chow',
                'Clumber Spaniel', 'Cocker Spaniel', 'Coton De Tulear', 'Collie Rough', 'Collie Smooth',
                'Corgi Cardigun', 'Corgi Pembroke', 'Dachshund', 'Dalmata', 'Dandie Dinmont Terrier',
                'Deerhound Escoces', 'Doberman Pinscher', 'Dogo Argentino', 'Dogo Canario', 'Dogo De Burdeos',
                'Dogo Tibetano', 'Dutch Shepherd', 'English Springer Spaniel', 'Epagneul Breton', 'Escoces Ciervo',
                'Espaniel Breton', 'Estaffordshire Bull Terrier', 'Estonian Hound', 'Fazione', 'Faraon Hound',
                'Fila Brasileiro', 'Flat-Coated Retriever', 'Fox Terrier (Pelo Duro)', 'Fox Terrier (Pelo Liso)', 'Foxhound Americano',
                'Foxhound Ingles', 'Galgo Espanol', 'Galgo Irlandes', 'Galgo Italiano', 'Galgo Ruso',
                'German Pinscher', 'German Shorthaired Pointer', 'German Wirehaired Pointer', 'Gran Danes', 'Grand Basset Griffon Vendeen',
                'Grand Bleu De Gascogne', 'Grand Griffon Vendeen', 'Greyhound', 'Griffon Bruxelas', 'Griffon Nivernais',
                'Groenendael', 'Gypsy Vanner', 'Hairless Peruvian', 'Hamiltonstovare', 'Hannover Hound',
                'Harrier', 'Havanese', 'Hokkaido', 'Hovawart', 'Husky Siberiano',
                'Ibizan Hound', 'Icelandic Sheepdog', 'Irish Red Setter', 'Irish Terrier', 'Irish Water Spaniel',
                'Irish Wolfhound', 'Istrian Shorthaired Hound', 'Italian Greyhound', 'Italiano Spinone', 'Jack Russell Terrier',
                'Jagdterrier', 'Kakapo', 'Kangal Dog', 'Karelian Bear Dog', 'Keeshond',
                'Kerry Blue Terrier', 'Komondor', 'Kuvasz', 'Labrador Retriever', 'Lakeland Terrier',
                'Lapphund Sueco', 'Lapphund Finlandes', 'Lebrel Afgano', 'Lebrel Escoces', 'Lebrel Irlandes',
                'Leonberger', 'Levrier Borzoï', 'Levrier Italiano', 'Levrier Polaco', 'Levrier Ruso',
                'Lhasa Apso', 'Lhasa Apso Tibetano', 'Malamute De Alaska', 'Maltese', 'Manx',
                'Maremma Sheepdog', 'Mastif Español', 'Mastif Napolitano', 'Mastiff Ingles', 'Mastiff Tibetano',
                'Mastín', 'Mastín Español', 'Mastín Napolitano', 'Mudi', 'Mutt Mixed Breed',
                'Neapolitan Mastiff', 'Newfoundland', 'Norfolk Terrier', 'Norwegian Buhund', 'Norwegian Elkhound',
                'Norwich Terrier', 'Nova Scotia Duck Tolling Retriever', 'Old English Sheepdog', 'Pachon Navarro', 'Papillon',
                'Parson Russell Terrier', 'Pastor Alemán', 'Pastor Australiano', 'Pastor Belga Groenendael', 'Pastor Belga Laekenois',
                'Pastor Belga Malinois', 'Pastor Belga Tervuren', 'Pastor Blanco Suizo', 'Pastor Caucasico', 'Pastor Catalan',
                'Pastor Ganadero Australiano', 'Pastor Ganadero Australiano (Miniatura)', 'Pastor Ganadero Inglés', 'Pastor Ganadero Inglés (Miniatura)', 'Pastor Ganadero Inglés De Cola Recta',
                'Pastor Ganadero Inglés De Cola Recta (Miniatura)', 'Pastor Inglés De Ovejas', 'Pastor Inglés De Ovejas (Miniatura)', 'Pastor Inglés Viejo', 'Pastor Islandés',
                'Pastor Lapón Finlandés', 'Pastor Lapón Sueco', 'Pastor Maremmano', 'Pastor Pirenáico', 'Pastor Polaco Nizinny',
                'Pastor Puli', 'Pastor Ruso', 'Pastor Ruso Meridional', 'Pastor Sueco De Värmland', 'Pastor Tatra',
                'Pastor Vasco', 'Patti', 'Patterdale Terrier', 'Pekinés', 'Pequinés',
                'Pequinés Japones', 'Perdigüero Burgalés', 'Perdigüero De Burgos', 'Perdiguero Portugués', 'Perro Agua Español',
                'Perro Agua Portugues', 'Perro Agua Portugues Pequeño', 'Perro Agua Romagnolo', 'Perro Agua Tosa', 'Perro Agua Tollerante',
                'Perro Agua Romagnolo', 'Perro Agua Tollerante', 'Perro Crestado Chino', 'Perro Dálmata', 'Perro De Aguas Españolas',
                'Perro De Aguas Español', 'Perro De Agua Español', 'Perro De Agua Portugués', 'Perro De Agua Romagnolo', 'Perro De Agua Tosa',
                'Perro De Agua Tollerante', 'Perro De Agua Romagnolo', 'Perro De Agua Tollerante', 'Perro De Agua Romagnolo', 'Perro De Agua Tollerante',
                'Perro De Agua Romagnolo', 'Perro De Agua Tollerante', 'Perro De Agua Romagnolo', 'Perro De Agua Tollerante', 'Perro De Agua Romagnolo'
            ],

            // Función para normalizar caracteres sin tildes
            normalize(text) {
                return text.toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
            },

            filterBreeds() {
                if (!this.search.trim()) {
                    this.filteredList = this.breeds;
                    return;
                }

                const searchNorm = this.normalize(this.search);
                this.filteredList = this.breeds.filter(breed => {
                    const breedNorm = this.normalize(breed);
                    return breedNorm.includes(searchNorm);
                });
            },

            selectBreed(breed) {
                this.selected = breed;
                this.search = breed;
                this.open = false;
            },

            init() {
                this.filterBreeds();
            }
        }
    }
</script>
