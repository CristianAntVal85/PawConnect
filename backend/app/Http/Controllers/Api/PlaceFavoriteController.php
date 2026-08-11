<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\PetFriendlyPlace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PlaceFavoriteController extends Controller
{
    /**
     * Obtener lugares favoritos del usuario.
     *
     * @return ResourceCollection
     */
    public function index(Request $request): ResourceCollection
    {
        $favorites = $request->user()
            ->favoritePlaces()
            ->paginate(20);

        return PlaceResource::collection($favorites);
    }

    /**
     * Agregar lugar a favoritos.
     */
    public function store(Request $request, PetFriendlyPlace $place): JsonResponse
    {
        if ($place->status !== 'approved') {
            return response()->json([
                'message' => 'No puedes agregar a favoritos un lugar no aprobado',
            ], 403);
        }

        if ($request->user()->favoritePlaces()->where('place_id', $place->id)->exists()) {
            return response()->json([
                'message' => 'Este lugar ya está en tus favoritos',
            ], 409);
        }

        $request->user()->favoritePlaces()->attach($place->id);

        return response()->json([
            'message' => 'Lugar agregado a favoritos correctamente',
        ], 201);
    }

    /**
     * Eliminar lugar de favoritos.
     */
    public function destroy(Request $request, PetFriendlyPlace $place): JsonResponse
    {
        if (!$request->user()->favoritePlaces()->where('place_id', $place->id)->exists()) {
            return response()->json([
                'message' => 'Este lugar no está en tus favoritos',
            ], 404);
        }

        $request->user()->favoritePlaces()->detach($place->id);

        return response()->json([
            'message' => 'Lugar removido de favoritos correctamente',
        ]);
    }
}
