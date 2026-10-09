<x-layouts.app :title="$product->name">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14 lg:py-16">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy">{{ $product->category->brand->name }}</span>
            <span class="mx-1.5">/</span>
            <a href="{{ route('category.show', [$product->category->brand->slug, $product->category->slug]) }}"
                class="hover:text-link transition">{{ $product->category->name }}</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">{{ $product->name }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

            {{-- Product Image --}}
            <div class="rounded-2xl bg-ice ring-1 ring-gray-100 overflow-hidden flex items-center justify-center p-8 min-h-[320px]">
                @if ($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                        class="max-h-[400px] max-w-full object-contain">
                @else
                    <span class="text-slate">No image available</span>
                @endif
            </div>

            {{-- Product Details --}}
            <div>
                @if ($product->category?->brand)
                    <p class="text-sm font-bold uppercase tracking-wide text-cyan">
                        {{ $product->category->brand->name }}
                    </p>
                @endif

                <h1 class="mt-2 text-2xl sm:text-3xl font-bold text-navy">{{ $product->name }}</h1>

                <p class="mt-1 text-slate">{{ $product->category->name }}</p>

                @if ($product->short_description)
                    <p class="mt-4 text-slate leading-relaxed">{{ $product->short_description }}</p>
                @endif

                {{-- Specs Table --}}
                @if ($product->specs)
                    <div class="mt-6 rounded-xl bg-ice ring-1 ring-gray-100 overflow-hidden">
                        <table class="w-full text-sm">
                            @foreach ($product->specs as $key => $value)
                                <tr class="border-b border-gray-100 last:border-0">
                                    <td class="py-3 px-4 font-semibold text-navy w-2/5 bg-white/50">{{ $key }}</td>
                                    <td class="py-3 px-4 text-slate">{{ $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif

                {{-- Send Enquiry Button --}}
                <button onclick="document.getElementById('enquiry-form').classList.remove('hidden')"
                    class="mt-8 inline-flex items-center gap-2 rounded-full bg-cyan px-8 py-3
                           text-sm font-bold text-navy shadow-md
                           hover:bg-navy hover:text-white transition-all duration-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.4 5.25a1.5 1.5 0 0 1-1.6 0L2.25 6.75" />
                    </svg>
                    Send Enquiry
                </button>
            </div>
        </div>

        {{-- Enquiry Form --}}
        <div id="enquiry-form"
            class="{{ (session('success') || $errors->any()) ? '' : 'hidden' }}
                   mt-12 rounded-2xl bg-ice ring-1 ring-gray-100 p-6 sm:p-8 max-w-2xl">

            <h2 class="text-xl font-bold text-navy mb-5">Send an Enquiry</h2>

            @if (session('success'))
                <div class="rounded-xl bg-success/10 border border-success/30 text-success px-5 py-4 mb-5 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl bg-alert/10 border border-alert/30 text-alert px-5 py-4 mb-5">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('enquiry.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-navy mb-1.5">Name <span class="text-alert">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full h-11 rounded-xl border border-gray-200 bg-white px-4
                                   text-sm text-navy placeholder:text-slate/50
                                   outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20
                                   @error('name') border-alert @enderror">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-navy mb-1.5">Email <span class="text-alert">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full h-11 rounded-xl border border-gray-200 bg-white px-4
                                   text-sm text-navy placeholder:text-slate/50
                                   outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20
                                   @error('email') border-alert @enderror">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-navy mb-1.5">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            class="w-full h-11 rounded-xl border border-gray-200 bg-white px-4
                                   text-sm text-navy outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-navy mb-1.5">Application Budget</label>
                        <input type="text" name="budget" value="{{ old('budget') }}"
                            class="w-full h-11 rounded-xl border border-gray-200 bg-white px-4
                                   text-sm text-navy outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-navy mb-1.5">Where is the order coming from?</label>
                    <input type="text" name="order_location" value="{{ old('order_location') }}"
                        class="w-full h-11 rounded-xl border border-gray-200 bg-white px-4
                               text-sm text-navy outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-navy mb-1.5">Message</label>
                    <textarea name="message" rows="3"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3
                               text-sm text-navy outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20
                               resize-y">{{ old('message') }}</textarea>
                </div>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-full bg-navy px-8 py-3
                           text-sm font-bold text-white shadow-md
                           hover:bg-link transition-all duration-300 btn-primary">
                    Submit Enquiry
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
