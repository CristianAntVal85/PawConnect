<?php

namespace App\Services;

use App\Models\Pet;
use App\Models\UserLocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

class MatchService
{
    /**
     * Devuelve candidatos ordenados por distancia ascendente desde la ubicación
     * de búsqueda (estilo Wallapop): el buscador envía su posición actual (GPS o
     * elegida manualmente) y los candidatos se ordenan por la distancia entre esa
     * posición y su ubicación predeterminada almacenada.
     *
     * Reglas:
     * - excluir mascotas propias del usuario
     * - excluir mascotas ya vistas (likes/pass)
     * - filtrar por distancia (Haversine) entre searchLat/searchLng y la
     *   ubicación predeterminada del dueño del candidato
     * - ordenar por distancia ascendente; compatibilidad como criterio secundario
     * - incluir distance_km en cada candidato
     */
    public function getCandidates(
        Pet $currentPet,
        float $searchLat,
        float $searchLng,
        int $radiusKm = 25,
    ): BaseCollection {
        if ($currentPet->species !== 'dog') {
            return new BaseCollection();
        }

        $seenPetIds = $this->getSeenPetIds($currentPet);

        $candidates = Pet::query()
            ->where('id', '!=', $currentPet->id)
            ->where('user_id', '!=', $currentPet->user_id)
            ->whereNotIn('id', $seenPetIds)
            ->where('available_for_walks', true)
            ->where('species', 'dog')
            ->with(['user.defaultLocation'])
            ->get();

        $scored = $candidates
            ->map(function (Pet $candidate) use ($searchLat, $searchLng, $radiusKm, $currentPet): ?array {
                $location = $candidate->user?->defaultLocation;

                if (! $location) {
                    return null;
                }

                $distance = $this->haversine(
                    $searchLat,
                    $searchLng,
                    (float) $location->lat,
                    (float) $location->lng,
                );

                if ($distance > $radiusKm) {
                    return null;
                }

                return [
                    'pet'         => $candidate,
                    'distance_km' => round($distance, 1),
                    'compat'      => $this->compatibilityScore($currentPet, $candidate),
                ];
            })
            ->filter()
            ->sortBy([
                ['distance_km', 'asc'],
                ['compat', 'desc'],
            ])
            ->values();

        return $scored->map(function (array $item): Pet {
            $item['pet']->setAttribute('distance_km', $item['distance_km']);

            return $item['pet'];
        });
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
