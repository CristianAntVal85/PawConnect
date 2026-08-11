<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Services\HealthStatusService;
use App\Services\HealthVeterinarianService;

class HealthController extends Controller
{
    private HealthStatusService $healthStatusService;
    private HealthVeterinarianService $veterinarianService;

    public function __construct(
        HealthStatusService $healthStatusService,
        HealthVeterinarianService $veterinarianService
    ) {
        $this->healthStatusService = $healthStatusService;
        $this->veterinarianService = $veterinarianService;
    }

    /**
     * Mostrar el timeline sanitario de una mascota.
     */
    public function timeline(Pet $pet)
    {
        // Verificar que el usuario es dueño de la mascota o es admin
        if ($pet->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        $records = $pet->healthRecords()
            ->latest()
            ->get()
            ->map(function ($record) {
                $status = $this->healthStatusService->getStatus($record);
                return array_merge(
                    $record->toArray(),
                    ['health_status' => $status]
                );
            });

        $generalStatus = $this->healthStatusService->getPetGeneralStatus($pet);

        // Obtener veterinarios recomendados basado en registros críticos
        $recommendedVeterinarians = $this->veterinarianService->getRecommendedVeterinarians(
            $pet,
            auth()->user(),
            radiusKm: 15  // Buscar en radio de 15km
        );

        return view('health.timeline', [
            'pet' => $pet,
            'records' => $records,
            'generalStatus' => $generalStatus,
            'recommendedVeterinarians' => $recommendedVeterinarians,
            'veterinarianService' => $this->veterinarianService,
        ]);
    }
}
