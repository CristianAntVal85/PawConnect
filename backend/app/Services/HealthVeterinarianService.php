<?php

namespace App\Services;

use App\Models\Pet;
use App\Models\PetFriendlyPlace;
use App\Models\User;
use Illuminate\Support\Collection;

class HealthVeterinarianService
{
    private HealthStatusService $healthStatusService;

    public function __construct(HealthStatusService $healthStatusService)
    {
        $this->healthStatusService = $healthStatusService;
    }

    /**
     * Obtener veterinarios cercanos recomendados basado en registros sanitarios próximos a vencer.
     *
     * @param Pet $pet
     * @param User $user
     * @param int $radiusKm - Radio de búsqueda en kilómetros
     * @return Collection
     */
    public function getRecommendedVeterinarians(Pet $pet, User $user, int $radiusKm = 10): Collection
    {
        // Obtener la ubicación del usuario (si no tiene, retornar vacío)
        if (!$user->lat || !$user->lng) {
            return collect();
        }

        // Detectar si hay registros próximos a vencer o vencidos
        $criticalRecords = $this->getCriticalHealthRecords($pet);

        if ($criticalRecords->isEmpty()) {
            return collect();
        }

        // Buscar veterinarios cercanos usando Haversine
        return $this->findNearbyVeterinarians(
            $user->lat,
            $user->lng,
            $radiusKm,
            $criticalRecords
        );
    }

    /**
     * Obtener registros sanitarios críticos (próximos a vencer o vencidos).
     *
     * @param Pet $pet
     * @return Collection
     */
    public function getCriticalHealthRecords(Pet $pet): Collection
    {
        return $pet->healthRecords()
            ->whereNotNull('next_due_date')
            ->get()
            ->filter(function ($record) {
                $status = $this->healthStatusService->getStatus($record);
                // Retorna registros en estado yellow (próximo a vencer) o red (vencido)
                return in_array($status['status'], ['yellow', 'red']);
            });
    }

    /**
     * Buscar veterinarios cercanos usando la fórmula de Haversine.
     *
     * @param float $userLat
     * @param float $userLng
     * @param int $radiusKm
     * @param Collection $criticalRecords
     * @return Collection
     */
    private function findNearbyVeterinarians(
        float $userLat,
        float $userLng,
        int $radiusKm,
        Collection $criticalRecords
    ): Collection {
        // Haversine: distancia en km
        $haversine = <<<'SQL'
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) *
            cos(radians(lng) - radians(?)) +
            sin(radians(?)) * sin(radians(lat))))
        SQL;

        $vets = PetFriendlyPlace::where('category', 'vet')
            ->where('status', 'approved')
            ->selectRaw("*, {$haversine} as distance", [
                $userLat,
                $userLng,
                $userLat,
            ])
            ->havingRaw('distance <= ?', [$radiusKm])
            ->orderBy('distance', 'asc')
            ->limit(5)
            ->get();

        // Enriquecer con información de qué servicios necesita (basado en registros críticos)
        return $vets->map(function ($vet) use ($criticalRecords) {
            $types = $criticalRecords->pluck('type')->unique()->values()->all();
            return (object) [
                'place_id' => $vet->id,
                'place' => $vet,
                'distance' => $vet->distance,
                'needed_services' => $types,
                'urgency' => $this->getUrgencyLevelFromRecords($criticalRecords),
            ];
        });
    }

    /**
     * Determinar el nivel de urgencia basado en los registros críticos.
     *
     * @param Collection $criticalRecords
     * @return string
     */
    private function getUrgencyLevelFromRecords(Collection $criticalRecords): string
    {
        // Si hay registros vencidos (red), urgencia ALTA
        foreach ($criticalRecords as $record) {
            $status = $this->healthStatusService->getStatus($record);
            if ($status['status'] === 'red') {
                return 'high';
            }
        }

        // Si todos son amarillos, urgencia MEDIA
        return 'medium';
    }

    /**
     * Determinar el nivel de urgencia de una mascota basado en sus registros críticos.
     *
     * @param Pet $pet
     * @return string
     */
    public function getUrgencyLevel(Pet $pet): string
    {
        $criticalRecords = $this->getCriticalHealthRecords($pet);
        return $this->getUrgencyLevelFromRecords($criticalRecords);
    }

    /**
     * Obtener descripción amigable de los servicios necesarios.
     *
     * @param array $types
     * @return string
     */
    public function getServicesDescription(array $types): string
    {
        $descriptions = [
            'vaccination'   => 'Vacunación',
            'deworming'     => 'Desparasitación',
            'checkup'       => 'Revisión general',
            'surgery'       => 'Cirugía',
            'medication'    => 'Medicación',
            'dental'        => 'Limpieza dental',
            'grooming'      => 'Aseo',
        ];

        $services = array_map(
            fn($type) => $descriptions[$type] ?? ucfirst($type),
            $types
        );

        return implode(', ', $services);
    }

    /**
     * Obtener emoji de urgencia.
     *
     * @param string $urgency
     * @return string
     */
    public function getUrgencyEmoji(string $urgency): string
    {
        return match ($urgency) {
            'high'   => '🚨',
            'medium' => '⏰',
            default  => 'ℹ️',
        };
    }
}

