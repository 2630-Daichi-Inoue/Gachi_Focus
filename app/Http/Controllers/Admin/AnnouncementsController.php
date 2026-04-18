<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use Carbon\Carbon;

class AnnouncementsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'keyword'        => ['nullable', 'string', 'max:50'],
            'published_date' => ['nullable', 'date'],
            'is_public'      => ['nullable', 'in:all,1,0'],
        ]);

        $query = Announcement::query();

        // Filter by keyword
        if ($request->filled('keyword')) {
            $query->where('title', 'like', "%{$request->keyword}%")
                  ->orWhere('message', 'like', "%{$request->keyword}%");
        }
        // Filter by is_public status
        if ($request->filled('is_public') && $request->is_public !== 'all') {
            $query->where('is_public', $request->boolean('is_public'));
        }
        // Filter by publishing date
        if ($from = $request->input('published_date')) {
            $query->where('published_at', '>=', "{$from} 00:00:00");
        }

        $rowsPerPage = (int)$request->input('rows_per_page', 20);

        $announcements = $query
                    ->latest()
                    ->paginate($rowsPerPage);

        return view('admin.announcements.index', compact('announcements', 'rowsPerPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.announcements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnnouncementRequest $request)
    {
        # 1. Validate all form data
        $data = $request->validated();

        $publishedAt = null;
        if (!empty($data['published_date']) && !empty($data['published_time'])) {
            $publishedAt = Carbon::parse("{$data['published_date']} {$data['published_time']}");
        }

        $expiredAt = null;
        if (!empty($data['expired_date']) && !empty($data['expired_time'])) {
            $expiredAt = Carbon::parse("{$data['expired_date']} {$data['expired_time']}");
        }

        if ($publishedAt && $publishedAt->lt(now())) {
            return back()->withErrors([
                'published_date' => 'Published date and time must be in the future.'
            ])->withInput();
        }

        if ($expiredAt && $publishedAt && $expiredAt->lte($publishedAt)) {
            return back()->withErrors([
                'expired_date' => 'Expired date and time must be after the published date and time.'
            ])->withInput();
        }

        # 2. Save announcement data to announcements table
        Announcement::create([
            'title'         => $data['title'],
            'message'       => $data['message'],
            'published_at'  => $publishedAt,
            'expired_at'    => $expiredAt,
            'is_public'     => $data['is_public'] ?? true,
        ]);

        # 3. Redirect back to the announcement list with a success message
        return redirect()->route('admin.announcements.index')
                            ->with('ok', 'Successfully created.');
    }

    public function hide(Announcement $announcement)
    {
        if ($announcement->is_public === false) {
            return redirect()->route('admin.announcements.index')
                ->with('error', 'The announcement has already been hidden.');
        }

        # 1. Update the announcement data in the announcements table
        $announcement->update ([
            'is_public' => false,
        ]);

        # 2. redirect to the index
        return redirect()->route('admin.announcements.index')
                        ->with('status', 'Successfully hidden.');
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        // Nothing goes here since we have a modal to show announcement details in the list page.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        // Nothing goes here since we create a new announcement and hide an old one if needed.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        // Nothing goes here since we create a new announcement and hide an old one if needed.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        // Nothing goes here since we only hide old announcements instead of deleting them.
    }
}
