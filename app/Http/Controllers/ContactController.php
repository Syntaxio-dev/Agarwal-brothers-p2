<?php

namespace App\Http\Controllers;

use App\Mail\NewContactMessage;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Support\AutoReply;
use App\Support\FormRules;

class ContactController extends Controller
{
    public function show()
    {
        return view('public.contact-us');
    }

    public function store(Request $request): RedirectResponse
    {
        FormRules::prepare($request);

        $validator = Validator::make($request->all(), [
            'name' => FormRules::name(),
            'email' => ['required', 'email', 'max:255'],
            'phone' => FormRules::phone(),
            'phone_country' => FormRules::phoneCountry(),
            'company' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
            // Honeypot: real users never fill this in.
            'website' => ['prohibited'],
        ], FormRules::messages());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->withFragment('contact-form');
        }

        $data = FormRules::finish($validator->validated());

        $contact = ContactMessage::create($data);

        try {
            Mail::to(config('contact.inbox'))->queue(new NewContactMessage($contact));
        } catch (\Throwable $e) {
            report($e);
        }

        AutoReply::send('auto_contact', $contact);

        return back()
            ->with('success', 'Thank you! Your message has reached our team and we will get back to you shortly.')
            ->withFragment('contact-form');
    }
}
