<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pets = collect();

        if (Auth::check()) {
            $pets = Pet::query()
                ->with(['photos'])
            ->where('user_id', Auth::id())
                ->latest()
                ->take(8)
                ->get();
        }

        return view('dashboard.index', [
            'pets' => $pets,
        ]);
    }
}
