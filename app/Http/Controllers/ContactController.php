<?php

namespace App\Http\Controllers;

use App\Mail\NewContactMessage;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('public.contact-us');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
            // Honeypot: real users never fill this in.
            'website' => ['prohibited'],
        ]);

        $contact = ContactMessage::create($data);

        try {
            Mail::to(config('contact.inbox'))->send(new NewContactMessage($contact));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()
            ->with('success', 'Thank you! Your message has reached our team and we will get back to you shortly.')
            ->withFragment('contact-form');
    }
}
