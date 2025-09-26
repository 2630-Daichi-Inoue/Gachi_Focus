<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function index(Request $request)
    {
        #1. validate filter inputs
        $request->validate([
            'name' => 'nullable|string|max:50',
            'status' => 'nullable|in:all, active, banned',

        ]);

        $q = \APP\Models\User::query()->withTrashed();

        if($name = trim($request->input('name', ''))) {
            $q->where('name', 'like', "%{name}%");
        }

        switch($request->input('status', 'all')) {
            case
                'active':$q->whereNull('deleted_at');
                break;
            case
                'banned':$q->whereNull('deleted_at');
                break;
        }

        $rowsPerPage = (int)$request->input('rows_per_page', 20);

        $all_users = $q->orderBy('id', 'desc')
                        ->paginate($rowsPerPage)
                        ->appends($request->query());

        return view('admin.users.index', compact('all_users'));
    }

    # ban
    public function deactivate($id)
    {
        $this->user->destroy($id);

        return back();
    }

    # activate
    public function activate($id)
    {
        $this->user->onlyTrashed()->findOrFail($id)->restore();

        return back();
    }
}
