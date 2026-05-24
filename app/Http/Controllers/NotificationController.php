<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Traits\AppliesChronologicalSort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    use AppliesChronologicalSort;

    public function index(Request $request)
    {
        $data = $request->merge([
            'new_only' => $request->boolean('new_only'),
        ]);
        $data->validate([
            'keyword' => ['nullable', 'string', 'max:50'],
            'new_only' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:datePresentToPast,datePastToPresent'],
            'rows_per_page' => ['nullable', 'integer', 'in:20,50,100'],
        ]);

        $query = Notification::query()
            ->where('user_id', Auth::id());

        // Filter by keyword
        if ($data['keyword']) {
            $query->where(function ($q) use ($data) {
                $q->where('title', 'like', '%'.$data['keyword'].'%')
                    ->orWhere('message', 'like', '%'.$data['keyword'].'%');
            });
        }

        // Filter by read/unread status
        if ($data['new_only']) {
            $query->whereNull('read_at');
        }

        $rowsPerPage = (int) ($data['rows_per_page'] ?? 20);

        // Default: date present → past
        $this->applySort($query, $data['sort'] ?? 'datePresentToPast');

        $notifications = $query
            ->paginate($rowsPerPage)
            ->withQueryString();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'filters' => [
                'keyword' => $data['keyword'] ?? '',
                'new_only' => $data['new_only'] ?? false,
                'sort' => $data['sort'] ?? 'datePresentToPast',
                'rows_per_page' => $rowsPerPage,
            ],
        ]);
    }

    private function applySort(Builder $q, ?string $sort): void
    {
        $this->applyChronologicalSort($q, $sort, 'created_at', 'datePastToPresent');
    }

    public function read(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to read this notification.');
        }

        $notification->update([
            'read_at' => now(),
        ]);

        return back()->with('ok', 'The notification has been marked as read.');
    }
}
