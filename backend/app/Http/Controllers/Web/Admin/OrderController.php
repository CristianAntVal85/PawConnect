<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with(['user:id,name', 'product:id,name,price', 'pet:id,name'])
            ->latest('id')
            ->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'product', 'pet', 'statusHistory']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,shipped,delivered,cancelled'],
            'note'   => ['nullable', 'string', 'max:500'],
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
