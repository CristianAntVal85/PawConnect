<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::query()
            ->with(['user:id,name', 'product:id,name,price', 'pet:id,name'])
            ->latest('id');

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Búsqueda por ID de pedido, usuario o producto o tracking
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereAny([
                'id',
                'tracking_number'
            ], 'LIKE', "%{$search}%")
            ->orWhereHas('user', fn($q) => $q->where('name', 'LIKE', "%{$search}%"))
            ->orWhereHas('product', fn($q) => $q->where('name', 'LIKE', "%{$search}%"));
        }

        // Filtro por fecha (desde)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        // Filtro por fecha (hasta)
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $orders = $query->paginate(20);
        $statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function export()
    {
        return Excel::download(new OrdersExport(), 'pedidos_' . now()->format('Y-m-d_H-i-s') . '.csv');
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'product', 'pet', 'statusHistory']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status'           => ['required', 'string', 'in:pending,confirmed,shipped,delivered,cancelled'],
            'note'             => ['nullable', 'string', 'max:500'],
            'tracking_number'  => ['nullable', 'string', 'max:100'],
            'tracking_url'     => ['nullable', 'url'],
        ]);

        // Usar la API para actualizar (que dispara eventos y notificaciones)
        $token = auth()->user()->createToken('admin-order-update')->plainTextToken;

        $response = \Http::withToken($token)
            ->put(route('api.v1.orders.update', $order->id), $validated);

        return back()->with(
            $response->ok() ? 'success' : 'error',
            $response->ok()
                ? "Pedido actualizado a: {$validated['status']}"
                : 'Error al actualizar el pedido'
        );
    }
}
