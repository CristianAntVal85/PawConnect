<?php

namespace App\Services;

use App\Models\Pet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

class MatchService
{
    /**
     * Devuelve candidatos ordenados por distancia ascendente desde la ubicación
     * de búsqueda (estilo Wallapop): el buscador envía su posición actual (GPS o
     * elegida manualmente) y los candidatos se ordenan por la distancia entre esa
     * posición y la ubicación actual almacenada del dueño del candidato.
     *
     * Reglas:
     * - excluir mascotas propias del usuario
     * - excluir mascotas ya vistas (likes/pass)
     * - filtrar por distancia (Haversine) entre searchLat/searchLng y la
     *   ubicación actual del dueño del candidato (users.lat/lng)
     * - ordenar por distancia ascendente; compatibilidad como criterio secundario
     * - incluir distance_km en cada candidato
     */
    public function getCandidates(
        Pet $currentPet,
        float $searchLat,
        float $searchLng,
        ?int $radiusKm = 25,
        ?string $breed = null,
        ?string $energy = null,
        ?string $age = null
    ): Collection
    {
        if ($currentPet->species !== 'dog') {
            return new Collection();
        }

        $seenPetIds = $this->getSeenPetIds($currentPet);
        $currentUser = $currentPet->user;

        $query = Pet::query()
            ->where('id', '!=', $currentPet->id)
            ->where('user_id', '!=', $currentUser->id)
            ->whereNotIn('id', $seenPetIds)
            ->where('available_for_walks', true)
            ->where('species', 'dog')
            ->with('user:id,lat,lng');

        if ($breed && $breed !== 'all') {
            $query->where('breed', $breed);
        }

        if ($energy && $energy !== 'all') {
            $query->where('energy_level', $energy);
        }

        if ($age && $age !== 'all') {
            $query->where(function ($q) use ($age) {
                $q->whereRaw("
                    CASE
                        WHEN age <= 2 THEN 'young'
                        WHEN age < 7 THEN 'adult'
                        ELSE 'senior'
                    END = ?
                ", [$age]);
            });
        }

        $candidates = $query->get();

        // Filtrar por distancia
        $candidates = $candidates->filter(function (Pet $candidate) use ($searchLat, $searchLng, $radiusKm): bool {
            $candidateUser = $candidate->user;

            // Si radiusKm es null → no filtrar por distancia
            if ($radiusKm === null) {
                return true;
    }

            if (! $candidateUser?->lat || ! $candidateUser?->lng) {
                return false;
            }

            $distance = $this->haversine(
                $searchLat,
                $searchLng,
                (float) $candidateUser->lat,
                (float) $candidateUser->lng,
            );

            return $distance <= $radiusKm;
        });

        // Ordenar por compatibilidad
        return $candidates->sortByDesc(function (Pet $candidate) use ($currentPet): int {
            return $this->compatibilityScore($currentPet, $candidate);
        })->values();
    }


    public function registerInteraction(Pet $currentPet, Pet $targetPet, string $action): array
    {
        if (! in_array($action, ['like', 'pass'], true)) {
            return [
                'match' => null,
                'swipe' => null,
            ];
        }

        return DB::transaction(function () use ($currentPet, $targetPet, $action): array {
            $existingSwipe = $currentPet->matchesAsPetA()
                ->where('pet_b_id', $targetPet->id)
                ->first();

            if ($existingSwipe) {
                return [
                    'match' => $existingSwipe->status === 'matched' ? $existingSwipe : null,
                    'swipe' => [
                        'action' => $existingSwipe->status === 'liked' ? 'like' : 'pass',
                        'pet_id' => $targetPet->id,
                    ],
                ];
            }

            $swipe = $currentPet->matchesAsPetA()->create([
                'pet_b_id' => $targetPet->id,
                'status' => $action === 'like' ? 'liked' : 'passed',
            ]);

            $match = null;

            if ($action === 'like') {
                $reverseSwipe = $targetPet->matchesAsPetA()
                    ->where('pet_b_id', $currentPet->id)
                    ->where('status', 'liked')
                    ->first();

                if ($reverseSwipe) {
                    $reverseSwipe->update(['status' => 'matched']);
                    $swipe->update(['status' => 'matched']);
                    $match = $swipe->fresh();
                }
            }

            return [
                'match' => $match,
                'swipe' => [
                    'action' => $action,
                    'pet_id' => $targetPet->id,
                ],
            ];
        });
    }

    protected function getSeenPetIds(Pet $currentPet): array
    {
        return $currentPet->matchesAsPetA()
            ->pluck('pet_b_id')
            ->merge($currentPet->matchesAsPetB()->pluck('pet_a_id'))
            ->unique()
            ->filter()
            ->values()
            ->all();
    }

    protected function compatibilityScore(Pet $currentPet, Pet $candidate): int
    {
        $sizeMap = ['small' => 1, 'medium' => 2, 'large' => 3];
        $energyMap = ['low' => 1, 'medium' => 2, 'high' => 3];

        $currentSize = $sizeMap[$currentPet->size] ?? null;
        $candidateSize = $sizeMap[$candidate->size] ?? null;
        $currentEnergy = $energyMap[$currentPet->energy_level] ?? null;
        $candidateEnergy = $energyMap[$candidate->energy_level] ?? null;

        $sizeScore = 0;
        if ($currentSize !== null && $candidateSize !== null) {
            $sizeScore = max(0, 3 - abs($currentSize - $candidateSize));
        }

        $energyScore = 0;
        if ($currentEnergy !== null && $candidateEnergy !== null) {
            $energyScore = max(0, 3 - abs($currentEnergy - $candidateEnergy));
        }

        return $sizeScore + $energyScore + ($candidate->available_for_walks ? 2 : 0);
    }

    protected function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
