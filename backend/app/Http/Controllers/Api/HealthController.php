<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHealthRecordRequest;
use App\Http\Requests\UpdateHealthRecordRequest;
use App\Http\Resources\HealthRecordResource;
use App\Models\HealthRecord;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function index(Request $request, Pet $pet): JsonResponse
    {
        $this->ensureCanManagePet($request, $pet);

        $records = $pet->healthRecords()->latest()->get();

        return $this->success(
            HealthRecordResource::collection($records)->resolve(),
            'Registros sanitarios obtenidos correctamente.'
        );
    }

    public function store(StoreHealthRecordRequest $request, Pet $pet): JsonResponse
    {
        $this->ensureCanManagePet($request, $pet);

        $validated = $request->validated();

        $record = new HealthRecord($validated);
        $record->pet_id = $pet->id;
        $record->next_due_date = $this->calculateNextDueDate(
            $validated['last_date'] ?? null,
            $validated['periodicity_days'] ?? null
        );

        $record->save();

        return $this->success(
            (new HealthRecordResource($record))->resolve(),
            'Registro sanitario creado correctamente.',
            201
        );
    }

    public function show(Request $request, Pet $pet, HealthRecord $health): JsonResponse
    {
        $this->ensureCanManagePet($request, $pet);
        $this->ensureRecordBelongsToPet($pet, $health);

        return $this->success(
            (new HealthRecordResource($health))->resolve(),
            'Registro sanitario obtenido correctamente.'
        );
    }

    public function update(UpdateHealthRecordRequest $request, Pet $pet, HealthRecord $health): JsonResponse
    {
        $this->ensureCanManagePet($request, $pet);
        $this->ensureRecordBelongsToPet($pet, $health);

        $validated = $request->validated();

        $health->fill($validated);

        $lastDate = $validated['last_date'] ?? $health->last_date?->toDateString();
        $periodicityDays = $validated['periodicity_days'] ?? $health->periodicity_days;

        $health->next_due_date = $this->calculateNextDueDate($lastDate, $periodicityDays);

        $health->save();

        return $this->success(
            (new HealthRecordResource($health->fresh()))->resolve(),
            'Registro sanitario actualizado correctamente.'
        );
    }

    public function destroy(Request $request, Pet $pet, HealthRecord $health): JsonResponse
    {
        $this->ensureCanManagePet($request, $pet);
        $this->ensureRecordBelongsToPet($pet, $health);

        $health->delete();

        return $this->success(null, 'Registro sanitario eliminado correctamente.');
    }

    /**
     * Renovar un registro sanitario (actualizar last_date a hoy).
     *
     * @param  Request  $request
     * @param  Pet  $pet
     * @param  HealthRecord  $health
     * @return JsonResponse
     */
    public function renew(Request $request, Pet $pet, HealthRecord $health): JsonResponse
    {
        $this->ensureCanManagePet($request, $pet);
        $this->ensureRecordBelongsToPet($pet, $health);

        $health->last_date = now()->toDateString();
        $health->next_due_date = $this->calculateNextDueDate(
            $health->last_date,
            $health->periodicity_days
        );

        $health->save();

        return $this->success(
            (new HealthRecordResource($health->fresh()))->resolve(),
            'Registro sanitario renovado correctamente.'
        );
    }

    /**
     * Obtener veterinarios cercanos recomendados basado en registros sanitarios críticos.
     */
    public function recommendedVeterinarians(Request $request, Pet $pet)
    {
        $this->ensureCanManagePet($request, $pet);

        $veterinarianService = app(\App\Services\HealthVeterinarianService::class);
        $vets = $veterinarianService->getRecommendedVeterinarians(
            $pet,
            $request->user(),
            radiusKm: 15
        );

        return $this->success(
            \App\Http\Resources\RecommendedVeterinarianResource::collection($vets)->resolve(),
            'Veterinarios recomendados obtenidos correctamente.',
        );
    }

    private function ensureCanManagePet(Request $request, Pet $pet): void
    {
        if (! $request->user()->isAdmin() && $pet->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    private function ensureRecordBelongsToPet(Pet $pet, HealthRecord $health): void
    {
        if ((int) $health->pet_id !== (int) $pet->id) {
            abort(404);
        }
    }

    /**
     * Calcula la próxima fecha de revisión sanitaria.
     *
     * @param  string|null  $lastDate  Fecha del último registro (formato YYYY-MM-DD)
     * @param  int|null  $periodicityDays  Días de periodicidad
     * @return string|null  Próxima fecha de revisión (formato YYYY-MM-DD) o null
     */
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
