<?php


namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Amenity;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpaceRequest;
use App\Http\Requests\UpdateSpaceRequest;

class SpacesController extends Controller
{

    public function index(Request $request)
    {
        $query = Space::withTrashed();
        // Filter by name
        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }
        // Filter by prefecture
        if ($request->filled('prefecture')) {
            $query->where('prefecture', $request->prefecture);
        }
        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', 'LIKE', '%' . $request->city . '%');
        }
        // Filter by address_line
        if ($request->filled('address_line')) {
            $query->where('address_line', 'LIKE', '%' . $request->address_line . '%');
        }
        // Filter by is_public
        if ($request->filled('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        $spaces = $query
                    ->latest()
                    ->paginate(10);

        $prefectures = Space::select('prefecture')
                        ->distinct()
                        ->orderBy('prefecture')
                        ->pluck('prefecture');

        return view('admin.spaces.index', compact('spaces', 'prefectures'));
    }

    public function register()
    {
        $all_amenities = Amenity::orderBy('name')->select('id','name')->get();

        return view('admin.spaces.register', compact('all_amenities'));
    }


    public function store(StoreSpaceRequest $request)
    {

        # 1. Validate all form data
        $data = $request->validated();
        // 画像保存（storage/app/public/spaces）
        $imagePath = $request->file('image')->store('spaces', 'public');

        # 2. Save space data to spaces table
        $space= Space::create([
            'name' => $data['name'],
            'prefecture' => $data['prefecture'],
            'city'  => $data['city'],
            'address_line' => $data['address_line'],
            'capacity' => $data['capacity'],
            'open_time' => $data['open_time'],
            'close_time' => $data['close_time'],
            'weekend_price_yen' => $data['weekend_price_yen'],
            'weekday_price_yen' => $data['weekday_price_yen'],
            'description' => $data['description'],
            'image_path' => $imagePath,
            'is_public' => $data['is_public'] ?? true,
        ]);

        # 3. Sync amenities to the pivot table
        $space->amenities()->sync($data['amenities'] ?? []);

        # 4. Redirect back to the spaces list with a success message
        return redirect()->route('admin.spaces.index')->with('status', 'Space registered.');
    }

    public function edit($id)
    {
        $space = $this->space
            ->withTrashed()
            ->findOrFail($id);

        $all_amenities = $this->amenity->all();

        # get all amenity IDs of the space, and save in an array.
        $selected_amenities = [];
        foreach ($space->amenitySpace as $amenity_space) {
            $selected_amenities[] = $amenity_space->amenity_id;
        }

        return view('admin.spaces.edit')
            ->with('space', $space)
            ->with('all_amenities', $all_amenities)
            ->with('selected_amenities', $selected_amenities);
    }

    public function update(UpdateSpaceRequest $request, $id)
    {
        #1. Validate the data
        $request->validate([
            'name' => 'required|min:1|max:50',
            'location_for_overview' => 'required|min:1|max:50',
            'location_for_details' => 'required|min:1|max:100',
            'min_capacity' => 'required|integer|min:1|max:99|lte:max_capacity',
            'max_capacity' => 'required|integer|min:1|max:99|gte:min_capacity',
            'area' => 'required|numeric|min:1|max:9999.99',
            'weekday_price' => 'required|numeric|min:10|max:999999',
            'weekend_price' => 'required|numeric|min:10|max:999999',
            'description' => 'required|min:1|max:1000',
            'amenity' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:1048',
        ], [
            'max_capacity.gte' => 'The Capacity(max) must be greater than or equal to Capacity(min).',
            'min_capacity.lte' => 'The Capacity(min) must be less than or equal to Capacity(max).',
            'max_capacity.required' => 'The capacity field is required.',
            'min_capacity.required' => 'The capacity field is required.'
        ]);


        # 2. Update the space
        $space = $this->space->findOrFail($id);
        $space->name = $request->name;
        $space->location_for_overview = $request->location_for_overview;
        $space->location_for_details = $request->location_for_details;
        $space->min_capacity = $request->min_capacity;
        $space->max_capacity = $request->max_capacity;
        $space->area = $request->area;
        $space->weekday_price = $request->weekday_price;
        $space->weekend_price = $request->weekend_price;
        $space->description = $request->description;

        // if the admin uploaded image
        if ($request->hasFile('image')) {
            // （任意）古い画像を削除：DBが相対パス運用のときだけ
            if (!empty($space->image) && $space->image !== '0' && !str_starts_with($space->image, 'http')) {
                Storage::disk('public')->delete($space->image);
            }

            $space->image = $request->file('image')->store('spaces', 'public');
        }

        $space->save();

        # 3. Delete all the recrods from amenity_space related to the space
        $space->amenitySpace()->delete();

        # 4. save the new amenities to amenity_space table
        foreach ($request->input('amenity', []) as $amenityId) {
            $newamenities[] = ['amenity_id' => (int)$amenityId];
        }
        if (!empty($newamenities)) {
            $space->amenitySpace()->createMany($newamenities);
        }

        # 5, redirect to the index
        return redirect()->route('index')->with('status', 'Space updated.');
    }

    public function destroy($id)
    {
        $space = \App\Models\Space::withTrashed()->findOrFail($id);

        if ($space->trashed()) {
            $space->restore();
            // return redirect()->route('index')->with('status', 'Space deleted.');
        } else {
            $space->delete();
        }
        return redirect()->route('index');
    }
}
