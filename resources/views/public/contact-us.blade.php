@php
    $c = config('contact');
    $input = 'h-11 w-full rounded-full border border-gray-200 bg-white px-5 text-sm text-navy placeholder:text-slate outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition';
    $card = 'edge-top group relative overflow-hidden w-full min-w-0 sm:w-[calc(50%-0.625rem)] lg:w-[calc(33.333%-0.84rem)] lg:px-3 xl:px-6 rounded-2xl bg-white border border-gray-100 shadow-sm p-6 text-center hover:shadow-lg hover:border-cyan/40 hover:-translate-y-1 transition-all duration-300';
    $icon = 'pop-icon mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-navy text-white shadow-md transition-all duration-300 group-hover:bg-link group-hover:scale-110';

    // ?topic=quote opens the form with the subject already filled (only the topics listed in config/contact.php)
    $topic = request('topic');
    $prefill = is_string($topic) ? ($c['topics'][$topic] ?? null) : null;
    $catalogueList = optional(\App\Models\SiteSetting::latest()->first())?->catalogueList() ?? [];
    $enq = $c['departments'][1] ?? $c['departments'][0];
    $waNumber = preg_replace('/\D/', '', (string) (optional(\App\Models\SiteSetting::latest()->first())->whatsapp_number ?: $c['whatsapp']));

    $faqs = array_values(array_filter([
        ['How can I get a quotation?', 'Send us the form on this page with the product or application you need, or call ' . $enq['person'] . ' (' . $enq['title'] . ') on ' . $enq['phone'] . '. You can also add several products to your enquiry list on the site and send them together.'],
        ['Which brands do you work with?', 'We are an authorised partner of ' . \App\Support\Company::plus('brands') . ' global brands. You can browse them from the Verticals and Brands pages of this website.'],
        ['Where is your office and when are you open?', 'Our ' . strtolower($c['head_office']['label']) . ' is at ' . $c['head_office']['address'] . '. ' . implode('. ', $c['working_days']) . '.'],
        $catalogueList ? ['Do you have a product catalogue?', count($catalogueList) > 1
            ? 'Yes, we have ' . count($catalogueList) . ' catalogues (' . implode(', ', array_column($catalogueList, 'title')) . '). Use the Catalogues button in the top bar of any page, or the downloads on this page, to get each one as a PDF.'
            : 'Yes. Use the Catalogue button in the top bar of any page to download it as a PDF.'] : null,
        ['Can I ask about more than one product at the same time?', 'Yes. Add products to your enquiry list with the cart button on any product card, then send the whole list as a single enquiry.'],
        ['Can I apply for a job with you?', 'Yes. Open roles are on our Careers page, and you can also share your profile through the general application form there.'],
        ! empty($c['gstin']) ? ['What is your GST number?', 'Our GSTIN is ' . $c['gstin'] . '.'] : null,
    ]));

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($f) => [
            '@type' => 'Question',
            'name' => $f[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
        ], $faqs),
    ];
@endphp

<x-layouts.app title="Contact Us" description="Contact Agarwal Brothers in Jaipur for laboratory equipment quotes, service and technical support. Send an enquiry and our team will respond." :schema="$faqSchema">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[620px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.20),transparent_70%),radial-gradient(700px_380px_at_8%_12%,rgba(0,119,182,0.10),transparent_70%),radial-gradient(700px_380px_at_95%_18%,rgba(0,180,216,0.12),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14">

        @include('public.partials.breadcrumb', ['items' => [['Home', '/'], ['Contact Us', null]]])

        {{-- ===== Header ===== --}}
        <div class="text-center flex flex-col items-center gap-3 mb-10">
            <span data-reveal class="section-badge">
                Contact Us
            </span>
            <h1 data-reveal class="text-2xl sm:text-3xl lg:text-4xl font-bold text-navy leading-tight">
                Get in <span class="text-cyan-ink">Touch</span>
            </h1>
            <p data-reveal class="max-w-2xl text-sm sm:text-base text-slate leading-relaxed">
                Have questions about our products or need a custom solution? We would love to hear from you.
            </p>
            <div data-reveal class="mt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="#contact-form" class="btn-primary inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white">
                    Send us a message
                    <svg class="arrow-nudge-down h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/>
                    </svg>
                </a>
                <a href="tel:{{ preg_replace('/\s+/', '', $c['call']) }}" class="btn-ghost rounded-full px-6 py-3">
                    <svg class="h-4 w-4 text-cyan-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    {{ $c['call'] }}
                </a>
                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer" class="btn-ghost rounded-full px-6 py-3">
                    <svg class="h-4 w-4 text-cyan-ink" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    WhatsApp
                </a>
            </div>
        </div>

        {{-- ===== Trust strip ===== --}}
        @include('public.partials.trust-strip')

        {{-- ===== What can we help you with ===== --}}
        @php
            $tiles = array_values(array_filter([
                ['Product enquiry', 'Tell us which instrument or brand you need.', route('contact', ['topic' => 'product']) . '#contact-form', 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9', false],
                ['Request a quote', 'Get a quotation for the models you have shortlisted.', route('contact', ['topic' => 'quote']) . '#contact-form', 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z', false],
                ['Service & spare parts', 'Ask about service, repair or spare parts.', route('contact', ['topic' => 'service']) . '#contact-form', 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z', false],
                ...array_map(fn ($c) => [$c['title'], 'Download the catalogue as a PDF.', $c['url'], 'M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3', true], $catalogueList),
            ]));
        @endphp
        @php
            $tileRows = (int) ceil(count($tiles) / 2);
        @endphp
        <div class="relative mt-20 overflow-hidden rounded-3xl bg-navy px-6 py-10 sm:px-10 sm:py-12 lg:px-14">
            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full border-[28px] border-white/5"></div>
            <div class="relative grid grid-cols-1 items-start gap-9 lg:grid-cols-[0.8fr_1.2fr]">

                <div data-reveal="left">
                    <span class="section-badge !text-cyan">Quick links</span>
                    <h2 class="mt-3 text-2xl font-bold leading-tight text-white sm:text-3xl">
                        What can we <span class="text-cyan">help you with?</span>
                    </h2>
                    <p class="mt-3 max-w-sm text-sm leading-relaxed text-white/70">Pick what you need and we will take you straight to the right place.</p>
                </div>

                <div class="grid grid-cols-1 gap-x-6 sm:grid-cols-2">
                    @foreach ($tiles as [$tTitle, $tText, $tHref, $tIcon, $tDownload])
                        @php
                            $divider = $loop->last ? '' : ((intdiv($loop->index, 2) === $tileRows - 1) ? 'border-b border-white/10 sm:border-b-0' : 'border-b border-white/10');
                        @endphp
                        <a data-reveal href="{{ $tHref }}" @if ($tDownload) download @endif
                           class="row-link group flex items-center gap-3 rounded-lg px-3 py-4 transition-colors duration-300 hover:bg-white/[0.06] focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan {{ $divider }}">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[10px] border transition-colors duration-300 {{ $tDownload ? 'border-cyan bg-cyan text-navy' : 'border-cyan/40 bg-ice text-navy group-hover:border-cyan' }}" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tIcon }}"/>
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-white transition-colors duration-300 group-hover:text-cyan">{{ $tTitle }}@if ($tDownload) <span class="ml-1.5 rounded border border-white/30 px-1 py-px align-middle font-mono text-[9px] font-medium uppercase tracking-wider text-white/70">PDF</span>@endif</span>
                                <span class="mt-0.5 block text-xs leading-snug text-white/60">{{ $tText }}</span>
                            </span>
                            <svg class="arrow-nudge h-4 w-4 shrink-0 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== How we work with you ===== --}}
        <div class="mt-20">
            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-8">
                <span class="section-badge">How it works</span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    From enquiry to <span class="text-cyan-ink">delivery</span>
                </h2>
                <p class="max-w-2xl text-sm text-slate">Four simple steps once you reach out to us.</p>
            </div>

            @include('public.partials.steps-flow', ['steps' => $c['steps']])
        </div>

        {{-- ===== Map ===== --}}
        <div class="mt-20 rounded-3xl bg-ice px-6 py-10 sm:px-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">

            <div data-reveal="left" class="order-2 lg:order-1 text-center lg:text-left">
                <h2 class="text-2xl sm:text-3xl font-bold text-cyan-ink leading-tight">Headquartered in {{ $c['head_office']['city'] }}</h2>
                <p class="mt-3 text-base text-navy leading-relaxed">
                    Wherever you are in Rajasthan, we are nearby, ready to support and serve your scientific journey.
                </p>
                <div class="mt-5 text-sm">
                    <p class="font-semibold text-navy">{{ $c['head_office']['label'] }} - {{ $c['head_office']['city'] }}</p>
                    <p class="mt-1 leading-relaxed text-slate">{{ $c['head_office']['address'] }}</p>
                    @if (! empty($c['gstin']))
                        <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-slate">GSTIN: <span class="text-navy">{{ $c['gstin'] }}</span></p>
                    @endif
                </div>
            </div>

            <div data-reveal="zoom" class="order-1 lg:order-2 relative mx-auto w-full max-w-[440px] aspect-[1000/908]">
                @include('public.partials.rajasthan-map')

                @php
                    $pins = [
                        ['city' => $c['head_office']['city'], 'tag' => $c['head_office']['label'], 'x' => 71.72, 'y' => 46.04, 'main' => true],
                    ];
                    foreach ($c['branches'] as $branch) {
                        if (isset($branch['map']['x'], $branch['map']['y'])) {
                            $pins[] = ['city' => $branch['city'], 'tag' => $branch['label'], 'x' => $branch['map']['x'], 'y' => $branch['map']['y'], 'main' => false];
                        }
                    }
                    foreach ($c['map_pins'] ?? [] as $extra) {
                        $pins[] = ['city' => $extra['city'], 'tag' => $extra['tag'] ?? null, 'x' => $extra['x'], 'y' => $extra['y'], 'main' => false];
                    }
                    $officeCities = collect([$c['head_office']['city']])->merge(collect($c['branches'])->pluck('city'))->filter()->values();
                    $officeCitiesText = $officeCities->count() > 1 ? $officeCities->slice(0, -1)->implode(', ') . ' and ' . $officeCities->last() : $officeCities->first();
                @endphp

                @foreach ($pins as $pin)
                    <div class="map-pin absolute" style="left: {{ $pin['x'] }}%; top: {{ $pin['y'] }}%; --pin-delay: {{ $loop->index * 250 + 450 }}ms">
                        @if ($pin['main'])
                            <span class="absolute -left-4 -top-4 h-8 w-8 rounded-full bg-cyan/40 animate-ping"></span>
                        @endif
                        <svg class="absolute -translate-x-1/2 -translate-y-full h-9 w-9 drop-shadow-lg {{ $pin['main'] ? 'text-cyan-ink' : 'text-navy' }}"
                             viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                        </svg>
                        <div class="absolute left-0 top-1 -translate-x-1/2 whitespace-nowrap rounded-lg bg-white border border-gray-200 shadow-md px-3 py-1.5 text-center">
                            <p class="text-xs font-bold text-navy leading-none">{{ $pin['city'] }}</p>
                            @if (filled($pin['tag']))
                                <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide {{ $pin['main'] ? 'text-cyan-ink' : 'text-slate' }} leading-none">{{ $pin['tag'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div data-reveal="right" class="order-3 text-center lg:text-right">
                <h2 class="text-2xl sm:text-3xl font-bold text-cyan-ink leading-tight">Office timings</h2>
                <div class="mt-3 space-y-1 text-base leading-relaxed">
                    @foreach ($c['working_days'] as $line)
                        <p class="{{ $loop->first ? 'font-semibold text-navy' : 'text-slate' }}">{{ $line }}</p>
                    @endforeach
                </div>
                <p class="mt-5 text-sm leading-relaxed text-slate">Visit us during office hours, or call ahead so the right person is ready for you.</p>
            </div>
        </div>
        </div>

        {{-- ===== Contact cards (5) ===== --}}
        <div class="mt-20">
            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-8">
                <span class="section-badge">
                    Contact Us
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    For support &amp; <span class="text-cyan-ink">enquiries</span>
                </h2>
                <p class="max-w-2xl text-sm text-slate">
                    Call or write to our team directly. We will be glad to help.
                </p>
            </div>

            <div class="flex flex-wrap justify-center gap-5">

                {{-- Departments --}}
                @foreach ($c['departments'] as $dept)
                    <div data-reveal class="{{ $card }}">
                        <div class="{{ $icon }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $dept['icon'] }}"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-navy">{{ $dept['title'] }}</h3>
                        @if (! empty($dept['person']))
                            <p class="mt-1 text-sm text-slate">{{ $dept['person'] }}</p>
                        @endif
                        <a href="mailto:{{ $dept['email'] }}" class="mt-3 flex items-center justify-center gap-2 text-sm font-semibold text-navy transition hover:text-link lg:text-[13px] xl:text-sm">
                            <svg class="hidden h-4 w-4 text-cyan-ink xl:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.4 5.25a1.5 1.5 0 0 1-1.6 0L2.25 6.75"/></svg>
                            {{ $dept['email'] }}
                        </a>
                        <a href="tel:{{ preg_replace('/\s+/', '', $dept['phone']) }}" class="mt-1.5 flex items-center justify-center gap-2 text-sm font-semibold text-navy hover:text-link transition">
                            <svg class="h-4 w-4 text-cyan-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            {{ $dept['phone'] }}
                        </a>
                    </div>
                @endforeach

                {{-- Branch --}}
                @foreach ($c['branches'] as $branch)
                    <div data-reveal class="{{ $card }}">
                        <div class="{{ $icon }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-navy">{{ $branch['label'] }} - {{ $branch['city'] }}</h3>
                        <p class="mt-2 text-sm text-slate leading-relaxed">{{ $branch['address'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== FAQ ===== --}}
        <div class="mt-20 rounded-3xl bg-ice px-4 py-10 sm:px-10">
        <div class="mx-auto max-w-3xl" x-data="{ open: null }">
            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-8">
                <span class="section-badge">FAQ</span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Common <span class="text-cyan-ink">questions</span>
                </h2>
            </div>

            <div class="space-y-3">
                @foreach ($faqs as $i => [$q, $a])
                    <div data-reveal class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-colors duration-300 hover:border-cyan/40">
                        <h3>
                            <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }}" aria-controls="faq-{{ $i }}"
                                    class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-navy transition hover:text-link focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan sm:text-base">
                                {{ $q }}
                                <svg class="h-5 w-5 shrink-0 text-cyan-ink transition-transform duration-300" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                        </h3>
                        <div id="faq-{{ $i }}" x-cloak x-show="open === {{ $i }}" x-transition.opacity.duration.200ms>
                            <p class="px-5 pb-5 text-sm leading-relaxed text-slate">{{ $a }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        </div>

        {{-- ===== Quick response form ===== --}}
        <div id="contact-form" class="mt-20 scroll-mt-6 max-w-4xl mx-auto">
            <div data-reveal class="rounded-3xl bg-gradient-to-br from-ice to-cyan/10 border border-cyan/20 p-3 sm:p-4">
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 sm:p-9">

                    <div class="text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-link">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"/></svg>
                            Need a quick response?
                        </span>
                        <h2 class="mt-3 text-xl sm:text-2xl font-bold text-navy">Contact our <span class="text-cyan-ink">team</span></h2>
                        <p class="mt-1 text-sm text-slate">Fill out the form and our team will get back to you shortly. Fields marked * are required.</p>
                        @if (! empty($c['reply_note']))
                            <p class="mt-1 text-sm font-medium text-navy">{{ $c['reply_note'] }}</p>
                        @endif
                    </div>

                    @if (session('success'))
                        <div data-reveal="fade" class="mt-6"><x-form.alert>{{ session('success') }}</x-form.alert></div>
                    @endif

                    @if ($errors->any())
                        <div data-reveal="fade" class="mt-6"><x-form.alert type="error">Please fix the highlighted fields and try again.</x-form.alert></div>
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
                                <input name="subject" type="text" value="{{ old('subject', $prefill) }}" placeholder="Application / Product" class="{{ $input }}">
                            </div>
                            <div class="md:col-span-2">
                                <textarea name="message" rows="4" placeholder="Please elaborate your requirement *" required data-rule="required" data-label="Message" @input="clean($el)" @blur="check($el)"
                                    class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm text-navy placeholder:text-slate outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition">{{ old('message') }}</textarea>
                                <x-form.error name="message" class="pl-4" />
                            </div>
                        </div>

                        <div class="mt-4" x-data="{ more: {{ (old('company') || old('city') || $errors->has('company') || $errors->has('city')) ? 'true' : 'false' }} }">
                            <button type="button" @click="more = !more" :aria-expanded="more" aria-controls="more-details"
                                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-link transition hover:text-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan">
                                <span x-text="more ? '- Hide company and city' : '+ Add company and city (optional)'"></span>
                            </button>
                            <div id="more-details" x-cloak x-show="more" x-transition.opacity.duration.200ms class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <input name="company" type="text" value="{{ old('company') }}" placeholder="Company / Organisation" class="{{ $input }}">
                                </div>
                                <div>
                                    <input name="city" type="text" value="{{ old('city') }}" placeholder="City" class="{{ $input }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 text-center">
                            <x-form.submit label="Submit" />
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== Prefer to talk? ===== --}}
        <div data-reveal="zoom" class="relative mt-20 overflow-hidden rounded-3xl bg-gradient-to-br from-navy to-link px-8 py-10 sm:px-12 sm:py-12
                    flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full border-[26px] border-white/5"></div>
            <div class="relative text-center sm:text-left">
                <h3 class="text-xl sm:text-2xl font-bold text-white">Prefer to talk?</h3>
                <p class="mt-2 max-w-md text-sm text-white/75">Call {{ $c['departments'][0]['person'] }} directly, or message us on WhatsApp.</p>
            </div>
            <div class="relative flex shrink-0 flex-col items-center gap-3 sm:flex-row">
                <a href="tel:{{ preg_replace('/\s+/', '', $c['call']) }}" class="btn-glass px-7 py-3">
                    {{ $c['call'] }}
                    <svg class="arrow-nudge h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                    </svg>
                </a>
                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center gap-2 rounded-full border border-white/40 px-7 py-3 text-sm font-semibold text-white transition hover:bg-white hover:text-navy">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    WhatsApp
                </a>
            </div>
        </div>

    </div>
  </div>
</x-layouts.app>