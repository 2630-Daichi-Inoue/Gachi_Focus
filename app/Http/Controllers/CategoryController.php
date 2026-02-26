<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->paginate(8);
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100','unique:categories,name'],
        ]);

        Category::create($data);
        return back()->with('success','Tag added.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100','unique:categories,name,'.$category->id],
        ]);

        $category->update($data);
        return back()->with('success','Tag updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success','Tag deleted.');
    }
}

