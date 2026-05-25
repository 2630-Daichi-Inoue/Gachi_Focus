<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function createOrEdit(Reservation $reservation)
    {
        $this->authorize('review', $reservation);

        if (! $reservation->isReviewable()) {
            return back()->with('error', 'You can review only completed reservations.');
        }

        $review = Review::where('user_id', Auth::id())
            ->where('reservation_id', $reservation->id)
            ->withTrashed()
            ->first();

        if ($review && $review->trashed()) {
            return back()->with('error', 'You have already deleted your review for this reservation. You cannot write a new one.');
        }

        $reservation->load('space');

        if ($review) {
            return Inertia::render('Reviews/Edit', [
                'reservation' => $reservation,
                'review' => $review,
            ]);
        }

        return Inertia::render('Reviews/Create', [
            'reservation' => $reservation,
        ]);
    }

    public function store(StoreReviewRequest $request, Reservation $reservation)
    {
        $this->authorize('review', $reservation);

        if (! $reservation->isReviewable()) {
            return back()->with('error', 'You can review only completed reservations.');
        }

        if (Auth::user()->isRestricted()) {
            return redirect()->route('reservations.index')
                ->with('error', 'Your account is currently restricted. New reviews cannot be submitted.');
        }

        $existingReview = Review::where('user_id', Auth::id())
            ->where('reservation_id', $reservation->id)
            ->withTrashed()
            ->first();

        if ($existingReview) {
            return redirect()->route('reservations.index')
                ->with('error', 'You have already reviewed this reservation.');
        }

        $data = $request->validated();

        Review::create([
            'user_id' => Auth::id(),
            'reservation_id' => $reservation->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'is_public' => true,
        ]);

        return redirect()->route('spaces.reviewIndex', $reservation->space_id)
            ->with('ok', 'Thank you for your review!');
    }

    public function update(StoreReviewRequest $request, Reservation $reservation)
    {
        $this->authorize('review', $reservation);

        if (! $reservation->isReviewable()) {
            return back()->with('error', 'You can review only completed reservations.');
        }

        $review = Review::where('user_id', Auth::id())
            ->where('reservation_id', $reservation->id)
            ->withTrashed()
            ->first();

        if (! $review || $review->trashed()) {
            return back()->with('error', 'You have already deleted your review for this reservation. You cannot write a new one.');
        }

        $data = $request->validated();

        $review->update([
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);

        return redirect()->route('spaces.reviewIndex', $reservation->space_id)
            ->with('ok', 'Your review has been updated!');
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('review', $reservation);

        $review = Review::where('user_id', Auth::id())
            ->where('reservation_id', $reservation->id)
            ->withTrashed()
            ->first();

        if (! $review) {
            return redirect()->route('reservations.index')
                ->with('error', 'You have not reviewed this reservation yet.');
        }

        if ($review->trashed()) {
            return redirect()->route('reservations.index')
                ->with('error', 'You have already deleted your review for this reservation.');
        }

        $review->update(['is_public' => false]);
        $review->delete();

        return redirect()->route('spaces.reviewIndex', $reservation->space_id)
            ->with('ok', 'Your review has been deleted.');
    }
}
