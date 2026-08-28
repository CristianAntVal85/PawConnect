<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Services\HealthStatusService;
use App\Services\HealthVeterinarianService;
use Illuminate\Http\Request;

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
        $this->ensureCanManagePet($pet);

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

        $recommendedVeterinarians = $this->veterinarianService->getRecommendedVeterinarians(
            $pet,
            auth()->user(),
            radiusKm: 15
        );

        return view('health.timeline', [
            'pet' => $pet,
            'records' => $records,
            'generalStatus' => $generalStatus,
            'recommendedVeterinarians' => $recommendedVeterinarians,
            'veterinarianService' => $this->veterinarianService,
        ]);
    }

    /**
     * Mostrar formulario de creación de registro sanitario.
     */
    public function create(Pet $pet)
    {
        $this->ensureCanManagePet($pet);

        return view('health.create', [
            'pet' => $pet,
        ]);
    }

    /**
     * Guardar un nuevo registro sanitario.
     */
    public function store(Request $request, Pet $pet)
    {
        $this->ensureCanManagePet($pet);

        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'last_date' => 'required|date',
            'periodicity_days' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'notifications_active' => 'boolean',
        ]);

        $nextDueDate = $this->calculateNextDueDate(
            $validated['last_date'],
            $validated['periodicity_days']
        );

        $record = HealthRecord::create([
            'pet_id' => $pet->id,
            'type' => $validated['type'],
            'last_date' => $validated['last_date'],
            'periodicity_days' => $validated['periodicity_days'],
            'notes' => $validated['notes'] ?? null,
            'notifications_active' => $validated['notifications_active'] ?? false,
            'next_due_date' => $nextDueDate,
        ]);

        return redirect()
            ->route('web.pets.health.timeline', $pet->id)
            ->with('success', 'Registro sanitario creado correctamente.');
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Pet $pet, HealthRecord $health)
    {
        $this->ensureRecordBelongsToPet($pet, $health);

        return view('health.edit', [
            'pet' => $pet,
            'record' => $health,
        ]);
    }

    /**
     * Actualizar registro sanitario.
     */
    public function update(Request $request, Pet $pet, HealthRecord $health)
    {
        $this->ensureRecordBelongsToPet($pet, $health);

        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'last_date' => 'required|date',
            'periodicity_days' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'notifications_active' => 'boolean',
        ]);

        $health->fill($validated);

        $health->next_due_date = $this->calculateNextDueDate(
            $validated['last_date'],
            $validated['periodicity_days']
        );

        $health->save();

        return redirect()
            ->route('web.pets.health.timeline', $pet->id)
            ->with('success', 'Registro sanitario actualizado correctamente.');
    }

    /**
     * Eliminar registro sanitario.
     */
    public function destroy(Pet $pet, HealthRecord $health)
    {
        $this->ensureRecordBelongsToPet($pet, $health);

        $health->delete();

        return redirect()
            ->route('web.pets.health.timeline', $pet->id)
            ->with('success', 'Registro sanitario eliminado correctamente.');
    }

    /**
     * Renovar registro sanitario (actualizar last_date a hoy).
     */
    public function renew(Pet $pet, HealthRecord $health)
    {
        $this->ensureRecordBelongsToPet($pet, $health);

        $health->last_date = now()->toDateString();
        $health->next_due_date = $this->calculateNextDueDate(
            $health->last_date,
            $health->periodicity_days
        );

        $health->save();

        return redirect()
            ->route('web.pets.health.timeline', $pet->id)
            ->with('success', 'Registro sanitario renovado correctamente.');
    }

    private function ensureCanManagePet(Pet $pet): void
    {
        if ($pet->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }
    }

    private function ensureRecordBelongsToPet(Pet $pet, HealthRecord $health): void
    {
        if ((int) $health->pet_id !== (int) $pet->id) {
            abort(404);
        }
    }

    private function calculateNextDueDate(?string $lastDate, ?int $periodicityDays): ?string
    {
        if (empty($lastDate) || empty($periodicityDays)) {
            return null;
        }

        return now()
            ->parse($lastDate)
            ->addDays((int) $periodicityDays)
            ->toDateString();
    }
}
