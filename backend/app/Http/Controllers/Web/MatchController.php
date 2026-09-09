<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\PetMatch;
use App\Services\MatchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function __construct(private MatchService $matchService)
    {
    }

    /**
     * Mostrar la vista de swipe para matches
     */
    public function swipe(Request $request): View
    {
        $user = $request->user();
        $pet = $user->pets()->firstOrFail();

        // Usar lat/lng del usuario actual
        $searchLat = (float) ($user->lat ?? 0);
        $searchLng = (float) ($user->lng ?? 0);

        $candidates = $this->matchService->getCandidates(
            $pet,
            $searchLat,
            $searchLng,
            radiusKm: (int) $request->input('radiusKm', 25),
            breed: $request->input('breed'),
            energy: $request->input('energy'),
            age: $request->input('age')
        );

        return view('matches.swipe', [
            'candidates' => $candidates,
        ]);
    }

    /**
     * Like a pet
     */
    public function like(Request $request, Pet $pet)
    {
        $currentPet = $request->user()->pets()->firstOrFail();

        $this->authorize('view', $currentPet);

        $result = $this->matchService->registerInteraction($currentPet, $pet, 'like');

        return response()->json([
            'success' => true,
            'match' => $result['match'],
            'message' => $result['match'] ? '¡Has hecho match!' : 'Like registrado',
        ]);
    }

    /**
     * Pass a pet
     */
    public function pass(Request $request, Pet $pet)
    {
        $currentPet = $request->user()->pets()->firstOrFail();

        $this->authorize('view', $currentPet);

        $result = $this->matchService->registerInteraction($currentPet, $pet, 'pass');

        return response()->json([
            'success' => true,
            'message' => 'Pass registrado',
        ]);
    }
}
