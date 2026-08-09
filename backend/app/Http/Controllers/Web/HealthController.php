<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Services\HealthStatusService;

class HealthController extends Controller
{
    private HealthStatusService $healthStatusService;

    public function __construct(HealthStatusService $healthStatusService)
    {
        $this->healthStatusService = $healthStatusService;
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

        return view('health.timeline', [
            'pet' => $pet,
            'records' => $records,
            'generalStatus' => $generalStatus,
        ]);
    }
}
