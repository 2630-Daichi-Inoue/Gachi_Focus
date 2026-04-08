<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Requests\StoreReviewRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

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
                    ->where('space_id', $reservation->space_id)
                    ->first();

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

    // post
    public function store(StoreReviewRequest $request, $reservationId)
    {

        $data = $request->validated();

        $reservation = Reservation::findOrFail($reservationId);

        Review::create([
            'reservation_id'    => $reservation->id,
            'rating'            => $data['rating'],
            'comment'           => $data['comment'],
            'is_public'         => true,
        ]);

        // --- Update average rating in spaces table ---
        $average = Review::where('space_id', $reservation->space_id)->avg('rating');
        $reservation->space->update(['rating' => round($average ?? 0, 1)]);

        return redirect()->route('reviews.index', ['space' => $reservation->space_id])
            ->with('success', 'Thank you for your review!');
    }

    // update
    public function update(StoreReviewRequest $request, Review $review)
    {
        $data = $request->validated();

        if ($review->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $rating = ($request->cleanliness + $request->conditions + $request->facilities) / 3;

        // --- Photo removal ---
        if ($request->filled('remove_photo') && $request->remove_photo == true) {
            if ($review->photo && Storage::disk('public')->exists($review->photo)) {
                Storage::disk('public')->delete($review->photo);
            }
            $review->photo = null;
        }

        // --- New photo upload ---
        if ($request->hasFile('photo')) {
            if ($review->photo && Storage::disk('public')->exists($review->photo)) {
                Storage::disk('public')->delete($review->photo);
            }
            $photoPath = $request->file('photo')->store('reviews', 'public');
            $review->photo = $photoPath;
        }

        // --- Update review ---
        $review->update([
            'cleanliness' => $request->cleanliness,
            'conditions'  => $request->conditions,
            'facilities'  => $request->facilities,
            'rating'      => $rating,
            'comment'     => $request->comment,
            'photo'       => $review->photo,
        ]);

        // --- Update average rating in spaces table ---
        $average = Review::where('space_id', $review->space_id)->avg('rating');
        $review->space->update(['rating' => round($average ?? 0, 1)]);

        return back()->with('success', 'Your review has been updated!');
    }

    // delete
    public function destroy(Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($review->photo && Storage::disk('public')->exists($review->photo)) {
            Storage::disk('public')->delete($review->photo);
        }

        $spaceId = $review->space_id;
        $review->delete();

        // --- Update average rating in spaces table ---
        $average = Review::where('space_id', $spaceId)->avg('rating');
        $review->space->update(['rating' => round($average ?? 0, 1)]);

        return back()->with('success', 'Your review has been deleted.');
    }
}
