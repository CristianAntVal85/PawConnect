<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Actualizar la ubicación actual del usuario autenticado.
     *
     * Usado por la vista de swipe cuando el usuario pulsa "Mi ubicación"
     * o cambia manualmente su posición.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'success' => true,
            'lat' => $user->lat,
            'lng' => $user->lng,
            'address' => $user->address,
        ]);
    }
}
