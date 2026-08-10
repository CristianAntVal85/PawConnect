@extends('layouts.app')

@section('title', 'Mis Pedidos')

@section('page-title', 'Mis Pedidos')
@section('page-subtitle', 'Historial de compras')

@section('content')
<div class="max-w-4xl mx-auto py-4">

    {{-- Estado vacío --}}
    <div id="empty-orders" class="hidden text-center py-16">
        <div class="text-6xl mb-4">📭</div>
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Aún no tienes pedidos</h2>
        <p class="text-slate-500 mb-6">Explora el marketplace y haz tu primera compra</p>
        <a href="{{ route('web.marketplace.index') }}"
           class="inline-flex px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors">
            🛍️ Ir al marketplace
        </a>
    </div>

    {{-- Lista de pedidos --}}
    <div id="orders-list" class="space-y-4">
        <div class="text-center py-12">
            <p class="text-slate-400">Cargando pedidos…</p>
        </div>
    </div>

    {{-- Paginación --}}
    <div id="orders-pagination" class="mt-8 flex justify-center gap-2"></div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const API_BASE = '/api/v1';

    const STATUS_CONFIG = {
        pending:    { label: 'Pendiente',   color: 'bg-amber-100 text-amber-700',  icon: '⏳' },
        confirmed:  { label: 'Confirmado',  color: 'bg-blue-100 text-blue-700',    icon: '✅' },
        shipped:    { label: 'Enviado',     color: 'bg-indigo-100 text-indigo-700',icon: '🚚' },
        delivered:  { label: 'Entregado',   color: 'bg-green-100 text-green-700',  icon: '📦' },
        cancelled:  { label: 'Cancelado',   color: 'bg-red-100 text-red-700',      icon: '❌' },
    };

    async function loadOrders(page = 1) {
        const token = document.querySelector('meta[name="api-token"]')?.content;
        try {
            const res = await fetch(`${API_BASE}/orders?page=${page}`, {
                headers: {
                    'Accept':        'application/json',
                    'Authorization': `Bearer ${token}`,
                },
            });
            if (!res.ok) throw new Error();
            const data = await res.json();
            renderOrders(data.data || []);
            renderPagination(data.pagination || {});
        } catch {
            document.getElementById('orders-list').innerHTML =
                '<div class="text-center py-12 text-red-500">❌ Error al cargar los pedidos.</div>';
        }
    }

    function renderOrders(orders) {
        const list  = document.getElementById('orders-list');
        const empty = document.getElementById('empty-orders');

        if (!orders.length) {
            list.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');
        list.innerHTML = orders.map(order => {
            const st = STATUS_CONFIG[order.status] ?? { label: order.status, color: 'bg-slate-100 text-slate-700', icon: '•' };
            const date = new Date(order.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });

            return `
            <div class="bg-white rounded-xl shadow hover:shadow-md transition-shadow overflow-hidden cursor-pointer"
                 onclick="window.location.href='/orders/${order.id}'">
                <div class="p-5 flex gap-4 items-start">
                    {{-- Imagen --}}
                    <div class="w-16 h-16 shrink-0 bg-slate-100 rounded-lg flex items-center justify-center overflow-hidden">
                        ${order.product?.photo
                            ? `<img src="${order.product.photo}" class="w-full h-full object-cover">`
                            : '<span class="text-2xl">📦</span>'}
                    </div>
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-slate-900 truncate">${order.product?.name ?? 'Producto'}</h3>
                                <p class="text-sm text-slate-500">${order.pet?.name ?? ''} · ${order.quantity} ud.</p>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold shrink-0 ${st.color}">
                                ${st.icon} ${st.label}
                            </span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-sm">
                            <span class="text-slate-500">${date}</span>
                            <span class="font-bold text-blue-600 text-base">$${parseFloat(order.total_price).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
                {{-- Barra de progreso --}}
                <div class="h-1 ${order.status === 'cancelled' ? 'bg-red-300' : 'bg-gradient-to-r from-blue-400 to-green-400'}"
                     style="width: ${progressWidth(order.status)}"></div>
            </div>`;
        }).join('');
    }

    function progressWidth(status) {
        const map = { pending: '20%', confirmed: '40%', shipped: '70%', delivered: '100%', cancelled: '100%' };
        return map[status] ?? '10%';
    }

    function renderPagination(pagination) {
        const container = document.getElementById('orders-pagination');
        if (!pagination.last_page || pagination.last_page <= 1) { container.innerHTML = ''; return; }

        let html = '';
        for (let i = 1; i <= pagination.last_page; i++) {
            html += `<button onclick="loadOrders(${i})"
                        class="px-3 py-2 rounded-lg ${i === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-900 hover:bg-slate-300'} transition-colors">
                        ${i}
                     </button>`;
        }
        container.innerHTML = html;
    }

    document.addEventListener('DOMContentLoaded', () => loadOrders(1));
})();
</script>
@endpush
