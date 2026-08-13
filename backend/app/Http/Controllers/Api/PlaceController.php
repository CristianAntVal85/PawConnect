<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlaceRequest;
use App\Http\Requests\UpdatePlaceRequest;
use App\Http\Resources\PlaceResource;
use App\Models\PetFriendlyPlace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlaceController extends Controller
{
    /**
     * Listado de lugares aprobados + lugares pendientes del usuario (si es admin).
     */
    public function index(Request $request): JsonResponse
    {
        $query = PetFriendlyPlace::with('creator:id,name');

        // Filtros
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('description', 'like', '%' . $request->input('search') . '%');
        }

        // Por defecto, solo lugares aprobados
        if (! $request->user()?->isAdmin()) {
            $query->where('status', 'approved');
        } else {
            // Admin ve todos, pero puede filtrar por status
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }
        }

        // Filtro por distancia si proporciona lat/lng
        if ($request->filled('lat') && $request->filled('lng')) {
            $userLat = (float) $request->input('lat');
            $userLng = (float) $request->input('lng');
            $radiusKm = (float) $request->input('radius', 50); // 50km por defecto

            // Fórmula Haversine para calcular distancia
            $query->selectRaw(
                '*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance',
                [$userLat, $userLng, $userLat]
            )
                ->whereRaw(
                    '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) <= ?',
                    [$userLat, $userLng, $userLat, $radiusKm]
                )
                ->orderBy('distance');
        } else {
            $query->latest('id');
        }

        $places = $query->paginate(20);

        return response()->json([
            'message' => 'Lugares obtenidos correctamente.',
            'data'    => PlaceResource::collection($places->items())->resolve(),
            'pagination' => [
                'total'        => $places->total(),
                'per_page'     => $places->perPage(),
                'current_page' => $places->currentPage(),
                'last_page'    => $places->lastPage(),
            ],
        ]);
    }

    /**
     * Crear nuevo lugar (sugerencia de usuario).
     */
    public function store(StorePlaceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'pending'; // Pendiente de aprobación admin

        // Procesar foto si existe
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('places', 'public');
            $validated['photo'] = Storage::url($path);
        }

        $place = PetFriendlyPlace::create($validated);
        $place->load('creator:id,name');

        return response()->json([
            'message' => 'Lugar sugerido exitosamente. Pendiente de aprobación del administrador.',
            'data'    => (new PlaceResource($place))->resolve(),
        ], 201);
    }

    /**
     * Ver detalles de un lugar.
     */
    public function show(PetFriendlyPlace $place): JsonResponse
    {
        // Los no-aprobados solo pueden verlos el admin o el creador
        if ($place->status !== 'approved' && ! auth()->user()?->isAdmin() && $place->created_by !== auth()->id()) {
            abort(403);
        }

        $place->load('creator:id,name', 'reviews');

        return response()->json([
            'message' => 'Lugar obtenido correctamente.',
            'data'    => (new PlaceResource($place))->resolve(),
        ]);
    }

    /**
     * Actualizar lugar (solo creador o admin).
     */
    public function update(UpdatePlaceRequest $request, PetFriendlyPlace $place): JsonResponse
    {
        if ($place->created_by !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, 'No autorizado para actualizar este lugar.');
        }

        $validated = $request->validated();

        // Procesar nueva foto si existe
        if ($request->hasFile('photo')) {
            // Eliminar foto anterior si existe
            if ($place->photo) {
                $oldPath = str_replace('/storage/', '', $place->photo);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('places', 'public');
            $validated['photo'] = Storage::url($path);
        }

        $place->update($validated);
        $place->load('creator:id,name');

        return response()->json([
            'message' => 'Lugar actualizado correctamente.',
            'data'    => (new PlaceResource($place))->resolve(),
        ]);
    }

    /**
     * Eliminar lugar (solo admin o creador).
     */
    public function destroy(Request $request, PetFriendlyPlace $place): JsonResponse
    {
        if ($place->created_by !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        // Eliminar foto si existe
        if ($place->photo) {
            $oldPath = str_replace('/storage/', '', $place->photo);
            Storage::disk('public')->delete($oldPath);
        }

        $place->delete();

        return response()->json([
            'message' => 'Lugar eliminado correctamente.',
        ]);
    }

    /**
     * Aprobar un lugar (solo admin).
     */
    public function approve(Request $request, PetFriendlyPlace $place): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'approved' => ['required', 'boolean'],
            'reason'   => ['nullable', 'string', 'max:500'],
        ]);

        $status = $validated['approved'] ? 'approved' : 'rejected';
        $place->update(['status' => $status]);

        if ($place->creator) {
            $place->creator->notify(new \App\Notifications\PlaceStatusNotification(
                $place,
                $status,
                $validated['reason'] ?? null
            ));
        }

        return response()->json([
            'message' => "Lugar {$status} correctamente.",
            'data'    => (new PlaceResource($place->load('creator:id,name')))->resolve(),
        ]);
    }

    public function reject(Request $request, PetFriendlyPlace $place): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $place->update(['status' => 'rejected']);

        if ($place->creator) {
            $place->creator->notify(new \App\Notifications\PlaceStatusNotification(
                $place,
                'rejected',
                $validated['reason']
            ));
        }

        return response()->json([
            'message' => 'Lugar rechazado correctamente.',
            'data'    => (new PlaceResource($place->load('creator:id,name')))->resolve(),
        ]);
    }
}
