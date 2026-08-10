<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'message' => 'Lugares favoritos obtenidos correctamente.',
            'data'    => $favorites->items(),
            'pagination' => [
                'total'        => $favorites->total(),
                'per_page'     => $favorites->perPage(),
                'current_page' => $favorites->currentPage(),
                'last_page'    => $favorites->lastPage(),
            ],
        ]);
    }

    /**
     * Agregar lugar a favoritos.
     */
    public function store(Request $request, PetFriendlyPlace $place): JsonResponse
    {
        if ($place->status !== 'approved') {
            return response()->json([
                'message' => 'No puede agregar a favoritos un lugar no aprobado.',
            ], 422);
        }

        if ($request->user()->favoritePlaces()->where('place_id', $place->id)->exists()) {
            return response()->json([
                'message' => 'Este lugar ya está en tus favoritos.',
            ], 422);
        }

        $request->user()->favoritePlaces()->attach($place->id);

        return response()->json([
            'message' => 'Lugar agregado a favoritos.',
        ]);
    }

    /**
     * Eliminar lugar de favoritos.
     */
    public function destroy(Request $request, PetFriendlyPlace $place): JsonResponse
    {
        $request->user()->favoritePlaces()->detach($place->id);

        return response()->json([
            'message' => 'Lugar eliminado de favoritos.',
        ]);
    }
}
