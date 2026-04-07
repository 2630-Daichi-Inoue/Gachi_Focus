<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function index(Request $request)
    {
        $userStatusList = ['active','restricted','banned'];

        $request->validate([
            'name'          => ['nullable', 'string', 'max:50'],
            'email'         => ['nullable', 'string', 'max:100'],
            'user_status'   => ['nullable', Rule::in(array_merge(['all'], $userStatusList))],
            'rows_per_page' => ['nullable', 'integer', 'in:20,50,100'],
        ]);

        // Exclude admin
        $query = User::query()
                ->where('is_admin', false);

        // Filter by name
        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }
        // Filter by email
        if ($request->filled('email')) {
            $query->where('email', 'LIKE', '%' . $request->email . '%');
        }
        // Filter by user_status
        $userStatus = $request->input('user_status', 'all');
        if($userStatus !== 'all') {
            $query->where('user_status', $userStatus);
        }

        $rowsPerPage = (int)$request->input('rows_per_page', 20);

        $users = $query
                    ->latest()
                    ->paginate($rowsPerPage);

        return view('admin.users.index', compact('users', 'rowsPerPage'));
    }

    # Restrict User
    public function restrict(User $user)
    {
        if ($user->trashed()) {
            return redirect()->route('admin.users.index')
                ->with('error', $user->name . ' has already been deleted.');
        }

        # 1. Update the user data in the users table
        $user->fill ([
            'user_status' => 'restricted',
        ]);

        $user->save();

        # 2. redirect to the index
        return redirect()->route('admin.users.index')
                        ->with('status', 'Successfully restricted.');
    }

    # Activate User
    public function activate(User $user)
    {
        if ($user->trashed()) {
            return redirect()->route('admin.users.index')
                ->with('error', $user->name . ' has already been deleted.');
        }

        # 1. Update the user data in the users table
        $user->fill ([
            'user_status' => 'active',
        ]);

        $user->save();

        # 2. redirect to the index
        return redirect()->route('admin.users.index')
                        ->with('status', 'Successfully activated.');
    }

    # Ban User
    public function ban(User $user)
    {
        if ($user->trashed()) {
            return redirect()->route('admin.users.index')
                ->with('error', $user->name . ' has already been deleted.');
        }

        # 1. Update the user data in the users table
        $user->fill ([
            'user_status' => 'banned',
        ]);

        $user->save();

        # 2. redirect to the index
        return redirect()->route('admin.users.index')
                        ->with('status', 'Successfully banned.');
    }
}
