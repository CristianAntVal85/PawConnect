<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PetController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Pet::class, 'pet');
    }

    /**
     * Listar mascotas. Por defecto, cada usuario ve solo las suyas.
     * Un admin puede usar ?scope=all para listar todas.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Pet::class);

        $query = Pet::query()->with('user:id,name,email');

        if (! $request->user()->isAdmin() || $request->query('scope') !== 'all') {
            $query->where('user_id', $request->user()->id);
        }

        $pets = $query->latest()->paginate(15);

        return response()->json([
            'message' => 'Mascotas obtenidas correctamente.',
            'data' => $pets,
        ]);
    }

    /**
     * Crear mascota.
     */
    public function store(StorePetRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $ownerId = $request->user()->id;

        if ($request->user()->isAdmin() && isset($validated['user_id'])) {
            $ownerId = (int) $validated['user_id'];
        }

        $pet = Pet::create([
            ...$validated,
            'user_id' => $ownerId,
        ]);

        return response()->json([
            'message' => 'Mascota creada correctamente.',
            'data' => $pet->load('user:id,name,email'),
        ], 201);
    }

    /**
     * Ver una mascota.
     */
    public function show(Pet $pet): JsonResponse
    {
        return response()->json([
            'message' => 'Mascota obtenida correctamente.',
            'data' => $pet->load('user:id,name,email'),
        ]);
    }

    /**
     * Actualizar mascota.
     */
    public function update(UpdatePetRequest $request, Pet $pet): JsonResponse
    {
        $pet->update($request->validated());

        return response()->json([
            'message' => 'Mascota actualizada correctamente.',
            'data' => $pet->fresh()->load('user:id,name,email'),
        ]);
    }

    /**
     * Eliminar mascota.
     */
    public function destroy(Pet $pet): JsonResponse
    {
        $pet->delete();

        return response()->json([
            'message' => 'Mascota eliminada correctamente.',
        ]);
    }
}
