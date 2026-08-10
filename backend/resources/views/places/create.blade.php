@extends('layouts.app')

@section('title', 'Sugerir Lugar Pet-Friendly')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900">➕ Sugerir Nuevo Lugar</h1>
            <p class="text-gray-600 mt-2">Ayuda a la comunidad compartiendo un lugar pet-friendly que conoces</p>
        </div>

        <!-- Formulario -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <form action="{{ route('places.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del lugar <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        class="w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ej: Parque Central"
                        required
                    >
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Categoría -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Categoría <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="category" 
                        name="category"
                        class="w-full px-4 py-2 border @error('category') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                        <option value="">Selecciona una categoría</option>
                        <option value="pipican" @selected(old('category') === 'pipican')>🏊‍♂️ Piscina para perros</option>
                        <option value="beach" @selected(old('category') === 'beach')>🏖️ Playa</option>
                        <option value="park" @selected(old('category') === 'park')>🌳 Parque</option>
                        <option value="vet" @selected(old('category') === 'vet')>🏥 Veterinaria</option>
                        <option value="shop" @selected(old('category') === 'shop')>🛒 Tienda</option>
                        <option value="restaurant" @selected(old('category') === 'restaurant')>🍽️ Restaurante</option>
                        <option value="hotel" @selected(old('category') === 'hotel')>🏨 Hotel</option>
                    </select>
                    @error('category')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Descripción <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        class="w-full px-4 py-2 border @error('description') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Describe el lugar, qué lo hace especial para mascotas..."
                        required
                    >{{ old('description') }}</textarea>
                    <p class="text-gray-500 text-sm mt-1">Máximo 1000 caracteres</p>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ubicación: Latitud y Longitud -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="lat" class="block text-sm font-medium text-gray-700 mb-2">
                            Latitud <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="lat" 
                            name="lat" 
                            step="0.00001"
                            value="{{ old('lat') }}"
                            class="w-full px-4 py-2 border @error('lat') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="-33.9249"
                            required
                        >
                        @error('lat')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="lng" class="block text-sm font-medium text-gray-700 mb-2">
                            Longitud <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="lng" 
                            name="lng" 
                            step="0.00001"
                            value="{{ old('lng') }}"
                            class="w-full px-4 py-2 border @error('lng') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="-18.4241"
                            required
                        >
                        @error('lng')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Botón para obtener coordenadas -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <button 
                        type="button" 
                        id="geoloc-btn" 
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
                    >
                        📍 Obtener mis coordenadas
                    </button>
                    <p class="text-sm text-gray-600 mt-2">
                        Usa tu ubicación actual o ingresa las coordenadas manualmente
                    </p>
                </div>

                <!-- Dirección -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Dirección <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="address" 
                        name="address" 
                        value="{{ old('address') }}"
                        class="w-full px-4 py-2 border @error('address') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ej: Calle Principal 123, Madrid"
                        required
                    >
                    @error('address')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono (opcional) -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Teléfono <span class="text-gray-500">(opcional)</span>
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone') }}"
                        class="w-full px-4 py-2 border @error('phone') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="+34 911 234 567"
                    >
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sitio web (opcional) -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                        Sitio web <span class="text-gray-500">(opcional)</span>
                    </label>
                    <input 
                        type="url" 
                        id="website" 
                        name="website" 
                        value="{{ old('website') }}"
                        class="w-full px-4 py-2 border @error('website') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="https://ejemplo.com"
                    >
                    @error('website')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Foto (opcional) -->
                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
                        Foto <span class="text-gray-500">(opcional, máx 5MB)</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition" id="photo-drop">
                        <input 
                            type="file" 
                            id="photo" 
                            name="photo" 
                            accept="image/*"
                            class="hidden"
                        >
                        <div>
                            <p class="text-gray-600">📸 Arrastra una foto aquí o haz clic para seleccionar</p>
                            <p class="text-sm text-gray-500 mt-1">JPG, PNG o GIF. Máximo 5MB</p>
                        </div>
                        <img id="photo-preview" class="mt-4 max-h-48 mx-auto" style="display:none;">
                    </div>
                    @error('photo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nota importante -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <p class="text-sm text-amber-800">
                        ⚠️ <strong>Nota:</strong> Tu sugerencia quedará en estado "Pendiente de aprobación" hasta que un administrador la verifique. Solo entonces será visible para otros usuarios.
                    </p>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-4">
                    <button 
                        type="submit" 
                        class="flex-1 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold"
                    >
                        ✓ Sugerir lugar
                    </button>
                    <a 
                        href="{{ route('places.index') }}" 
                        class="flex-1 bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-semibold text-center"
                    >
                        ✕ Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Geolocalización
    document.getElementById('geoloc-btn').addEventListener('click', function() {
        if (navigator.geolocation) {
            this.disabled = true;
            this.textContent = '📍 Obteniendo ubicación...';
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('lat').value = position.coords.latitude.toFixed(5);
                    document.getElementById('lng').value = position.coords.longitude.toFixed(5);
                    document.getElementById('geoloc-btn').textContent = '✓ Ubicación obtenida';
                    document.getElementById('geoloc-btn').classList.add('!bg-green-600', '!hover:bg-green-700');
                },
                function(error) {
                    alert('Error al obtener ubicación: ' + error.message);
                    document.getElementById('geoloc-btn').disabled = false;
                    document.getElementById('geoloc-btn').textContent = '📍 Obtener mis coordenadas';
                }
            );
        } else {
            alert('Geolocalización no disponible en tu navegador');
        }
    });

    // Drag and drop para foto
    const photoInput = document.getElementById('photo');
    const photoDrop = document.getElementById('photo-drop');
    const photoPreview = document.getElementById('photo-preview');

    photoDrop.addEventListener('click', () => photoInput.click());

    photoDrop.addEventListener('dragover', e => {
        e.preventDefault();
        photoDrop.classList.add('!border-blue-500', '!bg-blue-50');
    });

    photoDrop.addEventListener('dragleave', () => {
        photoDrop.classList.remove('!border-blue-500', '!bg-blue-50');
    });

    photoDrop.addEventListener('drop', e => {
        e.preventDefault();
        photoDrop.classList.remove('!border-blue-500', '!bg-blue-50');
        if (e.dataTransfer.files.length > 0) {
            photoInput.files = e.dataTransfer.files;
            previewPhoto();
        }
    });

    photoInput.addEventListener('change', previewPhoto);

    function previewPhoto() {
        const file = photoInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                photoPreview.src = e.target.result;
                photoPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection
