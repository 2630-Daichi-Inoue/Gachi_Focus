<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function index(Request $request, $reservationId)
    {
        $reservation = Reservation::with('space')->findOrFail($reservationId);

        $query = Review::with('user')->where('space_id', $reservation->space_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->get('sort') === 'with'){
            $query->whereNotNull('photo');
        }

        switch ($request->get('sort')) {
            case 'highest':
                $query->orderByDesc('rating');
                break;
            case 'lowest':
                $query->orderBy('rating');
                break;
            case 'oldest':
                $query->oldest();
                break;
            default:
                $query->latest();
                break;
        }

        $reviews = $query->get();

        $averageRating = round($reviews->avg(function ($r) {
            return ($r->cleanliness + $r->conditions + $r->facilities) / 3;
        }) ?? 0, 1);

        $cleanliness = round($reviews->avg('cleanliness') ?? 0, 1);
        $conditions  = round($reviews->avg('conditions') ?? 0, 1);
        $facilities  = round($reviews->avg('facilities') ?? 0, 1);

        return view('reviews.index', compact(
            'reservation',
            'reviews',
            'averageRating',
            'cleanliness',
            'conditions',
            'facilities'
        ));
    }

    // Post
    public function store(Request $request, $reservationId)
    {
        $request->validate([
            'cleanliness' => 'required|numeric|min:1|max:5',
            'conditions'  => 'required|numeric|min:1|max:5',
            'facilities'  => 'required|numeric|min:1|max:5',
            'comment'     => 'nullable|string|max:500',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
        ]);

        $reservation = Reservation::findOrFail($reservationId);

        $rating = ($request->cleanliness + $request->conditions + $request->facilities) / 3;

        $photoPath = null;
        if ($request->hasFile('photo')){
            $photoPath = $request->file('photo')->store('reviews', 'public');
        }

        Review::create([
            'user_id'     => Auth::id(),
            'space_id'    => $reservation->space_id,
            'cleanliness' => $request->cleanliness,
            'conditions'  => $request->conditions,
            'facilities'  => $request->facilities,
            'rating'      => $rating,
            'comment'     => $request->comment,
            'photo'       => $photoPath,
        ]);

        return redirect()->route('reviews.index', $reservationId)->with('success', 'Thank you for your review!');
    }

    // Update
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'cleanliness' => 'required|numeric|min:1|max:5',
            'conditions'  => 'required|numeric|min:1|max:5',
            'facilities'  => 'required|numeric|min:1|max:5',
            'comment'     => 'nullable|string|max:500',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($review->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $rating = ($request->cleanliness + $request->conditions + $request->facilities) / 3;

        if ($request->filled('remove_photo') && $request->remove_photo == true) {
        if ($review->photo && Storage::disk('public')->exists($review->photo)) {
            Storage::disk('public')->delete($review->photo);
        }
        $review->photo = null;
    }

        if ($request->hasFile('photo')){
            if ($review->photo && Storage::disk('public')->exists($review->photo)){
                Storage::disk('public')->delete($review->photo);
            }

            $photoPath = $request->file('photo')->store('reviews', 'public');
            $review->photo = $photoPath;
        }

        $review->update([
            'cleanliness' => $request->cleanliness,
            'conditions'  => $request->conditions,
            'facilities'  => $request->facilities,
            'comment'     => $request->comment,
            'photo'       => $review->photo,
        ]);

        return back()->with('success', 'Your review has been updated!');
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== Auth::id()){
            abort(403, 'Unauthorized action.');
        }

        if ($review->photo && Storage::disk('public')->exists($review->photo)){
            Storage::disk('public')->delete($review->photo);
        }

        $review->delete();

        return back()->with('success', 'Your review has been deleted.');
    }
}
