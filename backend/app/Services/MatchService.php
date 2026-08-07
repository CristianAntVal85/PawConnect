<?php

namespace App\Services;

use App\Models\Pet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MatchService
{
    /**
     * Devuelve candidatos para un usuario autenticado.
     *
     * Reglas:
     * - excluir mascotas propias del usuario
     * - excluir mascotas ya vistas (likes/pass)
     * - filtrar por distancia (Haversine) usando lat/lng del usuario y del candidato
     * - ordenar por compatibilidad: tamaño, energía y disponibilidad
     */
    public function getCandidates(Pet $currentPet, int $radiusKm = 25): Collection
    {
        $user = $currentPet->user;

        if ($currentPet->species !== 'dog' || ! $user->lat || ! $user->lng) {
            return new Collection();
        }

        $seenPetIds = $this->getSeenPetIds($currentPet);

        $query = Pet::query()
            ->where('id', '!=', $currentPet->id)
            ->where('user_id', '!=', $user->id)
            ->whereNotIn('id', $seenPetIds)
            ->where('available_for_walks', true)
            ->where('species', 'dog')
            ->with('user:id,lat,lng');

        $candidates = $query->get();

        $candidates = $candidates->filter(function (Pet $candidate) use ($user, $radiusKm): bool {
            $candidateUser = $candidate->user;

            if (! $candidateUser?->lat || ! $candidateUser?->lng) {
                return false;
            }

            $distance = $this->haversine(
                (float) $user->lat,
                (float) $user->lng,
                (float) $candidateUser->lat,
                (float) $candidateUser->lng,
            );

            return $distance <= $radiusKm;
        });

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
