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
        $query = Space::query();

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
        $amenities = Amenity::orderBy('name')
                            ->select('id','name')
                            ->get();

        return view('admin.spaces.register', compact('amenities'));
    }

    public function store(StoreSpaceRequest $request)
    {

        # 1. Validate all form data
        $data = $request->validated();
        // store the uploaded image (storage/app/public/spaces)
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
        return redirect()->route('admin.spaces.index')->with('status', 'Successfully registered.');
    }

    public function edit(Space $space)
    {
        if ($space->trashed()) {
            return redirect()->route('admin.spaces.index')
                ->with('error', $space->name . ' has already been deleted.');
        }

        $space->load('amenities');

        $amenities = Amenity::orderBy('name')
                            ->select('id', 'name')
                            ->get();

        # Get all amenity IDs of the space.
        $selectedAmenityIds = $space->amenities->pluck('id')->toArray();

        return view('admin.spaces.edit', ['space' => $space, 'amenities' => $amenities, 'selectedAmenityIds' => $selectedAmenityIds]);
    }

    public function update(UpdateSpaceRequest $request, Space $space)
    {
        if ($space->trashed()) {
            return redirect()->route('admin.spaces.index')
                ->with('error', $space->name . ' has already been deleted.');
        }

        # 1. Validate all form data
        $data = $request->validated();

        # 2. Update the space data in the spaces table
        $space->fill ([
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
        ]);
        // if(isset($data['is_public'])) $space->is_public = $data['is_public'];

        // if the admin uploads a new image file
        if ($request->hasFile('image')) {
            // （任意）古い画像を削除：DBが相対パス運用のときだけ
            if (!empty($space->image_path)) {
                Storage::disk('public')->delete($space->image_path);
            }

            $space->image_path = $request->file('image')->store('spaces', 'public');
        }

        $space->save();

        # 3. Sync amenities to the pivot table
        $space->amenities()->sync($data['amenities'] ?? []);

        # 4. redirect to the index
        return redirect()->route('admin.spaces.index')->with('status', 'Successfully updated.');
    }

    public function hide(Space $space)
    {
        if ($space->trashed()) {
            return redirect()->route('admin.spaces.index')
                ->with('error', $space->name . ' has already been deleted.');
        }

        # 1. Update the space data in the spaces table
        $space->fill ([
            'is_public' => false,
        ]);

        $space->save();

        # 2. redirect to the index
        return redirect()->route('admin.spaces.index')->with('status', 'Successfully hidden.');
    }

    public function show(Space $space)
    {
        if ($space->trashed()) {
            return redirect()->route('admin.spaces.index')
                ->with('error', $space->name . ' has already been deleted.');
        }

        # 1. Update the space data in the spaces table
        $space->fill ([
            'is_public' => true,
        ]);

        $space->save();

        # 2. redirect to the index
        return redirect()->route('admin.spaces.index')->with('status', 'Successfully shown.');
    }

    public function destroy(Space $space)
    {
        $space->delete();
        return redirect()->route('admin.spaces.index')->with('status', 'Successfully deleted.');
    }
}
