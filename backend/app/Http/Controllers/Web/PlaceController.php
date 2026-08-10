<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class PlaceController extends Controller
{
    /**
     * Mostrar mapa interactivo y listado de lugares.
     */
    public function index(Request $request)
    {
        $query = [
            'category' => $request->input('category'),
            'search'   => $request->input('search'),
        ];

        // Obtener coordenadas del usuario si está disponible
        if ($request->filled('lat') && $request->filled('lng')) {
            $query['lat']    = $request->input('lat');
            $query['lng']    = $request->input('lng');
            $query['radius'] = $request->input('radius', 50);
        }

        // Filtrar nulls
        $query = array_filter($query);

        try {
            $response = Http::withToken(auth()->user()?->createToken('web')->plainTextToken ?? null)
                ->get(config('app.url') . '/api/v1/places', $query);

            if ($response->ok()) {
                $data = $response->json();
                $places = $data['data'] ?? [];
                $pagination = $data['pagination'] ?? null;
            } else {
                $places = [];
                $pagination = null;
            }
        } catch (\Exception $e) {
            $places = [];
            $pagination = null;
        }

        $categories = [
            'pipican'    => 'Piscina para perros',
            'beach'      => 'Playa',
            'park'       => 'Parque',
            'vet'        => 'Veterinaria',
            'shop'       => 'Tienda',
            'restaurant' => 'Restaurante',
            'hotel'      => 'Hotel',
        ];

        return view('places.index', [
            'places'       => $places,
            'pagination'   => $pagination,
            'categories'   => $categories,
            'selectedCategory' => $request->input('category'),
            'searchTerm'   => $request->input('search'),
        ]);
    }

    /**
     * Mostrar formulario para crear sugerencia de lugar.
     */
    public function create()
    {
        $categories = [
            'pipican'    => 'Piscina para perros',
            'beach'      => 'Playa',
            'park'       => 'Parque',
            'vet'        => 'Veterinaria',
            'shop'       => 'Tienda',
            'restaurant' => 'Restaurante',
            'hotel'      => 'Hotel',
        ];

        return view('places.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Guardar nueva sugerencia de lugar.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|in:pipican,beach,park,vet,shop,restaurant,hotel',
            'description' => 'required|string|max:1000',
            'lat'         => 'required|numeric|between:-90,90',
            'lng'         => 'required|numeric|between:-180,180',
            'address'     => 'required|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'website'     => 'nullable|url',
            'photo'       => 'nullable|image|max:5120',
        ]);

        try {
            $response = Http::withToken(auth()->user()->createToken('web')->plainTextToken)
                ->post(config('app.url') . '/api/v1/places', $validated);

            if ($response->ok() || $response->status() === 201) {
                return redirect()->route('places.index')
                    ->with('success', 'Lugar sugerido exitosamente. Pendiente de aprobación del administrador.');
            } else {
                return back()->withErrors(['error' => 'Error al sugerir el lugar.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error de conexión: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar detalles del lugar.
     */
    public function show($id)
    {
        try {
            $response = Http::get(config('app.url') . '/api/v1/places/' . $id);

            if ($response->ok()) {
                $place = $response->json('data');
            } else {
                abort(404, 'Lugar no encontrado');
            }
        } catch (\Exception $e) {
            abort(500, 'Error al obtener el lugar');
        }

        $isFavorite = false;
        if (auth()->check()) {
            try {
                $favResponse = Http::withToken(auth()->user()->createToken('web')->plainTextToken)
                    ->get(config('app.url') . '/api/v1/places/favorites');

                if ($favResponse->ok()) {
                    $favorites = $favResponse->json('data');
                    $isFavorite = collect($favorites)->contains('id', $place['id']);
                }
            } catch (\Exception $e) {
                // Sin favoritos
            }
        }

        return view('places.show', [
            'place'       => $place,
            'isFavorite'  => $isFavorite,
        ]);
    }

    /**
     * Mostrar lugares favoritos del usuario.
     */
    public function favorites(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        try {
            $response = Http::withToken(auth()->user()->createToken('web')->plainTextToken)
                ->get(config('app.url') . '/api/v1/places/favorites', [
                    'page' => $request->input('page', 1),
                ]);

            if ($response->ok()) {
                $data = $response->json();
                $favorites = $data['data'] ?? [];
                $pagination = $data['pagination'] ?? null;
            } else {
                $favorites = [];
                $pagination = null;
            }
        } catch (\Exception $e) {
            $favorites = [];
            $pagination = null;
        }

        return view('places.favorites', [
            'favorites'  => $favorites,
            'pagination' => $pagination,
        ]);
    }
}
