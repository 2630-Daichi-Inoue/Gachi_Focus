<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\Request;

class UserSpaceController extends Controller
{
    public function show($id)
    {
        $space = Space::with(['photos', 'reviews'])
        ->withCount('reviews')->findOrFail($id);

        $space->rating = $space->reviews->count() > 0
        ? round($space->reviews->avg('rating'), 1) : 0;
        
        return view('spaces.detail', compact('space'));
    }
}
