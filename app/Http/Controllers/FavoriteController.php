<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function store(Space $space)
    {
        Favorite::firstOrCreate([
            'user_id' => Auth::id(),
            'space_id' => $space->id,
        ]);

        return back()->with('ok', 'Added to favorites. Thank you!');
    }

    public function destroy(Space $space)
    {
        Favorite::where('user_id', Auth::id())->where('space_id', $space->id)->delete();

        return back()->with('ok', 'Removed from favorites.');
    }
}
