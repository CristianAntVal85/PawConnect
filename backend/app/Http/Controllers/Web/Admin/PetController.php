<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\PetsExport;
use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PetController extends Controller
{
    public function index(Request $request)
    {
        $query = Pet::with('user', 'photos');

        // Búsqueda por nombre, especie, raza o dueño
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('species', 'like', "%{$search}%")
                ->orWhere('breed', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        }

        // Filtro por especie
        if ($request->filled('species') && $request->input('species') !== 'all') {
            $query->where('species', $request->input('species'));
        }

        // Filtro por disponibilidad para paseos
        if ($request->filled('available') && $request->input('available') !== 'all') {
            $available = $request->input('available') === 'yes';
            $query->where('available_for_walks', $available);
        }

        // Filtro por tamaño
        if ($request->filled('size') && $request->input('size') !== 'all') {
            $query->where('size', $request->input('size'));
        }

        $pets = $query->latest()->paginate(20);

        // Obtener especies únicas para el filtro
        $species = Pet::distinct()->pluck('species')->sort();
        $sizes = Pet::distinct()->pluck('size')->sort();

        return view('admin.pets.index', compact('pets', 'species', 'sizes'));
    }

    public function export()
    {
        return Excel::download(new PetsExport(), 'mascotas_' . now()->format('Y-m-d_H-i-s') . '.csv');
    }

    public function show(Pet $pet)
    {
        $pet->load('user', 'photos', 'healthRecords', 'walks', 'orders', 'matchesAsPetA', 'matchesAsPetB');

        return view('admin.pets.show', compact('pet'));
    }
}
