<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $request->validate([
            'userName'     => ['nullable', 'string', 'max:50'],
            'rating'       => ['nullable', 'in:all, 1, 2, 3, 4, 5'],
            'keyword'      => ['nullable', 'string', 'max:50'],
            'isPublic'     => ['nullable', 'in:all, 1, 0'],
            'rowsPerPage'  => ['nullable', 'integer', 'in:20, 50, 100'],
        ]);

        $query = Review::query()
                        ->with('user')
                        ->with('reservation.space');

        // Filter by username
        if ($request->filled('userName')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->userName . '%');
            });
        }
        // Filter by space's name
        if ($request->filled('spaceName')) {
            $query->whereHas('reservation.space', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->spaceName . '%');
            });
        }
        // Filter by rating
        if ($request->filled('rating') && $request->rating !== 'all') {
            $query->where('rating', $request->rating);
        }
        // Filter by keyword in comment
        if ($request->filled('keyword')) {
            $query->where('comment', 'LIKE', '%' . $request->keyword . '%');
        }
        // Filter by is_public status
        if ($request->filled('isPublic') && $request->isPublic !== 'all') {
            $query->where('is_public', $request->boolean('isPublic'));
        }

        $rowsPerPage = (int)$request->input('rowsPerPage', 20);

        $reviews = $query
                    ->latest()
                    ->paginate($rowsPerPage);

        return view('admin.reviews.index', compact('reviews', 'rowsPerPage'));
    }

    public function hide(Review $review)
    {
        if ($review->trashed()) {
            return redirect()->route('admin.reviews.index')
                ->with('error', $review->id . ' has already been deleted.');
        }

        # 1. Update the review data in the reviews table
        $review->fill ([
            'is_public' => false,
        ]);

        $review->save();

        # 2. redirect to the index
        return redirect()->route('admin.reviews.index')
                        ->with('status', 'Successfully hidden.');
    }

    public function show(Review $review)
    {
        if ($review->trashed()) {
            return redirect()->route('admin.reviews.index')
                ->with('error', $review->id . ' has already been deleted.');
        }

        # 1. Update the review data in the reviews table
        $review->fill ([
            'is_public' => true,
        ]);

        $review->save();

        # 2. redirect to the index
        return redirect()->route('admin.reviews.index')->with('status', 'Successfully shown.');
    }

}
