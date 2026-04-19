<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Space;
use App\Models\User;
use App\Models\Reservation;
use Carbon\Carbon;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {

        $request->validate([
            'keyword'        => ['nullable', 'string', 'max:50'],
            'type'           => ['nullable', 'string', 'in:all,user,space,contact'],
            'read_status'    => ['nullable', 'in:all,1,0'],
            'rows_per_page'  => ['nullable', 'integer', 'in:20,50,100']
        ]);

        $query = Notification::query()->with('user');

        // Filter by keyword
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('message', 'like', "%{$keyword}%");
            });
        }

        // Filter by type
        // dd($request->input('type'));
        if ($request->filled('type') && $request->input('type') !== 'all') {
            $type = $request->input('type');
            if ($type === 'user') {
                $query->where('related_type', 'user');
            } elseif ($type === 'space') {
                $query->where('related_type', 'space');
            } elseif ($type === 'contact') {
                $query->where('related_type', 'contact');
            }
        }

        // Filter by read status
        if ($request->filled('read_status') && $request->read_status !== 'all') {
            if ($request->read_status === '1') {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $rowsPerPage = (int)$request->input('rows_per_page', 20);

        $notifications = $query
                        ->orderBy('created_at', 'desc')
                        ->paginate($rowsPerPage);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function create(Space $space, User $user)
    {
        $message = null;

        if ($space->id !== null) {
            $today = today();

            $targetReservations = Reservation::where('space_id', $space->id)
                                                ->where('reservation_status', 'booked')
                                                ->where('started_at', '>=', Carbon::createFromFormat("Y-m-d H:i:s", "$today"))
                                                ->get();

            $targetUsers = $targetReservations->pluck('user_id')->unique()->values();

            if ($targetUsers->isEmpty()) {
                $message = 'No users have upcoming reservations for this space.';
            }

            return view('admin.notifications.spaces.create', compact('space', 'targetUsers', 'message'));

        } elseif ($user->id !== null) {

            if ($user->isDeleted()) {
                $message = 'The user has already been deleted.';
            }

            if ($user->isBanned()) {
                $message = 'The user cannot check out notifications because they are banned. Please unban the user to let them check out notifications if needed.';
            }

            return view('admin.notifications.users.create', compact('user', 'message'));
        } else {
            return redirect()->route('admin.dashboard')
                            ->with('status', 'Invalid notification type or target.');
        }
    }

    public function store(Request $request, Space $space, User $user)
    {
        if ($request->input('type') === 'user') {
            $request->validate([
                'title' => ['required', 'string', 'max:50'],
                'message' => ['required', 'string', 'max:1000'],
            ]);

            Notification::create([
                'user_id' => $user->id,
                'title' => $request->input('title'),
                'message' => $request->input('message'),
                'related_type' => 'user',
                'related_id' => $user->id,
            ]);
        }

        if ($request->input('type') === 'space') {
            $request->validate([
                'title' => ['required', 'string', 'max:50'],
                'message' => ['required', 'string', 'max:1000'],
            ]);

            $targetUsers = explode(',', $request->input('targetUsers'));

            foreach ($targetUsers as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'title' => $request->input('title'),
                    'message' => $request->input('message'),
                    'related_type' => 'space',
                    'related_id' => $space->id,
                ]);
            }
        }

        return redirect()->route('admin.dashboard')
                        ->with('status', 'A notification has been successfully sent.');
    }
}
