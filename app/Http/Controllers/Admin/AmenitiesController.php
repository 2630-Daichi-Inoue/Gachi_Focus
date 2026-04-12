<?php

namespace App\Http\Controllers\Admin;

use App\Models\Amenity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class AmenitiesController extends Controller
{
    public function index()
    {
        $amenities = Amenity::orderBy('name')->paginate(10);
        return view('admin.amenities.index', compact('amenities'));
    }

    public function create()
    {
        // Nothing goes here since we will use a modal for creating amenities
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required',
                        'string',
                        'max:100',
                        'unique:amenities,name'],
        ]);

        Amenity::create($data);
        return back()->with('success','Successfully added.');
    }

    public function show()
    {
        // Nothing goes here since we will not have a separate page for showing an amenity
    }

    public function edit()
    {
        // Nothing goes here since we will use a modal for editing amenities
    }

    public function update(Request $request, Amenity $amenity)
    {
        $data = $request->validate([
            // 'name' => ['required','string','max:100','unique:amenities,name,'.$amenity->id],
            'name' => ['required',
                        'string',
                        'max:100',
                        Rule::unique('amenities', 'name')->ignore($amenity)],
        ]);

        $amenity->update($data);
        return back()->with('success','Successfully updated.');
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->forceDelete();
        return back()->with('success','Successfully deleted.');
    }
}
