<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ContactController extends Controller
{

    public function index(Request $request)
    {
        $contactStatusList = ['open', 'closed', 'canceled'];
        $sortList = ['datePresentToPast', 'datePastToPresent'];

        $request->validate([
            'contactStatus' => ['nullable', Rule::in(array_merge(['all'], $contactStatusList))],
            'sort'          => ['nullable', Rule::in($sortList)],
            'rowsPerPage'   => ['nullable', 'integer', 'in:20,50,100']
        ]);

        $query = Contact::query()
                            ->where('user_id', Auth::id());

        // Filter by contact_status
        if($request->input('contactStatus', 'all')!== 'all') {
            $query->where('contact_status', $request->input('contactStatus'));
        }

        $rowsPerPage = (int)$request->input('rowsPerPage', 20);

        // Default: date present → past
        $this->applySort($query, $request->input('sort', 'datePresentToPast'));

        $contacts = $query
                        ->paginate($rowsPerPage)
                        ->withQueryString();

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'filters' => [
                'contactStatus' => $request->input('contactStatus', 'all'),
                'sort' => $request->input('sort', 'datePresentToPast'),
                'rowsPerPage' => $rowsPerPage,
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

    /**
     * Show contact form.
     */
    public function create(Request $request)
    {
        $reservationId = $request->input('reservation_id');

        if ($reservationId) {
            $reservation = Reservation::findOrFail($reservationId);

            if ($reservation->user_id !== Auth::id()) {
                abort(403, 'You cannot contact us about a reservation that does not belong to you.');
            }

            return Inertia::render('Contacts/Create', [
                'reservation' => $reservation,
            ]);
        } else {
            return Inertia::render('Contacts/Create', [
                'reservation' => null,
            ]);
        }
    }

    /**
     * Handle submit and redirect to Home with a flash message.
     */
    public function store(StoreContactRequest $request)
    {
        $data = $request->validated();

        Contact::create([
            'user_id'        => Auth::id(),
            'reservation_id' => $data['reservation_id'] ?? null,
            'title'          => $data['title'],
            'message'        => $data['message'],
            'contact_status' => 'open',
        ]);

        if ($data['reservation_id'] !== null) {
            return redirect()->route('reservations.index')
                            ->with('ok', 'Your contact has been submitted. We will get back to you as soon as possible!');
        } else {
            return redirect()->route('contacts.index')
                            ->with('ok', 'Your contact has been submitted. We will get back to you as soon as possible!');
        }
    }

    public function cancel(Contact $contact)
    {
        if ($contact->user_id !== Auth::id()) {
            abort(403, 'You cannot cancel a contact that does not belong to you.');
        }

        if ($contact->contact_status === 'closed') {
            return back()->with('error', 'This contact has already been closed.');
        }

        if ($contact->contact_status === 'canceled') {
            return back()->with('error', 'This contact has already been canceled.');
        }

        if ($contact->read_at !== null) {
            return back()->with('error', 'This contact has already been read. Please wait for our response.');
        }

        $contact->update([
            'contact_status' => 'canceled',
            'canceled_at' => now(),
        ]);

        return back()->with('ok', 'Your contact has been canceled.');

    }
}
