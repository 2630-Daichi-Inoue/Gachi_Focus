<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Requests\StoreReviewRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class ReviewController extends Controller
{
    // public function index(Request $request, $spaceId)
    // {
    //     $query = Review::where('space_id', $spaceId);

    //     // --- Search ---
    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('comment', 'like', "%{$search}%")
    //                 ->orWhereHas('user', function ($q2) use ($search) {
    //                     $q2->where('name', 'like', "%{$search}%");
    //                 });
    //         });
    //     }

    //     // --- Sort by filter ---
    //     if ($request->get('sort') === 'with') {
    //         $query->whereNotNull('photo');
    //     }

    //     switch ($request->get('sort')) {
    //         case 'highest':
    //             $query->orderByDesc('rating');
    //             break;
    //         case 'lowest':
    //             $query->orderBy('rating');
    //             break;
    //         case 'oldest':
    //             $query->oldest();
    //             break;
    //         default:
    //             $query->latest();
    //             break;
    //     }

    //     $reviews = $query->get();

    //     // --- Calculate averages ---
    //     $averageRating = round($reviews->avg(function ($r) {
    //         return ($r->cleanliness + $r->conditions + $r->facilities) / 3;
    //     }) ?? 0, 1);

    //     $cleanliness = round($reviews->avg('cleanliness') ?? 0, 1);
    //     $conditions  = round($reviews->avg('conditions') ?? 0, 1);
    //     $facilities  = round($reviews->avg('facilities') ?? 0, 1);

    //     return view('reviews.index', compact(
    //         'space',
    //         'reviews',
    //         'averageRating',
    //         'cleanliness',
    //         'conditions',
    //         'facilities'
    //     ));
    // }

    public function createOrEdit(Reservation $reservation)
    {
        $review = Review::where('user_id', Auth::id())
                        ->where('reservation_id', $reservation->id)
                        ->withTrashed()
                        ->first();

        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to review this reservation.');
        }

        if ($reservation->reservation_status === 'canceled' || Carbon::parse($reservation->ended_at)->isFuture()) {
            return back()->with('error', 'You can review only completed reservations.');
        }

        if ($review && $review->deleted_at !== null) {
            return back()->with('error', 'You have already deleted your review for this reservation. You cannot write a new one.');
        }

        $reservation->load('space');

        if($review) {
            return Inertia::render('Reviews/Edit', [
                'reservation' => $reservation,
                'review' => $review
            ]);
        } else {
            return Inertia::render('Reviews/Create', [
                'reservation' => $reservation
            ]);
        }
    }

    public function store(StoreReviewRequest $request, Reservation $reservation)
    {
        $data = $request->validated();

        $existingReview = Review::where('user_id', Auth::id())
                                ->where('reservation_id', $reservation->id)
                                ->withTrashed()
                                ->first();

        if ($existingReview) {
            return redirect()->route('reservations.index')
                            ->with('error', 'You have already reviewed this reservation.');
        }

        Review::create([
            'user_id'           => Auth::id(),
            'reservation_id'    => $reservation->id,
            'rating'            => $data['rating'],
            'comment'           => $data['comment'],
            'is_public'         => true,
        ]);

        return redirect()->route('spaces.reviewIndex', $reservation->space_id)
                        ->with('ok', 'Thank you for your review!');
    }

    public function update(StoreReviewRequest $request, Reservation $reservation)
    {
        $data = $request->validated();

        $review = Review::where('user_id', Auth::id())
                        ->where('reservation_id', $reservation->id)
                        ->withTrashed()
                        ->first();

        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to update this review.');
        }

        if ($reservation->reservation_status === 'canceled' || Carbon::parse($reservation->ended_at)->isFuture()) {
            return back()->with('error', 'You can review only completed reservations.');
        }

        if ($review->deleted_at !== null) {
            return back()->with('error', 'You have already deleted your review for this reservation. You cannot write a new one.');
        }

        $review->update([
            'rating'      => $data['rating'],
            'comment'     => $data['comment'],
        ]);

        return redirect()->route('spaces.reviewIndex', $reservation->space_id)
                        ->with('ok', 'Your review has been updated!');
    }

    public function destroy(Reservation $reservation)
    {
        $review = Review::where('user_id', Auth::id())
                        ->where('reservation_id', $reservation->id)
                        ->withTrashed()
                        ->first();

        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to delete this review.');
        }

        if (!$review) {
            return redirect()->route('reservations.index')->with('error', 'You have not reviewed this reservation yet.');
        }

        if ($review->deleted_at !== null) {
            return redirect()->route('reservations.index')->with('error', 'You have already deleted your review for this reservation.');
        }

        $review->update([
            'is_public' => false,
        ]);

        $review->delete();

        return redirect()->route('spaces.reviewIndex', $reservation->space_id)
                        ->with('ok', 'Your review has been deleted.');
    }
}
