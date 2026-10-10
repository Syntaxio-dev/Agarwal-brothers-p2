@php
    $c = config('contact');
    $input = 'h-11 w-full rounded-full border border-gray-200 bg-white px-5 text-sm text-navy placeholder:text-slate outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition';
    $card = 'w-full sm:w-[calc(50%-0.625rem)] lg:w-[calc(33.333%-0.84rem)] rounded-2xl bg-white border border-gray-100 shadow-sm p-6 text-center hover:shadow-lg hover:border-cyan/40 hover:-translate-y-1 transition-all duration-300';
    $icon = 'mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-navy text-white shadow-md';
@endphp

<x-layouts.app title="Contact Us" description="Contact Agarwal Brothers in Jaipur and Jodhpur for laboratory equipment quotes, service and technical support. Send an enquiry and our team will respond.">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[620px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.20),transparent_70%),radial-gradient(700px_380px_at_8%_12%,rgba(0,119,182,0.10),transparent_70%),radial-gradient(700px_380px_at_95%_18%,rgba(0,180,216,0.12),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14">

        <div class="text-sm text-slate mb-8">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">Contact Us</span>
        </div>

        {{-- ===== Header ===== --}}
        <div class="text-center flex flex-col items-center gap-3 mb-10">
            <span class="section-badge">
                Contact Us
            </span>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-navy leading-tight">
                Get in <span class="text-cyan-ink">Touch</span>
            </h1>
            <p class="max-w-2xl text-sm sm:text-base text-slate leading-relaxed">
                Have questions about our products or need a custom solution? We would love to hear from you.
            </p>
        </div>

        {{-- ===== Map ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">

            <div class="order-2 lg:order-1 text-center lg:text-left">
                <h2 class="text-2xl sm:text-3xl font-bold text-cyan-ink leading-tight">Headquartered in {{ $c['head_office']['city'] }}</h2>
                <p class="mt-3 text-base sm:text-lg text-navy leading-relaxed">
                    Wherever you are in Rajasthan, we are nearby, ready to support and serve your scientific journey.
                </p>
            </div>

            <div class="order-1 lg:order-2 relative mx-auto w-full max-w-[440px] aspect-[1000/908]">
                @include('public.partials.rajasthan-map')

                @php
                    $pins = [
                        ['city' => $c['head_office']['city'], 'tag' => $c['head_office']['label'], 'x' => 71.72, 'y' => 46.04, 'main' => true],
                        ['city' => 'Jodhpur', 'tag' => 'Branch Office', 'x' => 40.28, 'y' => 55.48, 'main' => false],
                    ];
                @endphp

                @foreach ($pins as $pin)
                    <div class="absolute" style="left: {{ $pin['x'] }}%; top: {{ $pin['y'] }}%;">
                        @if ($pin['main'])
                            <span class="absolute -left-4 -top-4 h-8 w-8 rounded-full bg-cyan/40 animate-ping"></span>
                        @endif
                        <svg class="absolute -translate-x-1/2 -translate-y-full h-9 w-9 drop-shadow-lg {{ $pin['main'] ? 'text-cyan-ink' : 'text-navy' }}"
                             viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                        </svg>
                        <div class="absolute left-0 top-1 -translate-x-1/2 whitespace-nowrap rounded-lg bg-white border border-gray-200 shadow-md px-3 py-1.5 text-center">
                            <p class="text-xs font-bold text-navy leading-none">{{ $pin['city'] }}</p>
                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide {{ $pin['main'] ? 'text-cyan-ink' : 'text-slate' }} leading-none">{{ $pin['tag'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="order-3 text-center lg:text-right">
                <h2 class="text-2xl sm:text-3xl font-bold text-cyan-ink leading-tight">Closer Than You Think</h2>
                <p class="mt-3 text-base sm:text-lg text-navy leading-relaxed">
                    Tap into our local teams in {{ $c['head_office']['city'] }} and Jodhpur for expert consultation and service tailored to your region.
                </p>
            </div>
        </div>

        {{-- ===== Contact cards (5) ===== --}}
        <div class="mt-20">
            <div class="text-center flex flex-col items-center gap-3 mb-8">
                <span class="section-badge">
                    Contact Us
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    For Support &amp; <span class="text-cyan-ink">Enquiries</span>
                </h2>
                <p class="max-w-2xl text-sm text-slate">
                    Reach out to the respective team, or visit us at one of our offices.
                </p>
            </div>

            <div class="flex flex-wrap justify-center gap-5">

                {{-- Departments --}}
                @foreach ($c['departments'] as $dept)
                    <div class="{{ $card }}">
                        <div class="{{ $icon }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $dept['icon'] }}"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-navy">{{ $dept['title'] }}</h3>
                        <a href="mailto:{{ $dept['email'] }}" class="mt-3 flex items-center justify-center gap-2 text-sm font-semibold text-navy hover:text-link transition">
                            <svg class="h-4 w-4 text-cyan-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.4 5.25a1.5 1.5 0 0 1-1.6 0L2.25 6.75"/></svg>
                            {{ $dept['email'] }}
                        </a>
                        <a href="tel:{{ preg_replace('/\s+/', '', $dept['phone']) }}" class="mt-1.5 flex items-center justify-center gap-2 text-sm font-semibold text-navy hover:text-link transition">
                            <svg class="h-4 w-4 text-cyan-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            {{ $dept['phone'] }}
                        </a>
                    </div>
                @endforeach

                {{-- Head office --}}
                <div class="{{ $card }}">
                    <div class="{{ $icon }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-bold text-navy">{{ $c['head_office']['label'] }} - {{ $c['head_office']['city'] }}</h3>
                    <p class="mt-2 text-sm text-slate leading-relaxed">{{ $c['head_office']['address'] }}</p>
                </div>

                {{-- Branch --}}
                @foreach ($c['branches'] as $branch)
                    <div class="{{ $card }}">
                        <div class="{{ $icon }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-navy">{{ $branch['label'] }} - {{ $branch['city'] }}</h3>
                        <p class="mt-2 text-sm text-slate leading-relaxed">{{ $branch['address'] }}</p>
                    </div>
                @endforeach

                {{-- Reach us --}}
                <div class="{{ $card }}">
                    <div class="{{ $icon }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-bold text-navy">Reach Us</h3>
                    <a href="mailto:{{ $c['mail_24x7'] }}" class="mt-2 block text-sm font-semibold text-navy hover:text-link transition">{{ $c['mail_24x7'] }}</a>
                    <a href="tel:{{ preg_replace('/\s+/', '', $c['call']) }}" class="mt-1 block text-sm font-semibold text-navy hover:text-link transition">{{ $c['call'] }}</a>
                    <div class="mt-3 space-y-0.5 text-xs text-slate leading-relaxed">
                        @foreach ($c['working_days'] as $line)
                            <p>{{ $line }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Quick response form ===== --}}
        <div id="contact-form" class="mt-20 scroll-mt-6 max-w-4xl mx-auto">
            <div class="rounded-3xl bg-gradient-to-br from-ice to-cyan/10 border border-cyan/20 p-3 sm:p-4">
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 sm:p-9">

                    <div class="text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-link">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"/></svg>
                            Need a quick response?
                        </span>
                        <h2 class="mt-3 text-xl sm:text-2xl font-bold text-navy">Contact Our <span class="text-cyan-ink">Team</span></h2>
                        <p class="mt-1 text-sm text-slate">Fill out the form and our team will get back to you shortly. Fields marked * are required.</p>
                    </div>

                    @if (session('success'))
                        <div class="mt-6"><x-form.alert>{{ session('success') }}</x-form.alert></div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-6"><x-form.alert type="error">Please fix the highlighted fields and try again.</x-form.alert></div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST" class="mt-6" x-data="formGuard(@js(collect($errors->messages())->map(fn ($m) => $m[0])->all()))" @submit="submit($event)" novalidate>
                        @csrf

                        <div class="hidden" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <input name="name" type="text" value="{{ old('name') }}" placeholder="Your Name *" class="{{ $input }}" required data-rule="required name" data-clean="name" data-label="Name" maxlength="80" autocomplete="name" @input="clean($el)" @blur="check($el)">
                                <x-form.error name="name" class="pl-4" />
                            </div>
                            <div>
                                <input name="email" type="email" value="{{ old('email') }}" placeholder="Your Email *" class="{{ $input }}" required data-rule="required email" data-label="Email" maxlength="255" autocomplete="email" @input="clean($el)" @blur="check($el)">
                                <x-form.error name="email" class="pl-4" />
                            </div>
                            <div>
                                <x-form.phone :required="true" :input="$input" rounded="rounded-full" />
                            </div>
                            <div>
                                <input name="company" type="text" value="{{ old('company') }}" placeholder="Company / Organisation" class="{{ $input }}">
                            </div>
                            <div>
                                <input name="city" type="text" value="{{ old('city') }}" placeholder="City" class="{{ $input }}">
                            </div>
                            <div>
                                <input name="subject" type="text" value="{{ old('subject') }}" placeholder="Application / Product" class="{{ $input }}">
                            </div>
                            <div class="md:col-span-2">
                                <textarea name="message" rows="4" placeholder="Please elaborate your requirement *" required data-rule="required" data-label="Message" @input="clean($el)" @blur="check($el)"
                                    class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm text-navy placeholder:text-slate outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition">{{ old('message') }}</textarea>
                                <x-form.error name="message" class="pl-4" />
                            </div>
                        </div>

                        <div class="mt-6 text-center">
                            <x-form.submit label="Submit" />
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
  </div>
</x-layouts.app>
