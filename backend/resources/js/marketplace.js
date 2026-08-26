// ────────────────────────────────────────────────────────────────
// MARKETPLACE - Filtros y Recomendaciones
// ────────────────────────────────────────────────────────────────

// Estado global
let currentProductsPage = 1;
let currentRecommendationsPage = 1;
let currentFilters = {
    category: '',
    maxPrice: 1000,
    breed: '',
    inStock: true,
    page: 1,
};

const API_URL = '/api/v1';
const token = document.querySelector('meta[name="csrf-token"]')?.content;

// ────────────────────────────────────────────────────────────────
// 1. Cargar productos del catálogo
// ────────────────────────────────────────────────────────────────

async function loadProducts(page = 1) {
    try {
        const params = new URLSearchParams({
            page: page,
            in_stock: currentFilters.inStock ? 'true' : 'false',
        });

        if (currentFilters.category) {
            params.append('category', currentFilters.category);
        }

        if (currentFilters.maxPrice < 1000) {
            params.append('max_price', currentFilters.maxPrice);
        }

        if (currentFilters.breed) {
            params.append('breed', currentFilters.breed);
        }

        const url = `${API_URL}/products?${params.toString()}`;
        console.log('Fetching products from:', url);

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        console.log('Products data:', data);
        renderProducts(data.data || []);
        renderProductsPagination(data.pagination || {});

    } catch (error) {
        console.error('Error loading products:', error);
        showError('Error al cargar productos: ' + error.message, 'products-container');
    }
}

// ────────────────────────────────────────────────────────────────
// 2. Renderizar productos en el grid
// ────────────────────────────────────────────────────────────────

function renderProducts(products) {
    const container = document.getElementById('products-container');
    const count = document.getElementById('products-count');

    if (!products || products.length === 0) {
        container.innerHTML = `
            <div class="col-span-full py-12 text-center">
                <p class="text-slate-500 text-lg">📭 No se encontraron productos con estos filtros</p>
            </div>
        `;
        count.textContent = '0';
        return;
    }

    count.textContent = products.length;

    container.innerHTML = products.map(product => `
        <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow overflow-hidden group cursor-pointer flex flex-col h-full"
             onclick="window.location.href='/marketplace/${product.id}'">

            <!-- Imagen -->
            <div class="relative bg-slate-200 h-48 flex items-center justify-center overflow-hidden">
                ${product.photo ? `<img src="${product.photo}" alt="${product.name}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">` : '<div class="text-4xl">📦</div>'}
            </div>

            <!-- Contenido -->
            <div class="p-4 flex flex-col flex-1 relative">
                <p class="text-xs text-slate-500 mb-1">${product.provider?.name || 'Proveedor desconocido'}</p>

                <h3 class="text-sm font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                    ${product.name}
                </h3>

                <p class="text-xs text-slate-600 mb-2 capitalize">
                    📂 ${product.category || 'General'}
                </p>

                ${product.min_weight_kg || product.max_weight_kg ? `
                    <p class="text-xs text-slate-600 mb-2 text-amber-700 bg-amber-50 px-2 py-1 rounded">
                        ⚖️ Peso: ${product.min_weight_kg || '0'}kg - ${product.max_weight_kg || '∞'}kg
                    </p>
                ` : ''}

                ${product.recommended_breeds?.length ? `
                    <p class="text-xs text-slate-600 mb-2">
                        🐕 ${Array.isArray(product.recommended_breeds) ? product.recommended_breeds.join(', ') : product.recommended_breeds}
                    </p>
                ` : ''}

                <div class="mb-3">
                    ${product.stock > 0
                        ? `<span class="text-xs font-semibold text-green-600">✓ En stock (${product.stock})</span>`
                        : `<span class="text-xs font-semibold text-red-600">✗ Sin stock</span>`
                    }
                </div>

                <div class="flex justify-between items-center pt-3 border-t border-slate-200 mt-auto mb-4">
                    <div class="text-xl font-bold text-blue-600">
                        $${parseFloat(product.price).toFixed(2)}
                    </div>
                    <button class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-semibold transition-colors"
                            onclick="event.stopPropagation(); addToCart(${product.id})">
                        🛒 Agregar
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// ────────────────────────────────────────────────────────────────
// 3. Cargar recomendaciones personalizadas
// ────────────────────────────────────────────────────────────────

async function loadRecommendations(petId, page = 1) {
    if (!petId) {
        const container = document.getElementById('recommendations-container');
        container.innerHTML = `
            <div class="col-span-full py-12 text-center">
                <p class="text-slate-500">Selecciona una mascota para ver recomendaciones personalizadas</p>
            </div>
        `;
        return;
    }

    try {
        const response = await fetch(`${API_URL}/products/recommended/${petId}?page=${page}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`,
            }
        });

        if (!response.ok) {
            throw new Error(response.status === 403 ? 'No tienes permiso' : 'Error al cargar recomendaciones');
        }

        const data = await response.json();
        renderRecommendations(data.data || [], data.pet);
        renderRecommendationsPagination(data.pagination || {});

    } catch (error) {
        console.error('Error:', error);
        showError('Error al cargar recomendaciones', 'recommendations-container');
    }
}

// ────────────────────────────────────────────────────────────────
// 4. Renderizar recomendaciones
// ────────────────────────────────────────────────────────────────

function renderRecommendations(products, pet = null) {
    const container = document.getElementById('recommendations-container');

    if (!products || products.length === 0) {
        container.innerHTML = `
            <div class="col-span-full py-12 text-center">
                <p class="text-slate-500">
                    ${pet ? `No hay recomendaciones disponibles para ${pet.name}` : 'Selecciona una mascota'}
                </p>
            </div>
        `;
        return;
    }

    container.innerHTML = products.map(product => `
        <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow overflow-hidden group cursor-pointer flex flex-col h-full"
             onclick="window.location.href='/marketplace/${product.id}'">

            <!-- Imagen -->
            <div class="relative bg-slate-200 h-48 flex items-center justify-center overflow-hidden">
                ${product.photo ? `<img src="${product.photo}" alt="${product.name}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">` : '<div class="text-4xl">📦</div>'}
                <div class="absolute top-2 left-2 bg-amber-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                    ⭐ Recomendado
                </div>
            </div>

            <!-- Contenido -->
            <div class="p-4 flex flex-col flex-1 relative">
                <p class="text-xs text-slate-500 mb-1">${product.provider?.name || 'Proveedor'}</p>

                <h3 class="text-sm font-bold text-slate-900 mb-2 line-clamp-2">
                    ${product.name}
                </h3>

                <p class="text-xs text-slate-600 mb-2 capitalize">
                    📂 ${product.category || 'General'}
                </p>

                <div class="mb-3">
                    ${product.stock > 0
                        ? `<span class="text-xs font-semibold text-green-600">✓ Disponible</span>`
                        : `<span class="text-xs font-semibold text-red-600">✗ Sin stock</span>`
                    }
                </div>

                <div class="flex justify-between items-center pt-3 border-t border-slate-200 mt-auto mb-4">
                    <div class="text-xl font-bold text-amber-600">
                        $${parseFloat(product.price).toFixed(2)}
                    </div>
                    <button class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-semibold transition-colors"
                            onclick="event.stopPropagation(); addToCart(${product.id})">
                        🛒
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// ────────────────────────────────────────────────────────────────
// 5. Renderizar paginación
// ────────────────────────────────────────────────────────────────

function renderProductsPagination(pagination) {
    const container = document.getElementById('products-pagination');
    if (!pagination.last_page || pagination.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    for (let i = 1; i <= pagination.last_page; i++) {
        html += `
            <button
                onclick="loadProducts(${i})"
                class="px-3 py-2 rounded-lg ${i === pagination.current_page
                    ? 'bg-blue-500 text-white'
                    : 'bg-slate-200 text-slate-900 hover:bg-slate-300'} transition-colors"
            >
                ${i}
            </button>
        `;
    }
    container.innerHTML = html;
}

function renderRecommendationsPagination(pagination) {
    const container = document.getElementById('recommendations-pagination');
    if (!pagination.last_page || pagination.last_page <= 1) {
        container.classList.add('hidden');
        return;
    }

    container.classList.remove('hidden');
    const petId = document.getElementById('pet-select').value;

    let html = '';
    for (let i = 1; i <= pagination.last_page; i++) {
        html += `
            <button
                onclick="loadRecommendations(${petId}, ${i})"
                class="px-3 py-2 rounded-lg ${i === pagination.current_page
                    ? 'bg-amber-500 text-white'
                    : 'bg-slate-200 text-slate-900 hover:bg-slate-300'} transition-colors"
            >
                ${i}
            </button>
        `;
    }
    container.innerHTML = html;
}

// ────────────────────────────────────────────────────────────────
// 6. Manejo de Filtros
// ────────────────────────────────────────────────────────────────

// Filtro de categoría
document.getElementById('filter-category')?.addEventListener('change', (e) => {
    currentFilters.category = e.target.value;
    loadProducts(1);
});

// Filtro de precio
document.getElementById('filter-price')?.addEventListener('input', (e) => {
    currentFilters.maxPrice = parseInt(e.target.value);
    document.getElementById('price-display').textContent =
        currentFilters.maxPrice >= 1000 ? '$1000+' : `$${currentFilters.maxPrice}`;
    loadProducts(1);
});

// Filtro de raza
document.getElementById('filter-breed')?.addEventListener('input', (e) => {
    currentFilters.breed = e.target.value;
    loadProducts(1);
});

// Filtro de disponibilidad
document.getElementById('filter-in-stock')?.addEventListener('change', (e) => {
    currentFilters.inStock = e.target.checked;
    loadProducts(1);
});

// Limpiar filtros
document.getElementById('reset-filters')?.addEventListener('click', () => {
    currentFilters = {
        category: '',
        maxPrice: 1000,
        breed: '',
        inStock: true,
        page: 1,
    };

    document.getElementById('filter-category').value = '';
    document.getElementById('filter-price').value = '1000';
    document.getElementById('filter-breed').value = '';
    document.getElementById('filter-in-stock').checked = true;
    document.getElementById('price-display').textContent = '$1000+';

    loadProducts(1);
});

// Ordenamiento
document.getElementById('sort-by')?.addEventListener('change', (e) => {
    // Nota: la API actual no soporta ordenamiento en el backend.
    // Aquí se puede implementar ordenamiento en cliente si es necesario.
    console.log('Ordenar por:', e.target.value);
});

// ────────────────────────────────────────────────────────────────
// 7. Selector de Mascotas para Recomendaciones
// ────────────────────────────────────────────────────────────────

document.getElementById('pet-select')?.addEventListener('change', (e) => {
    const petId = e.target.value;
    if (petId) {
        loadRecommendations(petId, 1);
    } else {
        document.getElementById('recommendations-container').innerHTML = `
            <div class="col-span-full py-12 text-center">
                <p class="text-slate-500">Selecciona una mascota para ver recomendaciones personalizadas</p>
            </div>
        `;
    }
});

// ────────────────────────────────────────────────────────────────
// 8. Utilidades
// ────────────────────────────────────────────────────────────────

function getAuthToken() {
    return document.querySelector('meta[name="auth-token"]')?.content || '';
}

function showError(message, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = `
        <div class="col-span-full py-12 text-center">
            <p class="text-red-500 text-lg">❌ ${message}</p>
        </div>
    `;
}

function addToCart(productId) {
    window.location.href = `/checkout?product_id=${productId}`;
}

// ────────────────────────────────────────────────────────────────
// 9. Inicialización
// ────────────────────────────────────────────────────────────────

function initMarketplace() {
    const productsContainer = document.getElementById('products-container');
    if (productsContainer) {
        console.log('Inicializando marketplace...');
        loadProducts(1);
    }
}

// Si el documento ya está listo, ejecutar inmediatamente
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMarketplace);
} else {
    initMarketplace();
}

// Exponer funciones globalmente para uso en atributos onclick del HTML
window.loadProducts = loadProducts;
window.loadRecommendations = loadRecommendations;
window.addToCart = addToCart;
window.applyFilters = applyFilters;
