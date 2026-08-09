<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Pet;
use App\Models\Product;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected RecommendationService $recommendationService
    ) {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $products = Product::with('provider')
            ->paginate(20);

        return response()->json([
            'message' => 'Productos obtenidos correctamente.',
            'data' => ProductResource::collection($products->items())->resolve(),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = Product::create($validated);
        $product->load('provider');

        return response()->json([
            'message' => 'Producto creado correctamente.',
            'data' => (new ProductResource($product))->resolve(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('provider');

        return response()->json([
            'message' => 'Producto obtenido correctamente.',
            'data' => (new ProductResource($product))->resolve(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();

        $product->update($validated);
        $product->load('provider');

        return response()->json([
            'message' => 'Producto actualizado correctamente.',
            'data' => (new ProductResource($product->fresh()))->resolve(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente.',
        ]);
    }

    /**
     * Obtiene productos recomendados para una mascota específica.
     */
    public function recommended(Request $request, Pet $pet): JsonResponse
    {
        // Verificar que la mascota pertenece al usuario autenticado
        if ($request->user()->id !== $pet->user_id) {
            return response()->json([
                'message' => 'No tienes permiso para ver recomendaciones de esta mascota.',
            ], 403);
        }

        $perPage = $request->input('per_page', 20);
        $products = $this->recommendationService->recommendForPet($pet, $perPage);

        return response()->json([
            'message' => "Productos recomendados para {$pet->name}.",
            'pet' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->species,
                'breed' => $pet->breed,
                'age' => $pet->age,
                'weight' => $pet->weight,
            ],
            'data' => ProductResource::collection($products->items())->resolve(),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }
}
