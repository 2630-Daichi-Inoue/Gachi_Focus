<?php
namespace App\Http\Controllers;

use App\Models\Utility;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function index()
    {
        $utilities = Utility::orderBy('name')->paginate(8);
        return view('utilities.index', compact('utilities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100','unique:utilities,name'],
        ]);

        Utility::create($data);
        return back()->with('success','Tag added.');
    }

    public function update(Request $request, Utility $utility)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100','unique:utilities,name,'.$utility->id],
        ]);

        $utility->update($data);
        return back()->with('success','Tag updated.');
    }

    public function destroy(Utility $utility)
    {
        $utility->delete();
        return back()->with('success','Tag deleted.');
    }
}

