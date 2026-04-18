<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $request->validate([
            'keyword'       => ['nullable', 'string', 'max:50'],
            'sort'          => ['nullable', 'in:datePresentToPast,datePastToPresent'],
            'rowsPerPage'   => ['nullable', 'integer', 'in:20,50,100']
        ]);

        $query = Announcement::query()
                            ->where('published_at', '<=', now())
                            ->where('is_public', true);

        // Filter by keyword
        if($request->input('keyword')) {
            $query->where('title', 'like', '%' . $request->input('keyword') . '%')
                  ->orWhere('message', 'like', '%' . $request->input('keyword') . '%');
        }

        $rowsPerPage = (int)$request->input('rowsPerPage', 20);

        // Default: date present → past
        $this->applySort($query, $request->input('sort', 'datePresentToPast'));

        $announcements = $query
                        ->paginate($rowsPerPage)
                        ->withQueryString();

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements,
            'filters' => [
                'keyword'     => $request->input('keyword', ''),
                'sort'        => $request->input('sort', 'datePresentToPast'),
                'rowsPerPage' => $rowsPerPage,
            ]
        ]);
    }

    public function applySort(\Illuminate\Database\Eloquent\Builder $q, ?string $sort): void
    {
        switch ($sort ?? 'datePresentToPast') {
            case 'datePresentToPast':
                $q->orderBy('published_at', 'desc')
                    ->latest('id');
                break;

            case 'datePastToPresent':
                $q->orderBy('published_at', 'asc')
                    ->latest('id');
                break;

            default:
                $q->orderBy('published_at', 'desc')
                    ->latest('id');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Nothing goes here since only admin can create announcements, and the form is handled in the admin panel.
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        // Nothing goes here since only admin can create announcements, and the form is handled in the admin panel.
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        // Nothing goes here since users view announcement details in a modal on the index page, and there is no separate page for announcement details.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        // Nothing goes here since only admin can edit announcements, and the form is handled in the admin panel.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        // Nothing goes here since only admin can update announcements, and the form is handled in the admin panel.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        // Nothing goes here since only admin can delete announcements, and the form is handled in the admin panel.
    }
}
