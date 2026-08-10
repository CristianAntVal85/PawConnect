@props(['product'])

<div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow overflow-hidden group cursor-pointer"
     onclick="window.location.href='/marketplace/{{ $product['id'] }}'">
    
    <!-- Imagen -->
    <div class="relative bg-slate-200 h-48 flex items-center justify-center overflow-hidden">
        @if(!empty($product['photo']))
            <img src="{{ $product['photo'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
        @else
            <div class="text-4xl">📦</div>
        @endif
        
        <!-- Badge de descuento/oferta (opcional) -->
        @if(!empty($product['discount']))
        <div class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold">
            -{{ $product['discount'] }}%
        </div>
        @endif
    </div>

    <!-- Contenido -->
    <div class="p-4">
        <!-- Proveedor -->
        <p class="text-xs text-slate-500 mb-1">
            {{ $product['provider']['name'] ?? 'Proveedor desconocido' }}
        </p>

        <!-- Nombre del producto -->
        <h3 class="text-sm font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
            {{ $product['name'] }}
        </h3>

        <!-- Categoría -->
        <p class="text-xs text-slate-600 mb-2 capitalize">
            📂 {{ str_replace('_', ' ', $product['category'] ?? 'General') }}
        </p>

        <!-- Restricciones de peso (si aplica) -->
        @if(!empty($product['min_weight_kg']) || !empty($product['max_weight_kg']))
        <p class="text-xs text-slate-600 mb-2 text-amber-700 bg-amber-50 px-2 py-1 rounded">
            ⚖️ Peso: 
            @if(!empty($product['min_weight_kg']))
                {{ $product['min_weight_kg'] }}kg
            @endif
            @if(!empty($product['max_weight_kg']))
                - {{ $product['max_weight_kg'] }}kg
            @endif
        </p>
        @endif

        <!-- Razas recomendadas -->
        @if(!empty($product['recommended_breeds']))
        <p class="text-xs text-slate-600 mb-2">
            🐕 {{ implode(', ', (array)$product['recommended_breeds']) }}
        </p>
        @endif

        <!-- Stock -->
        <div class="mb-3">
            @if($product['stock'] > 0)
                <span class="text-xs font-semibold text-green-600">✓ En stock ({{ $product['stock'] }})</span>
            @else
                <span class="text-xs font-semibold text-red-600">✗ Sin stock</span>
            @endif
        </div>

        <!-- Precio y botón -->
        <div class="flex justify-between items-center pt-3 border-t border-slate-200">
            <div class="text-xl font-bold text-blue-600">
                ${{ number_format($product['price'], 2) }}
            </div>
            <button class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-semibold transition-colors"
                    onclick="event.stopPropagation(); addToCart({{ $product['id'] }})">
                🛒 Agregar
            </button>
        </div>
    </div>
</div>
