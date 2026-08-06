<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HealthRecordResource;
use App\Models\HealthRecord;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function store(Request $request, Pet $pet): JsonResponse
    {
        $this->ensureCanManagePet($request, $pet);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['vaccination', 'deworming', 'checkup', 'surgery', 'medication'])],
            'last_date' => ['nullable', 'date'],
            'periodicity_days' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'notifications_active' => ['nullable', 'boolean'],
        ]);

        $record = new HealthRecord($validated);
        $record->pet_id = $pet->id;

        if (! empty($validated['last_date']) && ! empty($validated['periodicity_days'])) {
            $record->next_due_date = now()
                ->parse($validated['last_date'])
                ->addDays((int) $validated['periodicity_days'])
                ->toDateString();
        }

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

    public function update(Request $request, Pet $pet, HealthRecord $health): JsonResponse
    {
        $this->ensureCanManagePet($request, $pet);
        $this->ensureRecordBelongsToPet($pet, $health);

        $validated = $request->validate([
            'type' => ['sometimes', Rule::in(['vaccination', 'deworming', 'checkup', 'surgery', 'medication'])],
            'last_date' => ['sometimes', 'nullable', 'date'],
            'periodicity_days' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'notifications_active' => ['sometimes', 'boolean'],
        ]);

        $health->fill($validated);

        $lastDate = $validated['last_date'] ?? $health->last_date?->toDateString();
        $periodicityDays = $validated['periodicity_days'] ?? $health->periodicity_days;

        if (! empty($lastDate) && ! empty($periodicityDays)) {
            $health->next_due_date = now()
                ->parse($lastDate)
                ->addDays((int) $periodicityDays)
                ->toDateString();
        }

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
}
