<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Services\MatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MatchController extends Controller
{
    public function __construct(private MatchService $matchService)
    {
    }

    /**
     * Devuelve candidatos ordenados por distancia desde la ubicación de búsqueda.
     *
     * Parámetros query opcionales:
     *   ?lat=40.4168&lng=-3.7038   → posición actual (GPS o manual)
     *   ?radius=25                 → radio en kilómetros (por defecto 25)
     *   ?pet_id=3                  → mascota activa (por defecto la primera del usuario)
     *
     * Si no se envía lat/lng se usa la ubicación actual del usuario.
     * Si no existe ninguna de las dos se devuelve 422.
     */
    public function index(Request $request): JsonResponse
    {
        $petId = $request->query('pet_id');
        $pet = $petId ? Pet::findOrFail($petId) : $request->user()->pets()->firstOrFail();

        $this->authorize('view', $pet);

        [$searchLat, $searchLng] = $this->resolveSearchLocation($request);

        $radiusKm = (int) $request->query('radius', 25);

        $candidates = $this->matchService->getCandidates(
            $pet,
            $searchLat,
            $searchLng,
            $radiusKm
        );

        return $this->success(
            $candidates->values(),
            'Candidatos obtenidos correctamente.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $action = $request->route()->getName() === 'api.v1.matches.pass' ? 'pass' : 'like';
        $currentPet = $request->user()->pets()->firstOrFail();
        $targetPet = Pet::findOrFail($request->route('petId'));

        $result = $this->matchService->registerInteraction($currentPet, $targetPet, $action);

        return $this->success([
            'swipe' => $result['swipe'],
            'match' => $result['match'],
        ], $result['match'] ? '\u00a1Has hecho match!' : 'Interacción registrada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Resuelve la ubicación de búsqueda:
     *   1. lat/lng explícitos en la query (GPS del navegador o ubicación manual)
     *   2. Ubicación actual del usuario (users.lat/lng)
     *
     * @return array{0: float, 1: float}
     * @throws ValidationException si no hay ninguna ubicación disponible
     */
    private function resolveSearchLocation(Request $request): array
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if ($lat !== null && $lng !== null) {
            $request->validate([
                'lat' => ['numeric', 'between:-90,90'],
                'lng' => ['numeric', 'between:-180,180'],
            ]);

            return [(float) $lat, (float) $lng];
        }

        $user = $request->user();

        if ($user->lat !== null && $user->lng !== null) {
            return [(float) $user->lat, (float) $user->lng];
        }

        throw ValidationException::withMessages([
            'lat' => ['Debes enviar tu ubicación o actualizar tu perfil con una ubicación.'],
        ]);
    }
}
