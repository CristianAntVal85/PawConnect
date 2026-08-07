<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Services\MatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function __construct(private MatchService $matchService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $petId = $request->query('pet_id');
        $pet = $petId ? Pet::findOrFail($petId) : $request->user()->pets()->firstOrFail();

        $this->authorize('view', $pet);

        $candidates = $this->matchService->getCandidates($pet);

        return response()->json([
            'message' => 'Candidatos obtenidos correctamente.',
            'data' => $candidates->values(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $action = $request->route()->getName() === 'api.v1.matches.pass' ? 'pass' : 'like';
        $currentPet = $request->user()->pets()->firstOrFail();
        $targetPet = Pet::findOrFail($request->route('petId'));

        $result = $this->matchService->registerInteraction($currentPet, $targetPet, $action);

        return response()->json([
            'message' => $result['match'] ? '¡Has hecho match!' : 'Interacción registrada.',
            'data' => [
                'swipe' => $result['swipe'],
                'match' => $result['match'],
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
