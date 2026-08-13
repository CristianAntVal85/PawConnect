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
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('provider');

        if ($category = $request->query('category')) {
            $query->where('category', 'like', "%{$category}%");
        }

        if ($maxPrice = $request->query('max_price')) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        if ($breed = $request->query('breed')) {
            $query->whereJsonContains('recommended_breeds', $breed);
        }

        if ($request->query('in_stock') === 'true') {
            $query->where('stock', '>', 0);
        }

        $products = $query->paginate(20);

        return response()->json([
            'success' => true,
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

        return $this->success(
            (new ProductResource($product))->resolve(),
            'Producto creado correctamente.',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('provider');

        return $this->success(
            (new ProductResource($product))->resolve(),
            'Producto obtenido correctamente.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();

        $product->update($validated);
        $product->load('provider');

        return $this->success(
            (new ProductResource($product->fresh()))->resolve(),
            'Producto actualizado correctamente.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->success(null, 'Producto eliminado correctamente.');
    }

    /**
     * Obtiene productos recomendados para una mascota específica.
     */
    public function recommended(Request $request, Pet $pet): JsonResponse
    {
        // Verificar que la mascota pertenece al usuario autenticado
        if ($request->user()->id !== $pet->user_id) {
            return $this->error('No tienes permiso para ver recomendaciones de esta mascota.', 403);
        }

        $perPage = $request->input('per_page', 20);
        $products = $this->recommendationService->recommendForPet($pet, $perPage);

        return response()->json([
            'success' => true,
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

    /**
     * Obtiene productos relacionados con un tipo de servicio específico.
     *
     * Utilizado en contexto de:
     * - Registros sanitarios (vacunación, cirugía, etc.)
     * - Visitas a veterinarios
     * - Tipos de lugares (parque, playa, etc.)
     */
    public function byServiceType(Request $request, string $serviceType, int $petId): JsonResponse
    {
        // Buscar la mascota
        $pet = Pet::find($petId);
        if (!$pet) {
            return $this->error('Mascota no encontrada.', 404);
        }

        // Verificar que la mascota pertenece al usuario autenticado
        if ($request->user()->id !== $pet->user_id) {
            return $this->error('No tienes permiso para ver recomendaciones de esta mascota.', 403);
        }

        $perPage = $request->input('per_page', 6);
        $products = $this->recommendationService->getProductsByServiceType($pet, $serviceType, $perPage);

        $serviceLabel = $this->recommendationService->getServiceTypeLabel($serviceType);
        $emoji = $this->recommendationService->getServiceTypeEmoji($serviceType);

        return response()->json([
            'success' => true,
            'message' => "Productos relacionados con {$serviceLabel} para {$pet->name}.",
            'service' => [
                'type' => $serviceType,
                'label' => $serviceLabel,
                'emoji' => $emoji,
            ],
            'pet' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->species,
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

    /**
     * Devuelve la combinación óptima de pastillas antiparasitarias para cubrir el peso del animal.
     *
     * Útil cuando al usuario se le notifica que la desparasitación vence en 7 días:
     * si el perro pesa 35 kg y hay pastillas de 20/10/5 kg, devuelve [1×20kg, 1×10kg, 1×5kg].
     */
    public function dewormingCombo(Request $request, int $petId): JsonResponse
    {
        $pet = Pet::find($petId);
        if (!$pet) {
            return $this->error('Mascota no encontrada.', 404);
        }

        if ($request->user()->id !== $pet->user_id) {
            return $this->error('No tienes permiso para ver recomendaciones de esta mascota.', 403);
        }

        $result = $this->recommendationService->getDewormingCombination($pet);

        $items = collect($result['items'])->map(fn ($item) => [
            'product' => (new ProductResource($item['product']))->resolve(),
            'quantity' => $item['quantity'],
            'subtotal' => round($item['quantity'] * (float) $item['product']->price, 2),
        ])->values()->all();

        return $this->success([
            'combination' => $items,
            'total_dosage_kg' => $result['total_dosage_kg'],
            'covered' => $result['covered'],
            'pet' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->species,
                'weight_kg' => (float) $pet->weight,
            ],
        ], $result['covered']
            ? "Combinación de pastillas para {$pet->name} ({$pet->weight} kg)."
            : "No se encontraron pastillas suficientes para cubrir el peso de {$pet->name}.");
    }
}
