<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    /**
     * Muestra el marketplace con productos y filtros.
     */
    public function index(Request $request)
    {
        // Obtener mascotas del usuario para la sección de recomendaciones
        $pets = auth()->user()->pets()->where('species', 'dog')->get();
        
        // Categorías disponibles para filtrar
        $categories = [
            'comida' => 'Comida',
            'accesorios' => 'Accesorios',
            'juguetes' => 'Juguetes',
            'higiene' => 'Higiene',
            'estetica' => 'Estética',
            'salud' => 'Salud',
            'multiespecie' => 'Multiespecie',
        ];

        return view('marketplace.index', [
            'pets' => $pets,
            'categories' => $categories,
        ]);
    }

    /**
     * Muestra el detalle de un producto.
     */
    public function show($productId)
    {
        return view('marketplace.show', [
            'productId' => $productId,
        ]);
    }
}
