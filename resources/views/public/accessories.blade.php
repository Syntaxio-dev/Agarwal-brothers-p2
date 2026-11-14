<x-layouts.app :title="$brand->name . ' Accessories: Calibration Kits and More'" :description="'Calibration test kits and other accessories for ' . $brand->name . ' instruments from Agarwal Brothers, Jaipur. Add what you need to your enquiry list.'" :noindex="true">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-8 sm:py-14 lg:py-16">

        @include('public.partials.breadcrumb', ['items' => [
            ['Home', '/'],
            ['Verticals', route('verticals.index')],
            [$brand->name, route('brand.show', $brand->slug)],
            ['Accessories', null],
        ]])

        <div data-reveal>
            <span class="section-badge">Accessories</span>
            <h1 class="mt-3 text-[1.65rem] sm:text-3xl lg:text-4xl font-bold text-navy">{{ $brand->name }} Accessories</h1>
            <p class="mt-3 max-w-3xl text-[15px] leading-relaxed text-slate sm:text-base">Calibration test kits and other things that go with {{ $brand->name }} instruments. Add what you need to your enquiry list and we will reply with a combined quote.</p>
        </div>

        @php
            $groups = [
                ['title' => 'For ' . $brand->name . ' instruments', 'items' => $own],
                ['title' => 'General accessories for any brand', 'items' => $general],
            ];
        @endphp

        @foreach ($groups as $group)
            @if ($group['items']->count())
                <h2 data-reveal class="mt-10 text-lg font-bold text-navy">{{ $group['title'] }}</h2>
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach ($group['items'] as $accessory)
                        <div data-reveal class="group flex flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-cyan/40 hover:shadow-lg">
                            <div class="flex h-40 items-center justify-center bg-ice p-2 max-sm:h-32">
                                @if ($accessory->image)
                                    <img src="{{ asset('storage/' . $accessory->image) }}" alt="{{ $accessory->name }}"
                                         class="img-load max-h-full max-w-full object-contain mix-blend-multiply" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($accessory->image) !!} onload="this.classList.add('is-loaded')">
                                @else
                                    <svg class="h-10 w-10 text-slate/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085"/></svg>
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col p-4 max-sm:p-3">
                                <h3 class="text-sm font-bold leading-snug text-navy">{{ $accessory->name }}</h3>
                                @if ($accessory->short_description)
                                    <p class="mt-1.5 line-clamp-3 text-[13px] leading-relaxed text-slate">{{ $accessory->short_description }}</p>
                                @endif
                                <div class="mt-auto pt-3">
                                    @include('public.partials.accessory-toggle', ['accessory' => $accessory, 'class' => 'w-full'])
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach

        @if ($own->isEmpty() && $general->isEmpty())
            <div data-reveal="fade" class="mt-10 py-16 text-center text-slate">
                <p class="text-lg">No accessories listed for {{ $brand->name }} yet.</p>
                <a href="{{ route('contact') }}#contact-form" class="btn-primary mt-5 inline-flex items-center rounded-full px-6 py-2.5 text-sm font-semibold text-white">Ask our team</a>
            </div>
        @else
            <div data-reveal class="mt-10 flex flex-wrap items-center gap-3">
                <a href="{{ route('enquiry-list') }}" class="btn-primary inline-flex items-center rounded-full px-6 py-2.5 text-sm font-semibold text-white">Go to my enquiry list
                    <span x-show="$store.enquiryList.count" x-cloak class="ml-2 rounded-full bg-white/20 px-2 text-xs" x-text="$store.enquiryList.count"></span>
                </a>
                <a href="{{ route('brand.show', $brand->slug) }}" class="btn-ghost">Back to {{ $brand->name }}</a>
            </div>
        @endif
    </div>
</x-layouts.app>
