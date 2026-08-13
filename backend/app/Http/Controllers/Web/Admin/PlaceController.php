<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetFriendlyPlace;
use App\Notifications\PlaceStatusNotification;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    /**
     * Display a listing of pending and approved places.
     */
    public function index(Request $request)
    {
        $query = PetFriendlyPlace::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: show pending and approved places
            $query->whereIn('status', ['pending', 'approved']);
        }

        // Search by name, category, or creator
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhereHas('creator', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Date range filtering
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $places = $query->with('creator')->orderBy('created_at', 'desc')->paginate(15);
        $statuses = ['pending' => 'Pendiente', 'approved' => 'Aprobado', 'rejected' => 'Rechazado'];
        $categories = ['pipican', 'beach', 'park', 'vet', 'shop', 'restaurant', 'hotel'];

        return view('admin.places.index', compact('places', 'statuses', 'categories'));
    }

    /**
     * Display the specified place.
     */
    public function show(PetFriendlyPlace $place)
    {
        $place->load(['creator', 'reviews.user', 'favoritedBy']);
        $statuses = ['pending' => 'Pendiente', 'approved' => 'Aprobado', 'rejected' => 'Rechazado'];

        return view('admin.places.show', compact('place', 'statuses'));
    }

    /**
     * Approve a pending place.
     */
    public function approve(Request $request, PetFriendlyPlace $place)
    {
        if ($place->status === 'approved') {
            return redirect()->back()->with('error', 'Este lugar ya está aprobado.');
        }

        $place->update(['status' => 'approved']);

        // Notify creator
        if ($place->creator) {
            $place->creator->notify(new PlaceStatusNotification($place, 'approved'));
        }

        return redirect()->back()->with('success', "Lugar '{$place->name}' aprobado correctamente.");
    }

    /**
     * Reject a pending place.
     */
    public function reject(Request $request, PetFriendlyPlace $place)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($place->status === 'rejected') {
            return redirect()->back()->with('error', 'Este lugar ya ha sido rechazado.');
        }

        $place->update(['status' => 'rejected']);

        // Notify creator with reason
        if ($place->creator) {
            $place->creator->notify(new PlaceStatusNotification($place, 'rejected', $validated['rejection_reason']));
        }

        return redirect()->back()->with('success', "Lugar '{$place->name}' rechazado.");
    }
}
