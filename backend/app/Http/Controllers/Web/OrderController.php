<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pet;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Muestra la página de checkout para un producto.
     */
    public function checkout(Request $request)
    {
        $productId = $request->query('product_id');
        $petId     = $request->query('pet_id');

        $product = $productId ? Product::with('provider')->find($productId) : null;
        $pets    = auth()->user()->pets()->orderBy('name')->get();
        $pet     = $petId ? $pets->firstWhere('id', $petId) : $pets->first();

        return view('orders.checkout', [
            'product'        => $product,
            'pets'           => $pets,
            'selectedPet'    => $pet,
            'stripeKey'      => config('services.stripe.key'),
        ]);
    }

    /**
     * Lista todos los pedidos del usuario autenticado (Mis Pedidos).
     */
    public function index()
    {
        return view('orders.index');
    }

    /**
     * Muestra el detalle de un pedido con timeline de estados.
     */
    public function show($orderId)
    {
        return view('orders.show', ['orderId' => (int) $orderId]);
    }
}
