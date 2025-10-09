<?php


namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class SpacesController extends Controller
{
    private $space;
    private $category;


    public function __construct(Space $space, Category $category){
        $this->space = $space;
        $this->category = $category;
    }


    public function register()
    {
        $all_categories = $this->category->all();

        return view('admin.spaces.register')
                ->with('all_categories', $all_categories);
    }


    public function store(Request $request){
       
        # 1. Validate all form data
        $request->validate([
            'name' => 'required|min:1|max:50',
            'location_for_overview' => 'required|min:1|max:50',
            'location_for_details' => 'required|min:1|max:100',
            'min_capacity' => 'required|integer|min:1|max:99|lte:max_capacity',
            'max_capacity' => 'required|integer|min:1|max:99|gte:min_capacity',
            'area' => 'required|numeric|min:1|max:1000.00',
            'price' => 'required|numeric|min:1|max:100.00',
            'description' => 'required|min:1|max:1000',
            'category' => 'nullable|array',
            'image' => 'required|mimes:jpeg,jpg,png,gif|max:1048'
        ], [
            'max_capacity.gte' => 'The Capacity(max) must be greater than or equal to Capacity(min).',
            'min_capacity.lte' => 'The Capacity(min) must be less than or equal to Capacity(max).',
            'max_capacity.required' => 'The capacity field is required.',
            'min_capacity.required' => 'The capacity field is required.'
        ]);


        # 2. Save the space
        $this->space->name = $request->name;
        $this->space->location_for_overview = $request->location_for_overview;
        $this->space->location_for_details = $request->location_for_details;
        $this->space->min_capacity = $request->min_capacity;
        $this->space->max_capacity = $request->max_capacity;
        $this->space->area = $request->area;
        $this->space->price = $request->price;
        $this->space->description = $request->description;
        $this->space->image = 'data:image/' . $request->image->extension() . ';base64,' . base64_encode(file_get_contents($request->image));
        $this->space->save();


        # 3. save the categories to category_space table
        foreach($request->category as $category_id){
            $category_space[] = ['category_id' => $category_id];
        }
        $this->space->categorySpace()->createMany($category_space);


        # 4. Go back to homepage
        return redirect()->route('index');
    }


    public function edit($id)
    {
        $space = $this->space
                    ->withTrashed()
                    ->findOrFail($id);


        #if the auth user is NOT the owner, redirect to homepage
        // != NOT EQUAL
        // if(Auth::user()->id != $space->user->id)
        // {
        //     return redirect()->route('index');
        // }


        $all_categories = $this->category->all();


        # get all category IDS of the post. save in an array
        $selected_categories = [];
        // post 1 has category 4, 6
        // $post->categoryPost = [4,6]
        foreach($space->categorySpace as $category_space){
            $selected_categories[] = $category_space->category_id;
        }


        return view('admin.spaces.edit')
                ->with('space', $space)
                ->with('all_categories', $all_categories)
                ->with('selected_categories', $selected_categories);
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
            'area' => 'required|numeric|min:1|max:1000.00',
            'price' => 'required|numeric|min:1|max:100.00',
            'description' => 'required|min:1|max:1000',
            'category' => 'nullable|array',
            'image' => 'nullable|mimes:jpeg,jpg,png,gif|max:1048'
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
        $space->price = $request->price;
        $space->description = $request->description;


        // if the admin uploaded image
        if($request->image){
            $space->image = "data:image/" . $request->image->extension() . ';base64,' . base64_encode(file_get_contents($request->image));
        }


        $space->save();


        # 3. Delete all the recrods from category_space related to the space
        $space->categorySpace()->delete();


        # 4. save the new categories to category_space table
        foreach($request->input('category', []) as $categoryId){
            $newCategories[] = ['category_id' => (int)$categoryId];
        }
        if(!empty($newCategories)) {
            $space->categorySpace()->createMany($newCategories);
        }


        # 5, redirect to the index
        return redirect()->route('index')->with('status', 'Space updated.');
        // return redirect()->route('post.show', $id);
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
