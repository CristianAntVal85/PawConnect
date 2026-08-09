<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Walk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalkController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $walks = Walk::query()
            ->withCount('pets')
            ->with(['creator:id,name', 'pets:id,user_id,name,species,breed,available_for_walks'])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('scheduled_at', '>=', now()->startOfDay())
            ->orderBy('scheduled_at')
            ->get();

        $eligiblePets = $user->pets()
            ->where('species', 'dog')
            ->where('available_for_walks', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $initialCenter = [
            'lat' => (float) ($user->lat ?? $walks->first()?->lat ?? 40.4168),
            'lng' => (float) ($user->lng ?? $walks->first()?->lng ?? -3.7038),
        ];

        return view('walks.index', [
            'walks' => $walks,
            'eligiblePets' => $eligiblePets,
            'initialCenter' => $initialCenter,
        ]);
    }

    public function join(Request $request, Walk $walk): RedirectResponse
    {
        $validated = $request->validate([
            'pet_id' => ['required', 'integer', 'exists:pets,id'],
        ]);

        $pet = $request->user()->pets()->findOrFail($validated['pet_id']);

        if ($pet->species !== 'dog') {
            return $this->walkRedirect('error', 'Solo las mascotas caninas pueden unirse a paseos.');
        }

        if (! $pet->available_for_walks) {
            return $this->walkRedirect('error', 'La mascota seleccionada no está disponible para paseos.');
        }

        if ($walk->scheduled_at !== null && $walk->scheduled_at->isPast()) {
            return $this->walkRedirect('error', 'No puedes unirte a un paseo ya finalizado.');
        }

        if ($walk->pets()->whereKey($pet->id)->exists()) {
            return $this->walkRedirect('error', 'La mascota ya participa en este paseo.');
        }

        if ($walk->pets()->count() >= $walk->max_pets) {
            return $this->walkRedirect('error', 'El paseo ya alcanzó el máximo de mascotas permitidas.');
        }

        $walk->pets()->attach($pet->id);

        return $this->walkRedirect('success', 'Tu mascota se unió al paseo correctamente.');
    }

    public function leave(Request $request, Walk $walk): RedirectResponse
    {
        $validated = $request->validate([
            'pet_id' => ['nullable', 'integer', 'exists:pets,id'],
        ]);

        $petQuery = $request->user()
            ->pets()
            ->whereHas('walks', fn ($query) => $query->where('walk_id', $walk->id));

        if (! empty($validated['pet_id'])) {
            $petQuery->whereKey($validated['pet_id']);
        }

        $pet = $petQuery->first();

        if (! $pet) {
            return $this->walkRedirect('error', 'No tienes mascotas participando en este paseo.');
        }

        $walk->pets()->detach($pet->id);

        return $this->walkRedirect('success', 'Tu mascota salió del paseo correctamente.');
    }

    private function walkRedirect(string $flashKey, string $message): RedirectResponse
    {
        return redirect()
            ->route('web.walks.index')
            ->with($flashKey, $message);
    }
}
