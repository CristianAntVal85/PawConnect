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

        return response()->json([
            'message' => 'Registros sanitarios obtenidos correctamente.',
            'data' => HealthRecordResource::collection($records)->resolve(),
        ]);
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

        return response()->json([
            'message' => 'Registro sanitario creado correctamente.',
            'data' => (new HealthRecordResource($record))->resolve(),
        ], 201);
    }

    public function show(Request $request, Pet $pet, HealthRecord $health): JsonResponse
    {
        $this->ensureCanManagePet($request, $pet);
        $this->ensureRecordBelongsToPet($pet, $health);

        return response()->json([
            'message' => 'Registro sanitario obtenido correctamente.',
            'data' => (new HealthRecordResource($health))->resolve(),
        ]);
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

        return response()->json([
            'message' => 'Registro sanitario actualizado correctamente.',
            'data' => (new HealthRecordResource($health->fresh()))->resolve(),
        ]);
    }

    public function destroy(Request $request, Pet $pet, HealthRecord $health): JsonResponse
    {
        $this->ensureCanManagePet($request, $pet);
        $this->ensureRecordBelongsToPet($pet, $health);

        $health->delete();

        return response()->json([
            'message' => 'Registro sanitario eliminado correctamente.',
        ]);
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
