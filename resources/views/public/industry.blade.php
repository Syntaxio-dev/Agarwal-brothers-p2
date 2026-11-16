<x-layouts.app :title="'Laboratory instruments for ' . $industry->name"
               :description="'Laboratory equipment and instruments used in ' . $industry->name . ' from Agarwal Brothers, Jaipur. Compare products and send an enquiry.'">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-8 sm:py-14 lg:py-16">

        @include('public.partials.breadcrumb', ['items' => [
            ['Home', '/'],
            ['Industries', null],
            [$industry->name, null],
        ]])

        <div data-reveal>
            <span class="section-badge">Industry</span>
            <h1 class="mt-3 text-[1.65rem] sm:text-3xl lg:text-4xl font-bold text-navy">Instruments used in {{ $industry->name }}</h1>
            <p class="mt-3 max-w-3xl text-[15px] leading-relaxed text-slate sm:text-base">
                @if ($products->count())
                    {{ $products->count() }} {{ \Illuminate\Support\Str::plural('product', $products->count()) }} that laboratories in this industry use.
                    Open one to see its specifications, or add it to your enquiry list.
                @else
                    We are adding the instruments used in this industry. Tell us what you need and our team will recommend the right ones.
                @endif
            </p>
        </div>

        @if ($products->count())
            <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    @include('public.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <div data-reveal class="mt-8 rounded-2xl border border-gray-200 bg-ice p-6 sm:p-8">
                <p class="text-lg font-bold text-navy">Need instruments for {{ $industry->name }}?</p>
                <p class="mt-1 text-slate">Send us your requirement and we will reply with a quotation.</p>
                <a href="{{ route('contact') }}#contact-form" class="btn-primary mt-5 inline-flex items-center rounded-full px-6 py-2.5 text-sm font-semibold text-white">Send an enquiry</a>
            </div>
        @endif
    </div>
</x-layouts.app>
