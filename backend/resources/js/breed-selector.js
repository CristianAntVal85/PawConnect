export default function breedSelector(initialSelected = 'all') {
    return {
        search: '',
        selected: initialSelected,
        open: false,
        filteredList: [],

        breeds: [
            'Afgano (Afghan Hound)', 'Airedale Terrier', 'Akita Inu', 'Alaskan Malamute',
            'American Bully', 'American Cocker Spaniel', 'American Staffordshire Terrier',
            'Appenzeller Sennenhund', 'Australian Cattle Dog', 'Australian Shepherd',
            'Australian Terrier', 'Barbet', 'Basenji', 'Basset Hound', 'Beagle',
            'Bearded Collie', 'Beauceron', 'Bedlington Terrier', 'Belgian Griffon',
            'Belgian Malinois', 'Belgian Tervuren', 'Bergamasco', 'Bernese Mountain Dog',
            'Bichón Frisé', 'Bichón Habanero', 'Bichón Maltés', 'Black Russian Terrier',
            'Bloodhound', 'Bobtail (Old English Sheepdog)', 'Boerboel', 'Border Collie',
            'Border Terrier', 'Borzoi', 'Boston Terrier', 'Bóxer',
            'Braco Alemán (German Shorthaired Pointer)', 'Braco Francés', 'Braco Italiano',
            'Briard', 'Brittany Spaniel (Epagneul Breton)', 'Bull Terrier',
            'Bulldog Americano', 'Bulldog Francés', 'Bulldog Inglés', 'Bullmastiff',
            'Cairn Terrier', 'Caniche (Poodle)', 'Cane Corso', 'Cão da Serra da Estrela',
            'Cão de Água Português', 'Carlino (Pug)', 'Cavalier King Charles Spaniel',
            'Chesapeake Bay Retriever', 'Chihuahua', 'Chow Chow',
            'Cocker Spaniel Inglés', 'Collie Rough', 'Collie Smooth', 'Coton de Tuléar',
            'Dachshund (Teckel)', 'Dálmata', 'Deerhound', 'Doberman', 'Dogo Argentino',
            'Dogo Canario (Presa Canario)', 'Dogo de Burdeos', 'English Setter',
            'English Springer Spaniel', 'Eurasier', 'Field Spaniel', 'Finnish Lapphund',
            'Finnish Spitz', 'Flat-Coated Retriever', 'Fox Terrier (Smooth)',
            'Fox Terrier (Wire)', 'Galgo Español', 'Galgo Inglés (Greyhound)',
            'Golden Retriever', 'Gordon Setter', 'Gos d’Atura Català (Pastor Catalán)',
            'Gran Danés (Great Dane)', 'Groenendael', 'Harrier', 'Hovawart',
            'Husky Siberiano', 'Irish Setter', 'Irish Terrier', 'Irish Wolfhound',
            'Jack Russell Terrier', 'Keeshond', 'Kerry Blue Terrier', 'Komondor',
            'Kooikerhondje', 'Labrador Retriever', 'Lagotto Romagnolo', 'Lakeland Terrier',
            'Leonberger', 'Lhasa Apso', 'Lowchen', 'Maltipoo', 'Manchester Terrier',
            'Mastín Español', 'Mastín Napolitano', 'Miniature Pinscher', 'Newfoundland',
            'Norfolk Terrier', 'Norwegian Buhund', 'Norwegian Elkhound', 'Norwich Terrier',
            'Nova Scotia Duck Tolling Retriever', 'Ovejero Alemán (Pastor Alemán)',
            'Papillon', 'Parson Russell Terrier', 'Pastor Australiano',
            'Pastor Belga Malinois', 'Pastor Blanco Suizo', 'Pastor Vasco', 'Pekinés',
            'Perro de Agua Español', 'Perro Lobo Checoslovaco', 'Pharaoh Hound',
            'Pinscher Alemán', 'Pitbull Terrier', 'Podenco Andaluz', 'Podenco Ibicenco',
            'Pointer Inglés', 'Pomerania', 'Poodle Toy', 'Presa Mallorquín (Ca de Bou)',
            'Pug', 'Puli', 'Rafeiro do Alentejo', 'Ratonero Bodeguero Andaluz',
            'Rhodesian Ridgeback', 'Rottweiler', 'Sabueso Español',
            'Saint Bernard (San Bernardo)', 'Saluki', 'Samoyedo', 'Schipperke',
            'Schnauzer Gigante', 'Schnauzer Mediano', 'Schnauzer Miniatura',
            'Scottish Terrier', 'Setter Inglés', 'Setter Irlandés', 'Shar Pei',
            'Shetland Sheepdog', 'Shiba Inu', 'Shih Tzu', 'Siberian Laika', 'Skye Terrier',
            'Sloughi', 'Soft Coated Wheaten Terrier', 'Spaniel Tibetano', 'Spitz Alemán',
            'Staffordshire Bull Terrier', 'Sussex Spaniel', 'Swedish Vallhund', 'Vizsla',
            'Weimaraner', 'Welsh Corgi Cardigan', 'Welsh Corgi Pembroke',
            'Welsh Springer Spaniel', 'West Highland White Terrier', 'Whippet',
            'Wirehaired Pointing Griffon', 'Yorkshire Terrier'
        ],


        // Función para normalizar caracteres sin tildes
        normalize(text) {
            return String(text || '').toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');
        },

        filterBreeds() {
            const term = this.search.trim();
            if (!term) {
                this.filteredList = this.breeds.slice(0, 60);
                return;
            }

            const searchNorm = this.normalize(term);
            this.filteredList = this.breeds
                .filter((breed) => this.normalize(breed).includes(searchNorm))
                .slice(0, 60);
        },

        selectBreed(breed) {
            this.selected = breed;
            this.search = breed;
            this.open = false;
        },

        init() {
            if (this.selected && this.selected !== 'all') {
                this.search = this.selected;
            }
            this.filterBreeds();

            this.$watch('search', () => {
                this.open = true;
                this.filterBreeds();
            });
        }
    };
}
