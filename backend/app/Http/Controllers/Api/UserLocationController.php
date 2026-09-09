<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserLocationController extends Controller
{
    /**
     * Obtener la ubicación actual del usuario autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'lat' => $user->lat,
            'lng' => $user->lng,
            'address' => $user->address,
        ], 'Ubicación actual obtenida correctamente.');
    }

    /**
     * Actualizar la ubicación actual del usuario autenticado (lat/lng).
     *
     * Esta es la ubicación en tiempo real usada para filtros y búsquedas.
     * No se mantiene historial de ubicaciones pasadas.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->update($validated);

        return $this->success([
            'lat' => $user->lat,
            'lng' => $user->lng,
            'address' => $user->address,
        ], 'Ubicación actualizada correctamente.');
    }
}

