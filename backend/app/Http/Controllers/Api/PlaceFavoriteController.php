<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\PetFriendlyPlace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceFavoriteController extends Controller
{
    /**
     * Obtener lugares favoritos del usuario.
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()
            ->favoritePlaces()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Lugares favoritos obtenidos correctamente.',
            'data' => PlaceResource::collection($favorites->items())->resolve(),
            'pagination' => [
                'total' => $favorites->total(),
                'per_page' => $favorites->perPage(),
                'current_page' => $favorites->currentPage(),
                'last_page' => $favorites->lastPage(),
            ],
        ]);
    }

    /**
     * Agregar lugar a favoritos.
     */
    public function store(Request $request, PetFriendlyPlace $place): JsonResponse
    {
        if ($place->status !== 'approved') {
            return $this->error('No puedes agregar a favoritos un lugar no aprobado', 403);
        }

        if ($request->user()->favoritePlaces()->where('place_id', $place->id)->exists()) {
            return $this->error('Este lugar ya está en tus favoritos', 409);
        }

        $request->user()->favoritePlaces()->attach($place->id);

        return $this->success(null, 'Lugar agregado a favoritos correctamente', 201);
    }

    /**
     * Eliminar lugar de favoritos.
     */
    public function destroy(Request $request, PetFriendlyPlace $place): JsonResponse
    {
        if (!$request->user()->favoritePlaces()->where('place_id', $place->id)->exists()) {
            return $this->error('Este lugar no está en tus favoritos', 404);
        }

        $request->user()->favoritePlaces()->detach($place->id);

        return $this->success(null, 'Lugar removido de favoritos correctamente');
    }
}
