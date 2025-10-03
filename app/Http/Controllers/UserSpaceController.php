<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\Request;

class UserSpaceController extends Controller
{
    public function show($id)
    {
        $space = Space::with('photos')->findOrFail($id);
        
        return view('spaces.detail', compact('space'));
    }
}
