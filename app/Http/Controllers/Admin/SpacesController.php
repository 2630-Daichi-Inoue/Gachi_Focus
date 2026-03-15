<?php


namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Amenity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class SpacesController extends Controller
{
    private $space;
    private $amenity;

    public function __construct(Space $space, Amenity $amenities)
    {
        $this->space = $space;
        $this->amenity = $amenities;
    }

    public function index()
    {
        $home_spaces = \App\Models\Space::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('users.home', compact('home_spaces'));
    }

    public function register()
    {
        $all_amenities = $this->amenity->all();

        return view('admin.spaces.register')
            ->with('all_amenities', $all_amenities);
    }

    public function store(Request $request)
    {

        # 1. Validate all form data
        $validated = $request->validate([
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
            'image' => 'required|image|mimes:jpeg,jpg,png,gif|max:1048'
        ], [
            'max_capacity.gte' => 'The Capacity(max) must be greater than or equal to Capacity(min).',
            'min_capacity.lte' => 'The Capacity(min) must be less than or equal to Capacity(max).',
            'max_capacity.required' => 'The capacity field is required.',
            'min_capacity.required' => 'The capacity field is required.'
        ]);
        // 画像保存（storage/app/public/spaces）
        $imagePath = $request->file('image')->store('spaces', 'public');
        // => "spaces/xxxxx.jpg"


        // 2) Save space（例外が出ないように丁寧に）
        $this->space->fill([
            'name' => $validated['name'],
            'location_for_overview' => $validated['location_for_overview'],
            'location_for_details'  => $validated['location_for_details'],
            'min_capacity' => $validated['min_capacity'],
            'max_capacity' => $validated['max_capacity'],
            'area' => $validated['area'],
            'weekday_price' => $validated['weekday_price'],
            'weekend_price' => $validated['weekend_price'],
            'description' => $validated['description'],
            'image' => $imagePath,
        ]);
        $this->space->save();

        # 3. save the amenities to amenity_space table
        // 3) amenities（null安全 & 初期化）
        $amenityIds = (array) $request->input('amenity', []);
        if ($amenityIds) {
            $amenity_space = array_map(fn($id) => ['amenity_id' => $id], $amenityIds);
            $this->space->amenitySpace()->createMany($amenity_space);
        }

        # 4. Go back to homepage
        return redirect()->route('index');
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

    public function update(Request $request, $id)
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
