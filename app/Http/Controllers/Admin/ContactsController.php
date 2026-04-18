<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Notification;

class ContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'user_name'      => ['nullable', 'string', 'max:50'],
            'contact_status' => ['nullable', 'in:all,open,closed,canceled'],
            'keyword'        => ['nullable', 'string', 'max:50'],
            'read_status'    => ['nullable', 'in:all,1,0'],
            'rows_per_page'  => ['nullable', 'integer', 'in:20,50,100'],
        ]);

        $query = Contact::query()
                        ->with('user');

        // Filter by username
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->user_name . '%');
            });
        }
        // Filter by contact status
        if ($request->filled('contact_status') && $request->contact_status !== 'all') {
            $query->where('contact_status', $request->contact_status);
        }
        // Filter by keyword in title or message
        if ($request->filled('keyword')) {
            $query->where('title', 'LIKE', '%' . $request->keyword . '%')
                  ->orWhere('message', 'LIKE', '%' . $request->keyword . '%');
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

        $contacts = $query
                    ->latest()
                    ->paginate($rowsPerPage);

        return view('admin.contacts.index', compact('contacts', 'rowsPerPage'));
    }

    public function read(Contact $contact)
    {

        if ($contact->read_at !== null) {
            return redirect()->route('admin.contacts.index')
                            ->with('error', 'This contact has already been marked as read.');
        }

        if ($contact->contact_status !== 'open') {
            return redirect()->route('admin.contacts.index')
                            ->with('error', 'This contact is not open anymore.');
        }

        # 1. Update the contact data in the contacts table
        $contact->fill ([
            'read_at' => now(),
        ]);

        $contact->save();

        # 2. redirect to the index
        return redirect()->route('admin.contacts.index')
                        ->with('status', 'Successfully marked as read.');
    }

    public function close(Request $request, Contact $contact)
    {
        if ($contact->read_at === null) {
            return redirect()->route('admin.contacts.index')
                            ->with('error', 'This contact has not been marked as read yet.');
        }

        if ($contact->contact_status !== 'open') {
            return redirect()->route('admin.contacts.index')
                            ->with('error', 'This contact is not open anymore.');
        }

        $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        # 1. Update the contact data in the contacts table
        $contact->update ([
            'contact_status' => 'closed',
        ]);

        # 2. Create a notification
        Notification::create([
            'user_id' => $contact->user_id,
            'title' => 'Your contact has been closed.',
            'message' => $request->input('message') ?: 'Your contact has already been closed. If you have any further questions or concerns, please feel free to reach out to us again.',
            'related_type' => 'contact',
            'related_id' => $contact->id,
        ]);

        # 3. redirect to the index
        return redirect()->route('admin.contacts.index')
                        ->with('status', 'Successfully marked as closed.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Nothing goes here since contacts are created by users, not admins.
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Nothing goes here since contacts are created by users, not admins.
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Nothing goes here since we have a separate "read" method for marking as read and showing details in a modal.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Nothing goes here since we have a separate "close" method for marking as closed and we don't have any other editable fields for contacts.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Nothing goes here since we have separate methods for marking as read and closed, and we don't have any other editable fields for contacts.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Nothing goes here since we don't want admins to delete contacts, as they may contain important information regarding user issues and inquiries.
    }
}
