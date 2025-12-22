<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\Review;
use App\Models\Utility;
use Illuminate\Http\Request;

class UserSpaceController extends Controller
{
    public function show($id)
    {
        $space = Space::with('photos')->findOrFail($id);

        $reviews = Review::where('space_id', $space->id)->get();
        $reviewCount = $reviews->count();

        $averageRating = round($reviews->avg(function ($r) {
            return ($r->cleanliness + $r->conditions + $r->facilities) / 3;
        }) ?? 0, 1);

        $space->rating = $averageRating;

        $utilities = Utility::orderBy('name')->get();

        return view('spaces.detail', compact(
            'space', 
            'reviews',
            'reviewCount',
            'averageRating',
            'utilities'
        ));
    }
}
