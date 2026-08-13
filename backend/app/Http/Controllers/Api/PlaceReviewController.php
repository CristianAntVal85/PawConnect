<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\PlaceReviewResource;
use App\Models\PetFriendlyPlace;
use App\Models\PlaceReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PlaceReviewController extends Controller
{
    /**
     * Listado de reseñas de un lugar.
     */
    public function index(PetFriendlyPlace $place): JsonResponse
    {
        $reviews = $place->reviews()->with('user:id,name')->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Reseñas obtenidas correctamente.',
            'data'    => PlaceReviewResource::collection($reviews->items())->resolve(),
            'pagination' => [
                'total'        => $reviews->total(),
                'per_page'     => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
            ],
        ]);
    }

    /**
     * Crear nueva reseña.
     */
    public function store(StoreReviewRequest $request, PetFriendlyPlace $place): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $validated['place_id'] = $place->id;

        // Procesar foto si existe
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('reviews', 'public');
            $validated['photo'] = Storage::url($path);
        }

        $review = PlaceReview::create($validated);
        $review->load('user:id,name');

        return $this->success(
            (new PlaceReviewResource($review))->resolve(),
            'Reseña creada exitosamente.',
            201
        );
    }

    /**
     * Ver detalle de una reseña.
     */
    public function show(PetFriendlyPlace $place, PlaceReview $review): JsonResponse
    {
        if ($review->place_id !== $place->id) {
            abort(404);
        }

        $review->load('user:id,name');

        return $this->success(
            (new PlaceReviewResource($review))->resolve(),
            'Reseña obtenida correctamente.'
        );
    }

    /**
     * Actualizar reseña (solo autor o admin).
     */
    public function update(UpdateReviewRequest $request, PetFriendlyPlace $place, PlaceReview $review): JsonResponse
    {
        if ($review->place_id !== $place->id) {
            abort(404);
        }

        if ($review->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, 'No autorizado para actualizar esta reseña.');
        }

        $validated = $request->validated();

        // Procesar nueva foto si existe
        if ($request->hasFile('photo')) {
            // Eliminar foto anterior si existe
            if ($review->photo) {
                $oldPath = str_replace('/storage/', '', $review->photo);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('reviews', 'public');
            $validated['photo'] = Storage::url($path);
        }

        $review->update($validated);
        $review->load('user:id,name');

        return $this->success(
            (new PlaceReviewResource($review))->resolve(),
            'Reseña actualizada correctamente.'
        );
    }

    /**
     * Eliminar reseña (solo autor o admin).
     */
    public function destroy(PetFriendlyPlace $place, PlaceReview $review): JsonResponse
    {
        if ($review->place_id !== $place->id) {
            abort(404);
        }

        if ($review->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403, 'No autorizado para eliminar esta reseña.');
        }

        // Eliminar foto si existe
        if ($review->photo) {
            $oldPath = str_replace('/storage/', '', $review->photo);
            Storage::disk('public')->delete($oldPath);
        }

        $review->delete();

        return $this->success(null, 'Reseña eliminada correctamente.');
    }
}
