@extends('layouts.app')

@section('title', 'Gestión de Pedidos')

@section('page-title', 'Admin')
@section('page-subtitle', 'Gestión de pedidos')

@section('content')
<div class="max-w-7xl mx-auto py-4">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Pedidos</h2>
        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
            Total: {{ $orders->total() }}
        </span>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Búsqueda</label>
                <input type="text" name="search" placeholder="ID, Tracking, Usuario..."
                       value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            @switch($status)
                                @case('pending') ⏳ Pendiente @break
                                @case('confirmed') ✅ Confirmado @break
                                @case('shipped') 🚚 Enviado @break
                                @case('delivered') 📦 Entregado @break
                                @case('cancelled') ❌ Cancelado @break
                            @endswitch
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('admin.orders.index') }}" class="flex-1 py-2 bg-slate-200 hover:bg-slate-300 text-center text-slate-900 font-semibold rounded-lg text-sm transition-colors">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    @if ($orders->count())
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-700">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-700">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-700">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-700">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-700">Tracking</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-700">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-700">Fecha</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-slate-700">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-mono font-bold text-blue-600">#{{ $order->id }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $order->user->name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900">{{ Str::limit($order->product->name, 20) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold
                                        @switch($order->status)
                                            @case('pending') bg-yellow-100 text-yellow-800 @break
                                            @case('confirmed') bg-blue-100 text-blue-800 @break
                                            @case('shipped') bg-purple-100 text-purple-800 @break
                                            @case('delivered') bg-green-100 text-green-800 @break
                                            @case('cancelled') bg-red-100 text-red-800 @break
                                        @endswitch
                                    ">
                                        @switch($order->status)
                                            @case('pending') ⏳ @break
                                            @case('confirmed') ✅ @break
                                            @case('shipped') 🚚 @break
                                            @case('delivered') 📦 @break
                                            @case('cancelled') ❌ @break
                                        @endswitch
                                        {{ Str::ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 font-mono">
                                    @if ($order->tracking_number)
                                        <span class="text-blue-600 font-semibold">{{ $order->tracking_number }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-blue-600">
                                    ${{ number_format($order->total_price, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $order->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                        Ver →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <p class="text-slate-500 text-lg">📭 No hay pedidos para mostrar.</p>
        </div>
    @endif

</div>
@endsection
