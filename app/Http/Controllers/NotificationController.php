<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $request->merge([
            'new_only'      => $request->boolean('new_only'),
        ]);
        $data->validate([
            'keyword'       => ['nullable', 'string', 'max:50'],
            'new_only'      => ['nullable', 'boolean'],
            'sort'          => ['nullable', 'in:datePresentToPast,datePastToPresent'],
            'rows_per_page' => ['nullable', 'integer', 'in:20,50,100']
        ]);

        $query = Notification::query()
                            ->where('user_id', Auth::id());

        // Filter by keyword
        if($data['keyword']) {
            $query->where('title', 'like', '%' . $data['keyword'] . '%')
                  ->orWhere('message', 'like', '%' . $data['keyword'] . '%');
        }

        // Filter by read/unread status
        if($data['new_only']) {
            $query->whereNull('read_at');
        }

        $rowsPerPage = (int)($data['rows_per_page'] ?? 20);

        // Default: date present → past
        $this->applySort($query, $data['sort'] ?? 'datePresentToPast');

        $notifications = $query
                        ->paginate($rowsPerPage)
                        ->withQueryString();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'filters' => [
                'keyword'      => $data['keyword'] ?? '',
                'new_only'     => $data['new_only'] ?? false,
                'sort'         => $data['sort'] ?? 'datePresentToPast',
                'rows_per_page' => $rowsPerPage,
            ]
        ]);
    }

    public function applySort(\Illuminate\Database\Eloquent\Builder $q, ?string $sort): void
    {
        switch ($sort ?? 'datePresentToPast') {
            case 'datePresentToPast':
                $q->orderBy('created_at', 'desc')
                    ->latest('id');
                break;

            case 'datePastToPresent':
                $q->orderBy('created_at', 'asc')
                    ->latest('id');
                break;

            default:
                $q->orderBy('created_at', 'desc')
                    ->latest('id');
        }
    }

    public function read(Notification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->update([
            'read_at' => now()
        ]);

        return back()->with('ok', 'The notification has been marked as read.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Nothing goes here since only admin can create notifications, and the form is handled in the admin panel.
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        // Nothing goes here since only admin can create notifications, and the form is handled in the admin panel.
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        // Nothing goes here since users view notification details in a modal on the index page, and there is no separate page for notification details.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        // Nothing goes here since only admin can edit notifications, and the form is handled in the admin panel.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        // Nothing goes here since only admin can update notifications, and the form is handled in the admin panel.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        // Nothing goes here since only admin can delete notifications, and the form is handled in the admin panel.
    }
}
