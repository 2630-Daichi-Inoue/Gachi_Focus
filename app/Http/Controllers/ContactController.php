<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Mail\ContactSubmitted;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Show contact form.
     */
    public function create()
    {
        return view('contact.create'); // keep this in sync with your blade path
    }

    /**
     * Handle submit and redirect to Home with a flash message.
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        // 1) Validate & persist
        $contact = Contact::create($request->validated());

        // 2) Try to send mail, but don't block redirect if it fails
        try {
            // Send notification to site admin (or any receiver you set)
            Mail::to(config('mail.from.address'))
                ->send(new ContactSubmitted($contact));
        } catch (\Throwable $e) {
            // Log the error and continue; user experience should still be smooth
            Log::error('Contact mail failed: '.$e->getMessage());
        }

        // 3) Redirect to Home with a flash (key must match the blade: session('status'))
        return redirect()
            ->route('index') // <- Home is named "index" in your routes
            ->with('status', 'Your message has been sent successfully.');
            // Keep the key "status" because your blade/layout reads session('status')
    }
}
