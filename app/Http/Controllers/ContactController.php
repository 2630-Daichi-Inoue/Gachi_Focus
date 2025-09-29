<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Mail\ContactSubmitted;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function create()
    {
        return view('contact.create');
    }

    public function store(ContactRequest $request)
    {
        // save
        $contact = Contact::create($request->validated());

        // notify
        Mail::to(config('mail.from.address'))
            ->send(new ContactSubmitted($contact));

        // success
        return back()->with('success', 'Thanks! Your message has been sent.');
    }
}
