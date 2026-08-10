@extends('layouts.app')

@section('title', 'Detalle del Pedido')

@section('page-title', 'Pedido')
@section('page-subtitle', 'Detalle y tracking')

@section('content')
<div class="max-w-3xl mx-auto py-4">

    <nav class="text-sm text-slate-500 mb-6">
        <a href="{{ route('web.orders.index') }}" class="hover:text-slate-700">Mis pedidos</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Pedido #{{ $orderId }}</span>
    </nav>

    <div id="order-container">
        <div class="text-center py-16">
            <p class="text-slate-400">Cargando pedido…</p>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const ORDER_ID = {{ $orderId }};
    const API_BASE = '/api/v1';

    const STATUS_CONFIG = {
        pending:    { label: 'Pendiente',   icon: '⏳', desc: 'Tu pedido está siendo procesado' },
        confirmed:  { label: 'Confirmado',  icon: '✅', desc: 'Tu pedido ha sido confirmado y está siendo preparado' },
        shipped:    { label: 'Enviado',     icon: '🚚', desc: 'Tu pedido está en camino' },
        delivered:  { label: 'Entregado',   icon: '📦', desc: '¡Tu pedido ha llegado!' },
        cancelled:  { label: 'Cancelado',   icon: '❌', desc: 'Este pedido ha sido cancelado' },
    };

    const STATUS_ORDER = ['pending', 'confirmed', 'shipped', 'delivered'];

    async function loadOrder() {
        const token = document.querySelector('meta[name="api-token"]')?.content;
        try {
            const res = await fetch(`${API_BASE}/orders/${ORDER_ID}`, {
                headers: {
                    'Accept':        'application/json',
                    'Authorization': `Bearer ${token}`,
                },
            });
            if (res.status === 403 || res.status === 404) {
                document.getElementById('order-container').innerHTML =
                    '<div class="text-center py-16"><p class="text-slate-500">Pedido no encontrado.</p></div>';
                return;
            }
            if (!res.ok) throw new Error();
            const data = await res.json();
            renderOrder(data.data);
        } catch {
            document.getElementById('order-container').innerHTML =
                '<div class="text-center py-16 text-red-500">❌ Error al cargar el pedido.</div>';
        }
    }

    function renderOrder(order) {
        const st   = STATUS_CONFIG[order.status] ?? { label: order.status, icon: '•', desc: '' };
        const date = new Date(order.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });
        const isCancelled = order.status === 'cancelled';

        // Progreso de pasos
        const stepIdx   = isCancelled ? -1 : STATUS_ORDER.indexOf(order.status);
        const stepsHtml = STATUS_ORDER.map((s, i) => {
            const done    = i <= stepIdx;
            const current = i === stepIdx;
            return `
            <div class="flex flex-col items-center gap-1 flex-1">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm
                     ${done ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-400'}
                     ${current ? 'ring-4 ring-blue-200' : ''}">
                    ${done ? STATUS_CONFIG[s].icon : (i + 1)}
                </div>
                <span class="text-xs font-medium ${done ? 'text-blue-700' : 'text-slate-400'} hidden sm:block">
                    ${STATUS_CONFIG[s].label}
                </span>
            </div>
            ${i < STATUS_ORDER.length - 1 ? `<div class="flex-1 h-1 mt-4 rounded-full ${i < stepIdx ? 'bg-blue-400' : 'bg-slate-200'}"></div>` : ''}`;
        }).join('');

        document.getElementById('order-container').innerHTML = `
        <div class="space-y-6">

            {{-- Estado actual --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-slate-900">Estado del pedido</h2>
                    <span class="text-3xl">${st.icon}</span>
                </div>
                <p class="text-xl font-bold ${isCancelled ? 'text-red-600' : 'text-blue-700'} mb-1">${st.label}</p>
                <p class="text-slate-500 text-sm mb-6">${st.desc}</p>

                ${!isCancelled ? `
                <div class="flex items-center gap-0">
                    ${stepsHtml}
                </div>
                ` : `<div class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm">
                    Este pedido fue cancelado. Si tienes dudas, contacta con soporte.
                </div>`}
            </div>

            {{-- Producto + resumen --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-bold text-slate-900 border-b pb-3 mb-4">Resumen del pedido #${order.id}</h2>

                <div class="flex gap-4 items-start mb-5">
                    <div class="w-20 h-20 shrink-0 bg-slate-100 rounded-lg flex items-center justify-center overflow-hidden">
                        ${order.product?.photo
                            ? `<img src="${order.product.photo}" class="w-full h-full object-cover">`
                            : '<span class="text-3xl">📦</span>'}
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-slate-900">${order.product?.name ?? 'Producto'}</h3>
                        <p class="text-sm text-slate-500">Para: ${order.pet?.name ?? 'N/A'}</p>
                        <p class="text-sm text-slate-500">${order.quantity} ud. × $${parseFloat(order.product?.price ?? 0).toFixed(2)}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-blue-600">$${parseFloat(order.total_price).toFixed(2)}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm border-t pt-4">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Dirección de envío</p>
                        <p class="font-medium text-slate-900">${order.shipping_address}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Método de pago</p>
                        <p class="font-medium text-slate-900 capitalize">💳 ${order.payment_method}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Fecha del pedido</p>
                        <p class="font-medium text-slate-900">${date}</p>
                    </div>
                    ${order.stripe_payment_id ? `
                    <div>
                        <p class="text-xs text-slate-500 mb-1">ID de pago</p>
                        <p class="font-mono text-xs text-slate-600">${order.stripe_payment_id}</p>
                    </div>` : ''}
                </div>
            </div>

            {{-- Timeline de estados --}}
            ${order.status_history?.length ? `
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-bold text-slate-900 border-b pb-3 mb-4">📋 Historial de estados</h2>
                <ol class="relative border-l border-slate-200 ml-3 space-y-4">
                    ${order.status_history.map(h => {
                        const hst = STATUS_CONFIG[h.status] ?? { label: h.status, icon: '•' };
                        const hdate = new Date(h.created_at).toLocaleString('es-ES', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
                        return `
                        <li class="pl-6">
                            <span class="absolute -left-3 flex w-6 h-6 items-center justify-center bg-white border-2 border-blue-400 rounded-full text-xs">
                                ${hst.icon}
                            </span>
                            <div class="bg-slate-50 rounded-lg p-3">
                                <p class="font-semibold text-slate-900 text-sm">${hst.label}</p>
                                ${h.note ? `<p class="text-slate-600 text-xs mt-0.5">${h.note}</p>` : ''}
                                <p class="text-xs text-slate-400 mt-1">${hdate}</p>
                            </div>
                        </li>`;
                    }).join('')}
                </ol>
            </div>` : ''}

            {{-- Acciones --}}
            <div class="flex gap-3">
                <a href="{{ route('web.orders.index') }}"
                   class="flex-1 text-center py-3 bg-slate-200 hover:bg-slate-300 text-slate-900 font-semibold rounded-xl transition-colors">
                    ← Mis pedidos
                </a>
                <a href="{{ route('web.marketplace.index') }}"
                   class="flex-1 text-center py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                    🛍️ Seguir comprando
                </a>
            </div>

        </div>`;
    }

    document.addEventListener('DOMContentLoaded', loadOrder);
})();
</script>
@endpush
