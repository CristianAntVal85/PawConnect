<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreWebPetRequest;
use App\Http\Requests\Web\UpdateWebPetRequest;
use App\Models\Pet;
use App\Models\PetPhoto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PetController extends Controller
{
    public function index(): View
    {
        $query = Pet::query()
            ->with(['photos'])
            ->where('user_id', Auth::id());

        // Filtro por nombre o raza
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('breed', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por especie
        if (request('species')) {
            $query->where('species', request('species'));
        }

        $pets = $query->latest()->get();

        return view('pets.index', compact('pets'));
    }


    public function create(): View
    {
        return view('pets.create');
    }

    public function store(StoreWebPetRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $pet = Pet::query()->create([
            'user_id' => Auth::id(),
            'species' => $data['species'],
            'name' => $data['name'],
            'breed' => $data['breed'] ?? null,
            'age' => $data['age'] ?? null,
            'weight' => $data['weight'] ?? null,
            'size' => $data['size'],
            'energy_level' => $data['energy_level'],
            'personality' => $data['personality'] ?? null,
            'available_for_walks' => $request->boolean('available_for_walks'),
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('pets', 'public');

            PetPhoto::query()->create([
                'pet_id' => $pet->id,
                'path' => $path,
                'display_order' => 1,
            ]);
        }

        return redirect()
            ->route('web.pets.index')
            ->with('success', 'Mascota creada correctamente.');
    }

    public function show(Pet $pet): View
    {
        abort_unless((int) $pet->user_id === (int) Auth::id(), 403);

        $pet->load(['photos', 'healthRecords']);

        return view('pets.show', [
            'pet' => $pet,
            'healthRecords' => $pet->healthRecords,
        ]);
    }

    public function edit(Pet $pet): View
    {
        abort_unless((int) $pet->user_id === (int) Auth::id(), 403);

        return view('pets.edit', [
            'pet' => $pet,
        ]);
    }

    public function update(UpdateWebPetRequest $request, Pet $pet): RedirectResponse
    {
        abort_unless((int) $pet->user_id === (int) Auth::id(), 403);

        $data = $request->validated();

        $pet->update([
            'species' => $data['species'],
            'name' => $data['name'],
            'breed' => $data['breed'] ?? null,
            'age' => $data['age'] ?? null,
            'weight' => $data['weight'] ?? null,
            'size' => $data['size'],
            'energy_level' => $data['energy_level'],
            'personality' => $data['personality'] ?? null,
            'available_for_walks' => $request->boolean('available_for_walks'),
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('pets', 'public');

            $nextOrder = (int) ($pet->photos()->max('display_order') ?? 0) + 1;

            PetPhoto::query()->create([
                'pet_id' => $pet->id,
                'path' => $path,
                'display_order' => $nextOrder,
            ]);
        }

        return redirect()
            ->route('web.pets.show', $pet)
            ->with('success', 'Mascota actualizada correctamente.');
    }
}
