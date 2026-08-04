<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Models\Pet;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PetController extends Controller
{
    public function __construct(private StorageService $storageService)
    {
        $this->authorizeResource(Pet::class, 'pet');
    }

    /**
     * Listar mascotas. Por defecto, cada usuario ve solo las suyas.
     * Un admin puede usar ?scope=all para listar todas.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Pet::class);

        $query = Pet::query()->with(['user:id,name,email', 'photos:id,pet_id,path,display_order']);

        if (! $request->user()->isAdmin() || $request->query('scope') !== 'all') {
            $query->where('user_id', $request->user()->id);
        }

        $pets = $query->latest()->paginate(15);

        return response()->json([
            'message' => 'Mascotas obtenidas correctamente.',
            'data' => $pets,
        ]);
    }

    /**
     * Crear mascota.
     */
    public function store(StorePetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $photoFiles = $request->file('photos', []);

        $ownerId = $request->user()->id;

        if ($request->user()->isAdmin() && isset($validated['user_id'])) {
            $ownerId = (int) $validated['user_id'];
        }

        $newUploadedPaths = [];

        try {
            $pet = DB::transaction(function () use ($validated, $ownerId, $photoFiles, &$newUploadedPaths) {
                $pet = Pet::create([
                    ...Arr::except($validated, ['photos']),
                    'user_id' => $ownerId,
                ]);

                foreach ($photoFiles as $index => $photoFile) {
                    $path = $this->storageService->storePublicFile($photoFile, 'pets/'.$ownerId);
                    $newUploadedPaths[] = $path;

                    $pet->photos()->create([
                        'path' => $path,
                        'display_order' => $index,
                    ]);
                }

                return $pet->fresh()->load(['user:id,name,email', 'photos:id,pet_id,path,display_order']);
            });
        } catch (\Throwable $exception) {
            $this->storageService->deleteManyPublicFiles($newUploadedPaths);
            throw $exception;
        }


        return response()->json([
            'message' => 'Mascota creada correctamente.',
            'data' => $pet,
        ], 201);
    }

    /**
     * Ver una mascota.
     */
    public function show(Pet $pet): JsonResponse
    {
        return response()->json([
            'message' => 'Mascota obtenida correctamente.',
            'data' => $pet->load(['user:id,name,email', 'photos:id,pet_id,path,display_order']),
        ]);
    }

    /**
     * Actualizar mascota.
     */
    public function update(UpdatePetRequest $request, Pet $pet): JsonResponse
    {
        $validated = $request->validated();
        $photoFiles = $request->file('photos');
        $newUploadedPaths = [];
        $oldPhotoPaths = [];

        try {
            $pet = DB::transaction(function () use ($pet, $validated, $photoFiles, &$newUploadedPaths, &$oldPhotoPaths) {
                $pet->update(Arr::except($validated, ['photos']));

                if (is_array($photoFiles)) {
                    $oldPhotoPaths = $pet->photos()->pluck('path')->all();
                    $pet->photos()->delete();

                    foreach ($photoFiles as $index => $photoFile) {
                        $path = $this->storageService->storePublicFile($photoFile, 'pets/'.$pet->user_id);
                        $newUploadedPaths[] = $path;

                        $pet->photos()->create([
                            'path' => $path,
                            'display_order' => $index,
                        ]);
                    }
                }

                return $pet->fresh()->load(['user:id,name,email', 'photos:id,pet_id,path,display_order']);
            });
        } catch (\Throwable $exception) {
            $this->storageService->deleteManyPublicFiles($newUploadedPaths);
            throw $exception;
        }

        $this->storageService->deleteManyPublicFiles($oldPhotoPaths);

        return response()->json([
            'message' => 'Mascota actualizada correctamente.',
            'data' => $pet,
        ]);
    }

    /**
     * Eliminar mascota.
     */
    public function destroy(Pet $pet): JsonResponse
    {
        $photoPaths = $pet->photos()->pluck('path')->all();
        $pet->delete();
        $this->storageService->deleteManyPublicFiles($photoPaths);

        return response()->json([
            'message' => 'Mascota eliminada correctamente.',
        ]);
    }
}
