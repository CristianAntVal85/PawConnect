<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Pet;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['product:id,name,price', 'pet:id,name,user_id']);

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'message' => 'Pedidos obtenidos correctamente.',
            'data' => OrderResource::collection($orders)->resolve(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'pet_id' => ['required', 'exists:pets,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        $pet = Pet::query()->findOrFail((int) $validated['pet_id']);

        if (! $request->user()->isAdmin() && $pet->user_id !== $request->user()->id) {
            abort(403);
        }

        $product = Product::query()->findOrFail((int) $validated['product_id']);
        $quantity = (int) $validated['quantity'];
        $totalPrice = round(((float) $product->price) * $quantity, 2);

        $order = Order::query()->create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'pet_id' => $pet->id,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'shipping_address' => $validated['shipping_address'],
            'payment_method' => $validated['payment_method'] ?? 'card',
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Pedido creado correctamente.',
            'data' => (new OrderResource($order->fresh(['product:id,name,price', 'pet:id,name,user_id'])))->resolve(),
        ], 201, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if (! $request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'message' => 'Pedido obtenido correctamente.',
            'data' => (new OrderResource($order->load(['product:id,name,price', 'pet:id,name,user_id'])))->resolve(),
        ]);
    }
}
