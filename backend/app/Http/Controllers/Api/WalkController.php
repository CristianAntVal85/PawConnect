<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalkResource;
use App\Models\Pet;
use App\Models\Walk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WalkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:0.1', 'max:300'],
            'date_from' => ['nullable', 'date'],
        ]);

        $originLat = $validated['lat'] ?? $request->user()->lat;
        $originLng = $validated['lng'] ?? $request->user()->lng;
        $radiusKm = (float) ($validated['radius_km'] ?? 25);
        $dateFrom = $validated['date_from'] ?? now()->startOfDay()->toDateTimeString();

        $walks = Walk::query()
            ->withCount('pets')
            ->with(['creator:id,name', 'pets:id,user_id,name,species,breed,available_for_walks'])
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', $dateFrom)
            ->orderBy('scheduled_at')
            ->get();

        if ($originLat !== null && $originLng !== null) {
            $walks = $this->filterNearbyWalks($walks, (float) $originLat, (float) $originLng, $radiusKm);
        }

        return $this->success(
            WalkResource::collection($walks)->resolve(),
            'Paseos obtenidos correctamente.'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'max_pets' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $walk = $request->user()->walksCreated()->create($validated);

        return $this->success(
            (new WalkResource($this->loadWalkRelations($walk->fresh())))->resolve(),
            'Paseo creado correctamente.',
            201
        );
    }

    public function show(Walk $walk): JsonResponse
    {
        return $this->success(
            (new WalkResource($this->loadWalkRelations($walk)))->resolve(),
            'Paseo obtenido correctamente.'
        );
    }

    public function update(Request $request, Walk $walk): JsonResponse
    {
        $this->ensureCanManageWalk($request, $walk);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'lat' => ['sometimes', 'numeric', 'between:-90,90'],
            'lng' => ['sometimes', 'numeric', 'between:-180,180'],
            'scheduled_at' => ['sometimes', 'date', 'after:now'],
            'max_pets' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $walk->fill($validated);

        if (array_key_exists('max_pets', $validated) && $walk->pets()->count() > (int) $validated['max_pets']) {
            return $this->error('El máximo de mascotas no puede ser menor al número actual de participantes.', 422);
        }

        $walk->save();

        return $this->success(
            (new WalkResource($this->loadWalkRelations($walk->fresh())))->resolve(),
            'Paseo actualizado correctamente.'
        );
    }

    public function destroy(Request $request, Walk $walk): JsonResponse
    {
        $this->ensureCanManageWalk($request, $walk);

        $walk->delete();

        return $this->success(null, 'Paseo eliminado correctamente.');
    }

    public function join(Request $request, Walk $walk): JsonResponse
    {
        $validated = $request->validate([
            'pet_id' => ['required', 'integer', 'exists:pets,id'],
        ]);

        $pet = $request->user()->pets()->findOrFail($validated['pet_id']);

        if ($pet->species !== 'dog') {
            return $this->error('Solo las mascotas caninas pueden unirse a paseos.', 422);
        }

        if (! $pet->available_for_walks) {
            return $this->error('La mascota seleccionada no está disponible para paseos.', 422);
        }

        if ($walk->pets()->whereKey($pet->id)->exists()) {
            return $this->error('La mascota ya participa en este paseo.', 422);
        }

        if ($walk->pets()->count() >= $walk->max_pets) {
            return $this->error('El paseo ya alcanzó el máximo de mascotas permitidas.', 422);
        }

        $walk->pets()->attach($pet->id);

        return $this->success(
            (new WalkResource($this->loadWalkRelations($walk->fresh())))->resolve(),
            'Mascota unida al paseo correctamente.'
        );
    }

    public function leave(Request $request, Walk $walk): JsonResponse
    {
        $validated = $request->validate([
            'pet_id' => ['nullable', 'integer', 'exists:pets,id'],
        ]);

        $petQuery = $request->user()
            ->pets()
            ->whereHas('walks', fn ($query) => $query->where('walk_id', $walk->id));

        if (! empty($validated['pet_id'])) {
            $petQuery->whereKey($validated['pet_id']);
        }

        $pet = $petQuery->first();

        if (! $pet) {
            return $this->error('No tienes mascotas participando en este paseo.', 422);
        }

        $walk->pets()->detach($pet->id);

        return $this->success(
            (new WalkResource($this->loadWalkRelations($walk->fresh())))->resolve(),
            'Mascota retirada del paseo correctamente.'
        );
    }

    private function ensureCanManageWalk(Request $request, Walk $walk): void
    {
        if (! $request->user()->isAdmin() && (int) $walk->creator_id !== (int) $request->user()->id) {
            abort(403);
        }
    }

    private function loadWalkRelations(Walk $walk): Walk
    {
        return $walk->loadCount('pets')
            ->load(['creator:id,name', 'pets:id,user_id,name,species,breed,available_for_walks']);
    }

    private function filterNearbyWalks(Collection $walks, float $originLat, float $originLng, float $radiusKm): Collection
    {
        return $walks
            ->filter(function (Walk $walk) use ($originLat, $originLng, $radiusKm) {
                if ($walk->lat === null || $walk->lng === null) {
                    return false;
                }

                $distance = $this->distanceInKm($originLat, $originLng, (float) $walk->lat, (float) $walk->lng);
                $walk->distance_km = round($distance, 2);

                return $distance <= $radiusKm;
            })
            ->sortBy('distance_km')
            ->values();
    }

    private function distanceInKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);
        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLng / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($a)));
    }
}
