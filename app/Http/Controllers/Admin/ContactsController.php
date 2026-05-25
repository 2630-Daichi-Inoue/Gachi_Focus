<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Notification;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'id' => ['nullable', 'string'],
            'user_name' => ['nullable', 'string', 'max:50'],
            'contact_status' => ['nullable', 'in:all,open,closed,canceled'],
            'keyword' => ['nullable', 'string', 'max:50'],
            'read_status' => ['nullable', 'in:all,1,0'],
            'rows_per_page' => ['nullable', 'integer', 'in:20,50,100'],
        ]);

        $query = Contact::query()
            ->with('user');

        // Filter by ID (e.g. from notification bell)
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        // Filter by username
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%'.$request->user_name.'%');
            });
        }
        // Filter by contact status
        if ($request->filled('contact_status') && $request->contact_status !== 'all') {
            $query->where('contact_status', $request->contact_status);
        }
        // Filter by keyword in title or message
        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%'.$request->keyword.'%')
                    ->orWhere('message', 'LIKE', '%'.$request->keyword.'%');
            });
        }
        // Filter by read status
        if ($request->filled('read_status') && $request->read_status !== 'all') {
            if ($request->read_status === '1') {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $rowsPerPage = (int) $request->input('rows_per_page', 20);

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

        $contact->update(['read_at' => now()]);

        return redirect()->route('admin.contacts.index')
            ->with('ok', 'Successfully marked as read.');
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

        $contact->update(['contact_status' => 'closed']);

        // Create a notification
        Notification::create([
            'user_id' => $contact->user_id,
            'title' => 'Your contact has been closed.',
            'message' => $request->input('message') ?: 'Your contact has already been closed. If you have any further questions or concerns, please feel free to reach out to us again.',
            'related_type' => 'contact',
            'related_id' => $contact->id,
        ]);

        // 3. redirect to the index
        return redirect()->route('admin.contacts.index')
            ->with('ok', 'Successfully marked as closed.');
    }

}

