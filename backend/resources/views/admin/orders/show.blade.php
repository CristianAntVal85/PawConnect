@extends('layouts.app')

@section('title', 'Gestión de Pedido')

@section('page-title', 'Admin')
@section('page-subtitle', 'Detalles del pedido')

@section('content')
<div class="max-w-4xl mx-auto py-4">

    <nav class="text-sm text-slate-500 mb-6">
        <a href="{{ route('admin.orders.index') }}" class="hover:text-slate-700">Pedidos</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Pedido #{{ $order->id }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Información principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Cliente --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">👤 Cliente</h2>
                <p class="text-slate-900 font-medium">{{ $order->user->name }}</p>
                <p class="text-slate-600 text-sm">{{ $order->user->email }}</p>
            </div>

            {{-- Producto --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">📦 Producto</h2>
                <div class="flex gap-4">
                    <div class="w-16 h-16 shrink-0 bg-slate-100 rounded-lg flex items-center justify-center overflow-hidden">
                        @if ($order->product->photo)
                            <img src="{{ $order->product->photo }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl">📦</span>
                        @endif
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900">{{ $order->product->name }}</p>
                        <p class="text-sm text-slate-600">Cantidad: {{ $order->quantity }} ud.</p>
                        <p class="text-sm font-semibold text-blue-600 mt-1">${{ number_format($order->product->price, 2) }}</p>
                    </div>
                </div>
            </div>

            {{-- Envío --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">🚚 Envío</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Dirección</p>
                        <p class="text-slate-900 font-medium">{{ $order->shipping_address }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Mascota</p>
                        <p class="text-slate-900 font-medium">{{ $order->pet->name }}</p>
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            @if ($order->statusHistory->count())
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">📋 Historial</h2>
                    <ol class="relative ml-1 space-y-6">
                        @foreach ($order->statusHistory as $history)
                            <li class="relative">
                                <div class="absolute left-0 top-2 w-5 h-5 rounded-full bg-white border-4 border-blue-500 flex items-center justify-center text-xs font-bold">
                                    @switch($history->status)
                                        @case('pending')
                                            ⏳
                                            @break
                                        @case('confirmed')
                                            ✅
                                            @break
                                        @case('shipped')
                                            🚚
                                            @break
                                        @case('delivered')
                                            📦
                                            @break
                                        @case('cancelled')
                                            ❌
                                            @break
                                        @default
                                            •
                                    @endswitch
                                </div>
                                <div class="ml-8 bg-slate-50 rounded-lg p-4 border border-slate-200">
                                    <p class="font-semibold text-slate-900 text-sm capitalize">{{ $history->status }}</p>
                                    @if ($history->note)
                                        <p class="text-slate-600 text-xs mt-1">💬 {{ $history->note }}</p>
                                    @endif
                                    <p class="text-xs text-slate-400 mt-2">{{ $history->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

        </div>

        {{-- Sidebar: cambiar estado --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                <h2 class="text-lg font-bold text-slate-900 mb-4">🔄 Cambiar Estado</h2>

                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-2">Estado</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pending" @selected($order->status === 'pending')>⏳ Pendiente</option>
                            <option value="confirmed" @selected($order->status === 'confirmed')>✅ Confirmado</option>
                            <option value="shipped" @selected($order->status === 'shipped')>🚚 Enviado</option>
                            <option value="delivered" @selected($order->status === 'delivered')>📦 Entregado</option>
                            <option value="cancelled" @selected($order->status === 'cancelled')>❌ Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <label for="note" class="block text-sm font-medium text-slate-700 mb-2">Nota (opcional)</label>
                        <textarea id="note" name="note" rows="3"
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Ej: Enviado por FedEx, tracking #123..."></textarea>
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                        Actualizar estado
                    </button>
                </form>

                {{-- Resumen --}}
                <div class="mt-6 pt-6 border-t border-slate-200">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Total</p>
                            <p class="text-2xl font-bold text-blue-600">${{ number_format($order->total_price, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">ID de pago</p>
                            <p class="text-xs font-mono text-slate-600 truncate" title="{{ $order->stripe_payment_id }}">
                                {{ $order->stripe_payment_id }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Fecha del pedido</p>
                            <p class="text-sm text-slate-900">{{ $order->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Botones de acción --}}
    <div class="mt-6 flex gap-3">
        <a href="{{ route('admin.orders.index') }}"
           class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-900 font-semibold rounded-lg transition-colors">
            ← Volver
        </a>
    </div>

</div>
@endsection
