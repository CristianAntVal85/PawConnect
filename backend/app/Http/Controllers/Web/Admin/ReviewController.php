<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\ReviewsExport;
use App\Http\Controllers\Controller;
use App\Models\PlaceReview;
use App\Notifications\ReviewStatusNotification;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews for moderation.
     */
    public function index(Request $request)
    {
        $query = PlaceReview::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: show pending and approved reviews
            $query->whereIn('status', ['pending', 'approved']);
        }

        // Search by place name or reviewer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('place', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Date range filtering
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reviews = $query->with('place', 'user')->orderBy('created_at', 'desc')->paginate(15);
        $statuses = ['pending' => 'Pendiente', 'approved' => 'Aprobada', 'rejected' => 'Rechazada'];

        return view('admin.reviews.index', compact('reviews', 'statuses'));
    }

    public function export()
    {
        return Excel::download(new ReviewsExport(), 'reseñas_' . now()->format('Y-m-d_H-i-s') . '.csv');
    }

    /**
     * Display the specified review.
     */
    public function show(PlaceReview $review)
    {
        $review->load(['place', 'user']);
        $statuses = ['pending' => 'Pendiente', 'approved' => 'Aprobada', 'rejected' => 'Rechazada'];

        return view('admin.reviews.show', compact('review', 'statuses'));
    }

    /**
     * Approve a review.
     */
    public function approve(Request $request, PlaceReview $review)
    {
        if ($review->status === 'approved') {
            return redirect()->back()->with('error', 'Esta reseña ya está aprobada.');
        }

        $review->update(['status' => 'approved', 'rejection_reason' => null]);

        // Notify reviewer
        if ($review->user) {
            $review->user->notify(new ReviewStatusNotification($review, 'approved'));
        }

        return redirect()->back()->with('success', "Reseña aprobada correctamente.");
    }

    /**
     * Reject a review.
     */
    public function reject(Request $request, PlaceReview $review)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($review->status === 'rejected') {
            return redirect()->back()->with('error', 'Esta reseña ya ha sido rechazada.');
        }

        $review->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Notify reviewer with reason
        if ($review->user) {
            $review->user->notify(new ReviewStatusNotification($review, 'rejected', $validated['rejection_reason']));
        }

        return redirect()->back()->with('success', "Reseña rechazada.");
    }
}
