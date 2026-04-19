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
            'user_name'    => ['nullable', 'string', 'max:50'],
            'rating'       => ['nullable', 'in:all,1,2,3,4,5'],
            'keyword'      => ['nullable', 'string', 'max:50'],
            'is_public'    => ['nullable', 'in:all,1,0'],
            'rows_per_page' => ['nullable', 'integer', 'in:20,50,100'],
        ]);

        $query = Review::query()
                        ->with('user')
                        ->with('reservation.space');

        // Filter by username
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->user_name . '%');
            });
        }
        // Filter by space's name
        if ($request->filled('space_name')) {
            $query->whereHas('reservation.space', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->space_name . '%');
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
        if ($request->filled('is_public') && $request->is_public !== 'all') {
            $query->where('is_public', $request->boolean('is_public'));
        }

        $rowsPerPage = (int)$request->input('rows_per_page', 20);

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
                        ->with('ok', 'Successfully hidden.');
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
        return redirect()->route('admin.reviews.index')->with('ok', 'Successfully shown.');
    }

}
