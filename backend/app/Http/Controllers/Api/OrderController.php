<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Pet;
use App\Models\Product;
use App\Services\PaymentService;
use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;

class OrderController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Order::query()
            ->with(['product:id,name,price,photo', 'pet:id,name,user_id', 'statusHistory'])
            ->latest();

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        $orders = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Pedidos obtenidos correctamente.',
            'data' => OrderResource::collection($orders->items())->resolve(),
            'pagination' => [
                'total'        => $orders->total(),
                'per_page'     => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Crea un PaymentIntent en Stripe y devuelve el client_secret al frontend.
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $product  = Product::query()->findOrFail((int) $validated['product_id']);
        $quantity = (int) $validated['quantity'];

        if ($product->stock < $quantity) {
            return $this->error("Stock insuficiente. Solo hay {$product->stock} unidades disponibles.", 422);
        }

        $totalCents = PaymentService::toCents((float) $product->price * $quantity);

        try {
            $intent = $this->paymentService->createPaymentIntent($totalCents, 'usd', [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'quantity'     => $quantity,
                'user_id'      => $request->user()->id,
            ]);
        } catch (ApiErrorException $e) {
            return $this->error('Error al crear la intención de pago: ' . $e->getMessage(), 502);
        }

        return $this->success([
            'client_secret' => $intent->clientSecret,
            'amount'        => $totalCents,
            'currency'      => 'usd',
            'product'       => [
                'id'    => $product->id,
                'name'  => $product->name,
                'price' => (float) $product->price,
            ],
        ], 'PaymentIntent creado correctamente.');
    }

    /**
     * Crea el pedido tras confirmar el pago en Stripe.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'        => ['required', 'exists:products,id'],
            'pet_id'            => ['required', 'exists:pets,id'],
            'quantity'          => ['required', 'integer', 'min:1'],
            'shipping_address'  => ['required', 'string', 'max:255'],
            'payment_method'    => ['nullable', 'string', 'max:50'],
            'stripe_payment_id' => ['nullable', 'string', 'max:255'],
        ]);

        $pet     = Pet::query()->findOrFail((int) $validated['pet_id']);
        $product = Product::query()->findOrFail((int) $validated['product_id']);
        $quantity = (int) $validated['quantity'];

        if (! $request->user()->isAdmin() && $pet->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($product->stock < $quantity) {
            return $this->error("Stock insuficiente. Solo hay {$product->stock} unidades disponibles.", 422);
        }

        // Verificar el pago en Stripe si se proporcionó un payment_id
        if (! empty($validated['stripe_payment_id'])) {
            $paymentSucceeded = $this->paymentService->isPaymentSucceeded($validated['stripe_payment_id']);
            if (! $paymentSucceeded) {
                return $this->error('El pago no ha sido confirmado. Por favor, completa el pago antes de crear el pedido.', 422);
            }
        }

        $totalPrice = round(((float) $product->price) * $quantity, 2);

        $order = DB::transaction(function () use ($request, $validated, $product, $pet, $quantity, $totalPrice) {
            // Reducir el stock del producto
            $product->decrement('stock', $quantity);

            $order = Order::query()->create([
                'user_id'           => $request->user()->id,
                'product_id'        => $product->id,
                'pet_id'            => $pet->id,
                'quantity'          => $quantity,
                'total_price'       => $totalPrice,
                'shipping_address'  => $validated['shipping_address'],
                'payment_method'    => $validated['payment_method'] ?? 'card',
                'stripe_payment_id' => $validated['stripe_payment_id'] ?? null,
                'status'            => 'pending',
            ]);

            // Crear historial de estado inicial
            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'status'   => 'pending',
                'note'     => 'Pedido creado correctamente.',
            ]);

            return $order;
        });

        $order->load(['product:id,name,price', 'pet:id,name,user_id', 'statusHistory']);

        return $this->success(
            (new OrderResource($order))->resolve(),
            'Pedido creado correctamente.',
            201
        );
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if (! $request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['product:id,name,price,photo', 'pet:id,name,user_id', 'statusHistory']);

        return $this->success(
            (new OrderResource($order))->resolve(),
            'Pedido obtenido correctamente.'
        );
    }

    /**
     * Actualiza el estado de un pedido (solo admin).
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Solo administradores pueden cambiar el estado de pedidos.');
        }

        $validated = $request->validate([
            'status'           => ['required', 'string', 'in:pending,confirmed,shipped,delivered,cancelled'],
            'note'             => ['nullable', 'string', 'max:500'],
            'tracking_number'  => ['nullable', 'string', 'max:100'],
            'tracking_url'     => ['nullable', 'url'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];
        $note      = $validated['note'] ?? null;

        // Si es el mismo estado y no hay cambios en tracking, no hacer nada
        if ($oldStatus === $newStatus && empty($validated['tracking_number'])) {
            return $this->success(
                (new OrderResource($order->load(['product:id,name,price', 'pet:id,name,user_id', 'statusHistory'])))->resolve(),
                'El pedido ya tiene ese estado.'
            );
        }

        // Cargar relaciones necesarias para la notificación
        $order->load(['product', 'pet', 'user']);

        DB::transaction(function () use ($order, $oldStatus, $newStatus, $note, $validated) {
            // Preparar datos a actualizar
            $updateData = ['status' => $newStatus];
            if (! empty($validated['tracking_number'])) {
                $updateData['tracking_number'] = $validated['tracking_number'];
            }
            if (! empty($validated['tracking_url'])) {
                $updateData['tracking_url'] = $validated['tracking_url'];
            }

            // Actualizar estado del pedido (e tracking si se proporciona)
            $order->update($updateData);

            // Registrar en historial
            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'status'   => $newStatus,
                'note'     => $note,
            ]);

            // Disparar evento de cambio de estado (para WebSocket)
            OrderStatusChanged::dispatch($order, $oldStatus, $newStatus, $note);
        });

        // Enviar notificación al usuario fuera de la transacción
        $order->user->notify(new OrderStatusNotification($order, $oldStatus, $newStatus, $note));

        $order->load(['product:id,name,price', 'pet:id,name,user_id', 'statusHistory']);

        return $this->success(
            (new OrderResource($order))->resolve(),
            'Estado del pedido actualizado correctamente.'
        );
    }
}
