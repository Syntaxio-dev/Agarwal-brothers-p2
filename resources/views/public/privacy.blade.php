@php
    $company = 'Agarwal Brothers';
    $email = config('privacy.contact_email');
    $address = config('contact.head_office.address');
    $analytics = filled(config('services.analytics_id'));
    $retention = config('privacy.retention');
    $sessionMinutes = (int) config('session.lifetime');

    // What this site stores on the visitor's device
    $storage = [
        ['Session cookie', 'Cookie', 'Essential', 'Keeps you signed in to forms and protects them from misuse (security token).', $sessionMinutes . ' minutes of inactivity'],
        ['Security token (XSRF-TOKEN)', 'Cookie', 'Essential', 'Protects forms from forged requests.', $sessionMinutes . ' minutes of inactivity'],
        ['ab_compare', 'Saved data', 'Feature you asked for', 'Your compare list.', '7 days since last change'],
        ['ab_enquiry_list', 'Saved data', 'Feature you asked for', 'Your enquiry list (products and quantities).', '30 days since last change'],
        ['ab_recent', 'Saved data', 'Feature you asked for', 'Products you recently viewed.', '30 days since last change'],
        ['ab_consent', 'Saved data', 'Essential', 'Remembers your cookie choice.', '180 days'],
    ];
    if ($analytics) {
        $storage[] = ['_ga, _ga_*', 'Cookie', 'Analytics (only if you accept)', 'Google Analytics: counts visits and shows which pages are useful. Your IP address is anonymised.', 'Up to 2 years'];
    }

    $crumbs = [['Home', '/'], ['Privacy policy', null]];
    $h2 = 'mt-10 text-xl font-bold text-navy sm:text-2xl';
@endphp

<x-layouts.app title="Privacy policy" description="How Agarwal Brothers collects, uses and protects your information, and how to delete the data saved on your device.">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[420px]
                bg-[radial-gradient(900px_380px_at_50%_-8%,rgba(0,180,216,0.14),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative mx-auto w-[98%] max-w-4xl px-4 py-10 sm:py-14 md:px-10">
        @include('public.partials.breadcrumb', ['items' => $crumbs])

        <span class="section-badge">Privacy</span>
        <h1 class="mt-3 text-3xl font-bold leading-tight text-navy sm:text-4xl">Privacy <span class="text-cyan-ink">policy</span></h1>
        <p class="mt-3 text-sm text-slate">Last updated {{ config('privacy.updated') }}</p>

        <div class="rich-text mt-6">
            <p>
                {{ $company }} (&ldquo;we&rdquo;) supplies laboratory equipment and chemicals from {{ $address }}.
                This page explains what information this website collects, why, how long it is kept, and how you can control it.
            </p>

            <h2 class="{{ $h2 }}">Information we collect</h2>
            <ul>
                <li><strong>Enquiries and messages.</strong> When you use the enquiry form, enquiry list or contact form we receive your name, email, phone number, company, city, the products you ask about and your message.</li>
                <li><strong>Job applications.</strong> Name, email, phone, resume, your answers to the role questions and any message. Resumes are stored privately and are visible only to our HR team.</li>
                <li><strong>Technical data.</strong> Like any website, our server records your IP address, browser and the pages requested, for security and to keep the site running.</li>
                @if ($analytics)
                    <li><strong>Analytics.</strong> Only if you choose &ldquo;Accept all&rdquo;, we use Google Analytics to count visits and see which pages are useful. Choosing &ldquo;Essential only&rdquo; turns it off.</li>
                @else
                    <li><strong>Analytics.</strong> We do not currently use analytics or advertising cookies.</li>
                @endif
            </ul>

            <h2 class="{{ $h2 }}">How we use it</h2>
            <p>We use your details to reply to your enquiry or application, prepare quotations, provide support and keep our records. We do not sell your information and do not use it for advertising profiles.</p>

            <h2 class="{{ $h2 }}">Who can see it</h2>
            <p>Our own sales, service and HR staff. We also use service providers who help us run the site (web hosting and email delivery). They process data only on our behalf. We share information with authorities only when the law requires it.</p>
        </div>

        {{-- Cookies and saved data --}}
        <h2 class="{{ $h2 }}">Cookies and data saved on your device</h2>
        <p class="mt-2 text-sm leading-relaxed text-slate">
            The items below stay on your own device. They are not sent to us except the two cookies needed to run the site, and they remove themselves after the time shown.
        </p>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full min-w-[640px] border-collapse text-left text-sm">
                <thead>
                    <tr class="bg-ice">
                        @foreach (['Name', 'Type', 'Purpose', 'Kept for'] as $head)
                            <th class="border-b border-gray-200 px-4 py-3 font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-link">{{ $head }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($storage as [$name, $type, $category, $purpose, $lasts])
                        <tr class="border-b border-gray-100 align-top last:border-0">
                            <td class="px-4 py-3 font-mono text-xs font-medium text-navy">{{ $name }}</td>
                            <td class="px-4 py-3 text-slate">{{ $type }}<br><span class="text-xs">{{ $category }}</span></td>
                            <td class="px-4 py-3 text-slate">{{ $purpose }}</td>
                            <td class="px-4 py-3 text-slate">{{ $lasts }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Your choices --}}
        <h2 class="{{ $h2 }}">Your choices</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2" x-data="{ cleared: false }">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-link">Saved on this device</p>
                <p class="mt-2 text-sm leading-relaxed text-slate">Delete your compare list, enquiry list, recently viewed products and cookie choice from this browser.</p>
                <button type="button" @click="abClearSavedData(); cleared = true" class="btn-ghost mt-4">Clear saved data</button>
                <p x-cloak x-show="cleared" role="status" class="mt-3 text-sm font-medium text-success">Done. Everything this site saved on your device has been removed.</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-link">Cookie settings</p>
                <p class="mt-2 text-sm leading-relaxed text-slate">
                    @if ($analytics)
                        Change whether analytics cookies are allowed.
                    @else
                        Review the cookie notice again at any time.
                    @endif
                </p>
                <button type="button" x-data @click="$store.consent.reopen()" class="btn-ghost mt-4">Open cookie settings</button>
            </div>
        </div>

        <div class="rich-text mt-6">
            <h2 class="{{ $h2 }}">How long we keep your information</h2>
            <ul>
                <li>Enquiries and contact messages: up to {{ $retention['enquiries'] }} months after we last hear from you, unless a longer period is needed for an order or a legal requirement.</li>
                <li>Job applications: up to {{ $retention['applications'] }} months. Tell us if you would like it removed sooner.</li>
                <li>Server and security logs: a few weeks, then they are deleted automatically.</li>
            </ul>

            <h2 class="{{ $h2 }}">Your rights</h2>
            <p>
                You can ask us to show you the information we hold about you, correct it, or delete it.
                Write to <a href="mailto:{{ $email }}">{{ $email }}</a> from the email address you used with us and we will reply within a reasonable time, normally within 30 days.
            </p>

            <h2 class="{{ $h2 }}">Security</h2>
            <p>Forms are protected against misuse, uploaded resumes are stored privately, and access to our admin system is limited to authorised staff. No website can promise absolute security, so please do not send passwords or payment details through the forms.</p>

            <h2 class="{{ $h2 }}">Changes to this policy</h2>
            <p>If we change this policy we will update the date at the top of this page.</p>
        </div>

        <div class="mt-12 flex flex-col items-center justify-between gap-5 rounded-3xl bg-gradient-to-br from-navy to-link px-8 py-9 sm:flex-row sm:px-12">
            <div class="text-center sm:text-left">
                <h3 class="text-xl font-bold text-white sm:text-2xl">Questions about your data?</h3>
                <p class="mt-1.5 text-sm text-white/75">Our team will be glad to help.</p>
            </div>
            <a href="{{ route('contact') }}" class="btn-glass shrink-0 px-7 py-3">Contact us</a>
        </div>
    </div>
  </div>
</x-layouts.app>
