@extends('layouts.app')

@section('title', 'Checkout')

@section('page-title', 'Checkout')
@section('page-subtitle', 'Completa tu pedido')

@section('content')
<div class="max-w-4xl mx-auto py-4">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-slate-500 mb-6">
        <a href="{{ route('web.marketplace.index') }}" class="hover:text-slate-700">Marketplace</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Checkout</span>
    </nav>

    {{-- Sin producto seleccionado --}}
    @if(!$product)
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 text-center">
        <p class="text-amber-800 font-semibold text-lg mb-3">No has seleccionado ningún producto</p>
        <a href="{{ route('web.marketplace.index') }}"
           class="inline-flex px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
            ← Ir al marketplace
        </a>
    </div>
    @else

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        {{-- ─────────────────────────── Formulario (3 cols) ─── --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Producto seleccionado --}}
            <div class="bg-white rounded-xl shadow p-5 flex gap-4 items-start">
                <div class="w-20 h-20 shrink-0 bg-slate-100 rounded-lg flex items-center justify-center overflow-hidden">
                    @if($product->photo)
                        <img src="{{ $product->photo }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-3xl">📦</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-slate-500 mb-1">{{ $product->provider?->name }}</p>
                    <h3 class="font-bold text-slate-900 leading-tight">{{ $product->name }}</h3>
                    <p class="text-sm text-slate-600 capitalize mt-1">📂 {{ $product->category }}</p>
                    @if($product->stock > 0)
                        <p class="text-xs text-green-600 font-semibold mt-1">✓ {{ $product->stock }} en stock</p>
                    @else
                        <p class="text-xs text-red-600 font-semibold mt-1">✗ Sin stock</p>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    <p class="text-2xl font-bold text-blue-600">${{ number_format($product->price, 2) }}</p>
                    <p class="text-xs text-slate-500">por unidad</p>
                </div>
            </div>

            {{-- Formulario de pedido --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-5">
                <h2 class="text-lg font-bold text-slate-900 border-b pb-3">Detalles del pedido</h2>

                {{-- Mascota --}}
                <div>
                    <label for="pet-select" class="block text-sm font-semibold text-slate-700 mb-1">
                        ¿Para qué mascota?
                    </label>
                    @if($pets->isEmpty())
                        <p class="text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-lg p-3">
                            No tienes mascotas registradas.
                            <a href="{{ route('web.pets.create') }}" class="font-bold underline">Crea una aquí</a>
                        </p>
                    @else
                        <select id="pet-select"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($pets as $pet)
                                <option value="{{ $pet->id }}" {{ $selectedPet?->id === $pet->id ? 'selected' : '' }}>
                                    {{ $pet->name }}{{ $pet->breed ? ' (' . $pet->breed . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- Cantidad --}}
                <div>
                    <label for="quantity-input" class="block text-sm font-semibold text-slate-700 mb-1">
                        Cantidad
                    </label>
                    <div class="flex items-center gap-3">
                        <button type="button" id="qty-minus"
                                class="w-9 h-9 rounded-lg border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 flex items-center justify-center">−</button>
                        <input type="number" id="quantity-input" value="1" min="1" max="{{ $product->stock }}"
                               class="w-20 text-center px-2 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                        <button type="button" id="qty-plus"
                                class="w-9 h-9 rounded-lg border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 flex items-center justify-center">+</button>
                        <span class="text-sm text-slate-500">máx. {{ $product->stock }}</span>
                    </div>
                </div>

                {{-- Dirección de envío --}}
                <div>
                    <label for="shipping-address" class="block text-sm font-semibold text-slate-700 mb-1">
                        Dirección de envío
                    </label>
                    <input type="text" id="shipping-address"
                           placeholder="Ej: Calle Mayor 10, 1ºA, Madrid 28001"
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Pago con Stripe --}}
            <div class="bg-white rounded-xl shadow p-6 space-y-5">
                <h2 class="text-lg font-bold text-slate-900 border-b pb-3">
                    💳 Pago seguro
                    <span class="ml-2 text-xs font-normal text-slate-400">(modo test — tarjeta: 4242 4242 4242 4242)</span>
                </h2>

                <div id="stripe-element-container" class="p-3 border border-slate-300 rounded-lg min-h-[44px]">
                    <!-- Stripe Elements se monta aquí -->
                </div>

                <div id="payment-error" class="hidden text-red-600 text-sm bg-red-50 border border-red-200 rounded-lg p-3"></div>
                <div id="payment-success" class="hidden text-green-700 text-sm bg-green-50 border border-green-200 rounded-lg p-3"></div>
            </div>

        </div>

        {{-- ─────────────────────────── Resumen (2 cols) ─── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow p-6 sticky top-4">
                <h2 class="text-lg font-bold text-slate-900 border-b pb-3 mb-4">Resumen del pedido</h2>

                <div class="space-y-3 text-sm mb-6">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Producto</span>
                        <span class="font-medium text-slate-900 text-right max-w-[140px] truncate">{{ $product->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Precio unidad</span>
                        <span class="font-medium">${{ number_format($product->price, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Cantidad</span>
                        <span id="summary-qty" class="font-medium">1</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Envío</span>
                        <span class="font-medium text-green-600">Gratis</span>
                    </div>
                    <div class="flex justify-between text-base font-bold border-t pt-3">
                        <span>Total</span>
                        <span id="summary-total" class="text-blue-600">${{ number_format($product->price, 2) }}</span>
                    </div>
                </div>

                <button id="pay-btn"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl transition-colors"
                        {{ $product->stock == 0 || $pets->isEmpty() ? 'disabled' : '' }}>
                    🔒 Pagar ahora
                </button>

                <p class="text-xs text-slate-400 text-center mt-3">
                    Pago procesado de forma segura por Stripe
                </p>

                <div class="mt-4 flex justify-center gap-3 text-slate-300 text-2xl">
                    <span title="Visa">💳</span>
                    <span title="Mastercard">💳</span>
                    <span title="American Express">💳</span>
                </div>
            </div>
        </div>

    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const STRIPE_KEY   = @json($stripeKey);
    const PRODUCT_ID   = @json($product?->id);
    const UNIT_PRICE   = @json((float) ($product?->price ?? 0));
    const MAX_STOCK    = @json($product?->stock ?? 0);
    const API_BASE     = '/api/v1';

    if (!PRODUCT_ID) return; // sin producto, nada que hacer

    const stripe   = Stripe(STRIPE_KEY);
    const elements = stripe.elements();

    const cardEl = elements.create('card', {
        style: {
            base: { fontSize: '16px', color: '#1e293b', '::placeholder': { color: '#94a3b8' } },
            invalid: { color: '#ef4444' },
        },
    });
    cardEl.mount('#stripe-element-container');

    // ── Controles de cantidad ────────────────────────────────────
    const qtyInput  = document.getElementById('quantity-input');
    const summaryQty = document.getElementById('summary-qty');
    const summaryTotal = document.getElementById('summary-total');

    function updateSummary() {
        const qty   = parseInt(qtyInput.value) || 1;
        const total = (qty * UNIT_PRICE).toFixed(2);
        summaryQty.textContent   = qty;
        summaryTotal.textContent = '$' + total;
    }

    document.getElementById('qty-minus')?.addEventListener('click', () => {
        if (parseInt(qtyInput.value) > 1) { qtyInput.value--; updateSummary(); }
    });
    document.getElementById('qty-plus')?.addEventListener('click', () => {
        if (parseInt(qtyInput.value) < MAX_STOCK) { qtyInput.value++; updateSummary(); }
    });
    qtyInput?.addEventListener('input', updateSummary);

    // ── Pagar ────────────────────────────────────────────────────
    document.getElementById('pay-btn')?.addEventListener('click', handlePayment);

    async function handlePayment() {
        const btn = document.getElementById('pay-btn');
        const errBox = document.getElementById('payment-error');
        const okBox  = document.getElementById('payment-success');

        const petId    = document.getElementById('pet-select')?.value;
        const qty      = parseInt(qtyInput.value) || 1;
        const address  = document.getElementById('shipping-address')?.value?.trim();

        // Validaciones rápidas
        if (!petId)    return showError('Selecciona una mascota.');
        if (!address)  return showError('Introduce una dirección de envío.');
        if (qty < 1)   return showError('La cantidad debe ser al menos 1.');

        btn.disabled = true;
        btn.textContent = '⏳ Procesando…';
        errBox.classList.add('hidden');
        okBox.classList.add('hidden');

        try {
            // 1. Crear PaymentIntent en backend
            const piResponse = await apiFetch(`${API_BASE}/orders/payment-intent`, 'POST', {
                product_id: PRODUCT_ID,
                quantity:   qty,
            });

            if (!piResponse.ok) {
                const err = await piResponse.json();
                return showError(err.message || 'Error al iniciar el pago.');
            }

            const { client_secret } = await piResponse.json();

            // 2. Confirmar pago con Stripe.js
            const { error, paymentIntent } = await stripe.confirmCardPayment(client_secret, {
                payment_method: { card: cardEl },
            });

            if (error) {
                return showError(error.message);
            }

            // 3. Crear el pedido en backend con el payment_intent id
            const orderResponse = await apiFetch(`${API_BASE}/orders`, 'POST', {
                product_id:        PRODUCT_ID,
                pet_id:            parseInt(petId),
                quantity:          qty,
                shipping_address:  address,
                payment_method:    'card',
                stripe_payment_id: paymentIntent.id,
            });

            if (!orderResponse.ok) {
                const err = await orderResponse.json();
                return showError(err.message || 'Error al crear el pedido.');
            }

            const orderData = await orderResponse.json();
            const orderId   = orderData.data?.id;

            okBox.textContent = '✅ ¡Pago realizado con éxito! Redirigiendo a tu pedido…';
            okBox.classList.remove('hidden');

            setTimeout(() => {
                window.location.href = `/orders/${orderId}`;
            }, 1500);

        } catch (e) {
            showError('Error inesperado: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.textContent = '🔒 Pagar ahora';
        }
    }

    function showError(msg) {
        const errBox = document.getElementById('payment-error');
        errBox.textContent = '❌ ' + msg;
        errBox.classList.remove('hidden');
        document.getElementById('pay-btn').disabled  = false;
        document.getElementById('pay-btn').textContent = '🔒 Pagar ahora';
    }

    async function apiFetch(url, method, body) {
        const token = document.querySelector('meta[name="api-token"]')?.content;
        return fetch(url, {
            method,
            headers: {
                'Accept':        'application/json',
                'Content-Type':  'application/json',
                'Authorization': token ? `Bearer ${token}` : '',
            },
            body: JSON.stringify(body),
        });
    }
})();
</script>
@endpush
