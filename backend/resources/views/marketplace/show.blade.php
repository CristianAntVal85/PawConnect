@extends('layouts.app')

@section('title', 'Detalle del Producto')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Botón Volver -->
        <a href="{{ route('web.marketplace.index') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 mb-6 font-semibold">
            ← Volver al marketplace
        </a>

        <div id="product-container" class="bg-white rounded-lg shadow-xl overflow-hidden">
            <!-- Contenedor del producto (se carga con JavaScript) -->
            <div class="text-center py-16">
                <p class="text-slate-500">Cargando detalles del producto...</p>
            </div>
        </div>

        <!-- Productos relacionados -->
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Productos relacionados</h2>
            <div id="related-products" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="col-span-full py-12 text-center">
                    <p class="text-slate-500">Cargando productos relacionados...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script para cargar el producto específico
const productId = {{ $productId }};
const API_URL = '/api/v1';

async function loadProduct() {
    try {
        const response = await fetch(`${API_URL}/products/${productId}`, {
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!response.ok) {
            throw new Error(`Error: ${response.status}`);
        }

        const data = await response.json();
        renderProductDetail(data.data);
        loadRelatedProducts(data.data.category);

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('product-container').innerHTML = `
            <div class="p-8 text-center">
                <p class="text-red-500 text-lg">❌ Error al cargar el producto</p>
            </div>
        `;
    }
}

function renderProductDetail(product) {
    const container = document.getElementById('product-container');
    
    container.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
            <!-- Imagen -->
            <div class="flex items-center justify-center bg-slate-100 rounded-lg h-96">
                ${product.photo 
                    ? `<img src="${product.photo}" alt="${product.name}" class="w-full h-full object-cover rounded">`
                    : '<div class="text-6xl">📦</div>'
                }
            </div>

            <!-- Detalles -->
            <div>
                <div class="mb-4">
                    <p class="text-slate-500 text-sm mb-2">🏪 ${product.provider?.name || 'Proveedor'}</p>
                    <h1 class="text-4xl font-bold text-slate-900 mb-2">${product.name}</h1>
                    <p class="text-slate-600 text-lg">${product.description || 'Sin descripción'}</p>
                </div>

                <!-- Precio -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-slate-600 mb-1">Precio</p>
                    <p class="text-4xl font-bold text-blue-600">$${parseFloat(product.price).toFixed(2)}</p>
                </div>

                <!-- Información importante -->
                <div class="space-y-4 mb-6">
                    <!-- Categoría -->
                    <div class="flex items-start gap-3 bg-slate-50 p-4 rounded-lg">
                        <span class="text-xl">📂</span>
                        <div>
                            <p class="text-sm text-slate-600">Categoría</p>
                            <p class="font-semibold text-slate-900 capitalize">${product.category}</p>
                        </div>
                    </div>

                    <!-- Stock -->
                    <div class="flex items-start gap-3 ${product.stock > 0 ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'} p-4 rounded-lg">
                        <span class="text-xl">${product.stock > 0 ? '✓' : '✗'}</span>
                        <div>
                            <p class="text-sm text-slate-600">Disponibilidad</p>
                            <p class="font-semibold text-slate-900">
                                ${product.stock > 0 ? `${product.stock} unidades disponibles` : 'Sin stock'}
                            </p>
                        </div>
                    </div>

                    <!-- Restricciones de peso -->
                    ${product.min_weight_kg || product.max_weight_kg ? `
                        <div class="flex items-start gap-3 bg-amber-50 p-4 rounded-lg">
                            <span class="text-xl">⚖️</span>
                            <div>
                                <p class="text-sm text-slate-600">Rango de peso</p>
                                <p class="font-semibold text-slate-900">
                                    Desde ${product.min_weight_kg || '0'}kg hasta ${product.max_weight_kg || '∞'}kg
                                </p>
                            </div>
                        </div>
                    ` : ''}

                    <!-- Razas recomendadas -->
                    ${product.recommended_breeds && product.recommended_breeds.length ? `
                        <div class="flex items-start gap-3 bg-purple-50 p-4 rounded-lg">
                            <span class="text-xl">🐕</span>
                            <div>
                                <p class="text-sm text-slate-600">Razas recomendadas</p>
                                <p class="font-semibold text-slate-900">
                                    ${Array.isArray(product.recommended_breeds) 
                                        ? product.recommended_breeds.join(', ')
                                        : product.recommended_breeds
                                    }
                                </p>
                            </div>
                        </div>
                    ` : ''}
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-4">
                    <button onclick="addToCart(${product.id})" 
                            class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors"
                            ${product.stock === 0 ? 'disabled' : ''}>
                        🛒 Agregar al carrito
                    </button>
                    <button onclick="window.history.back()" 
                            class="px-6 py-3 bg-slate-300 hover:bg-slate-400 text-slate-900 font-bold rounded-lg transition-colors">
                        ← Atrás
                    </button>
                </div>

                <!-- Envío -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-900">
                        📦 <strong>Envío disponible</strong><br>
                        Entrega en 3-5 días hábiles a tu domicilio
                    </p>
                </div>
            </div>
        </div>
    `;
}

async function loadRelatedProducts(category) {
    try {
        const response = await fetch(`${API_URL}/products?category=${category}&in_stock=true`, {
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!response.ok) throw new Error('Error al cargar productos relacionados');

        const data = await response.json();
        const related = (data.data || []).filter(p => p.id !== productId).slice(0, 4);
        
        const container = document.getElementById('related-products');
        
        if (related.length === 0) {
            container.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-slate-500">No hay productos relacionados</p></div>';
            return;
        }

        container.innerHTML = related.map(product => `
            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow overflow-hidden cursor-pointer"
                 onclick="window.location.href='/marketplace/${product.id}'">
                
                <div class="bg-slate-200 h-40 flex items-center justify-center overflow-hidden">
                    ${product.photo ? `<img src="${product.photo}" alt="${product.name}" class="w-full h-full object-cover">` : '<div class="text-3xl">📦</div>'}
                </div>

                <div class="p-4">
                    <h3 class="text-sm font-bold text-slate-900 mb-2 line-clamp-2">${product.name}</h3>
                    <p class="text-xl font-bold text-blue-600 mb-3">$${parseFloat(product.price).toFixed(2)}</p>
                    <button onclick="event.stopPropagation(); addToCart(${product.id})" 
                            class="w-full px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-semibold transition-colors">
                        🛒 Agregar
                    </button>
                </div>
            </div>
        `).join('');

    } catch (error) {
        console.error('Error cargando relacionados:', error);
    }
}

function addToCart(productId) {
    window.location.href = `/checkout?product_id=${productId}`;
}

// Cargar cuando el DOM está listo
document.addEventListener('DOMContentLoaded', loadProduct);
</script>
@endsection
