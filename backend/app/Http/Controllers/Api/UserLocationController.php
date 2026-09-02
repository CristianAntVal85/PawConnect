<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserLocationRequest;
use App\Models\UserLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserLocationController extends Controller
{
    /**
     * Listar todas las ubicaciones guardadas del usuario autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $locations = $request->user()
            ->locations()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return $this->success($locations, 'Ubicaciones obtenidas correctamente.');
    }

    /**
     * Guardar una nueva ubicación para el usuario autenticado.
     * Si is_default es true se desmarca la anterior ubicación predeterminada.
     */
    public function store(StoreUserLocationRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $location = DB::transaction(function () use ($user, $data) {
            if (($data['is_default'] ?? false)) {
                $user->locations()->where('is_default', true)->update(['is_default' => false]);
            }

            // Si es la primera ubicación, marcarla como default automáticamente
            if (! $user->locations()->exists()) {
                $data['is_default'] = true;
            }

            return $user->locations()->create($data);
        });

        return $this->success($location, 'Ubicación guardada correctamente.', 201);
    }

    /**
     * Establecer una ubicación como predeterminada.
     */
    public function setDefault(Request $request, UserLocation $location): JsonResponse
    {
        $this->authorize('update', $location);

        DB::transaction(function () use ($request, $location) {
            $request->user()
                ->locations()
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $location->update(['is_default' => true]);
        });

        return $this->success($location->fresh(), 'Ubicación predeterminada actualizada.');
    }

    /**
     * Eliminar una ubicación guardada.
     */
    public function destroy(Request $request, UserLocation $location): JsonResponse
    {
        $this->authorize('delete', $location);

        $wasDefault = $location->is_default;
        $location->delete();

        // Si se elimina la predeterminada, promover la más reciente
        if ($wasDefault) {
            $request->user()
                ->locations()
                ->latest()
                ->first()
                ?->update(['is_default' => true]);
        }

        return $this->success(null, 'Ubicación eliminada correctamente.');
    }
}
