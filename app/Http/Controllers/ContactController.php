<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Models\Reservation;
use App\Mail\ContactSubmitted;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Symfony\Component\Uid\Ulid;

class ContactController extends Controller
{

    public function index(Request $request)
    {
        $contactStatusList = ['open', 'closed'];
        $sortList = ['dateFutureToPast', 'datePastToFuture'];

        $request->validate([
            'contactStatus' => ['nullable', Rule::in(array_merge(['all'], $contactStatusList))],
            'sort'          => ['nullable', Rule::in($sortList)],
            'rowsPerPage'   => ['nullable', 'integer', 'in:20, 50, 100']
        ]);

        $query = Contact::query()
                            ->where('user_id', Auth::id());

        // Filter by contact_status
        $contactStatus = $request->input('contactStatus', 'all');
        if($contactStatus !== 'all') {
            $query->where('contact_status', $contactStatus);
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
}
