<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PetController extends Controller
{
    public function index(): View
    {
        $pets = collect();

        if (Auth::check()) {
            $pets = Pet::query()
                ->with(['photos'])
            ->where('user_id', Auth::id())
                ->latest()
                ->get();
        }

        return view('pets.index', [
            'pets' => $pets,
        ]);
    }

    public function create(): View
    {
        return view('pets.create');
    }

    public function show(Pet $pet): View
    {
        $pet->load(['photos', 'healthRecords']);

        return view('pets.show', [
            'pet' => $pet,
            'healthRecords' => $pet->healthRecords,
        ]);
    }

    public function edit(Pet $pet): View
    {
        return view('pets.edit', [
            'pet' => $pet,
        ]);
    }
}
