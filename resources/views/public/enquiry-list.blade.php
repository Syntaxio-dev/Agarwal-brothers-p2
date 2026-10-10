@php
    $crumbs = [['Home', '/'], ['Enquiry list', null]];
    $sent = session('enquiry_sent');
    $input = 'w-full h-11 rounded-xl border border-gray-200 bg-white px-4 text-sm text-navy placeholder:text-slate outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20';
@endphp

<x-layouts.app title="Enquiry list" description="Send one enquiry for several laboratory instruments." :noindex="true">
  <div class="relative overflow-hidden"
       x-data
       @if ($sent) x-init="$store.enquiryList.clear()" @endif>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[480px]
                bg-[radial-gradient(900px_380px_at_50%_-8%,rgba(0,180,216,0.14),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-16 py-10 sm:py-14">
        @include('public.partials.breadcrumb', ['items' => $crumbs])

        <span data-reveal class="section-badge">Group enquiry</span>
        <h1 data-reveal class="mt-3 text-3xl font-bold leading-tight text-navy sm:text-4xl">Your enquiry list</h1>
        <p data-reveal class="mt-2 max-w-2xl text-sm leading-relaxed text-slate">
            Need more than one instrument? Add them all, set the quantities and send a single enquiry. Our team will reply with one combined quotation.
        </p>

        @if ($sent)
            {{-- Sent --}}
            <div data-reveal="zoom" class="mt-10 rounded-2xl border border-success/30 bg-white px-6 py-14 text-center shadow-sm">
                <span class="pop-icon mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-success/15 text-success">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </span>
                <h2 class="mt-5 text-2xl font-bold text-navy">Enquiry sent</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate">
                    Thank you! We received your enquiry for {{ $sent }} {{ $sent === 1 ? 'product' : 'products' }} and will contact you soon.
                </p>
                <div class="mt-7 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('verticals.index') }}" class="btn-primary inline-flex items-center rounded-full px-7 py-3 text-sm font-semibold text-white">Continue browsing</a>
                    <a href="{{ route('home') }}" class="btn-ghost">Back to home</a>
                </div>
            </div>
        @else
            {{-- Empty --}}
            <div data-reveal x-cloak x-show="! $store.enquiryList.count" class="mt-10 rounded-2xl border border-gray-200 bg-white px-6 py-16 text-center">
                <p class="text-lg font-semibold text-navy">Your enquiry list is empty.</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate">
                    Use <span class="font-mono font-semibold text-link">Add to list</span> on any product to collect it here.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('verticals.index') }}" class="btn-primary inline-flex items-center rounded-full px-6 py-2.5 text-sm font-semibold text-white">Browse products</a>
                    <a href="{{ route('search') }}" class="btn-ghost">Search</a>
                </div>
                <x-help-links :verticals="false" />
            </div>

            <div x-cloak x-show="$store.enquiryList.count" class="mt-10 grid grid-cols-1 gap-8 lg:grid-cols-5">

                {{-- Products --}}
                <div class="lg:col-span-3">
                    <div data-reveal class="flex items-center gap-4">
                        <h2 class="text-lg font-bold text-navy sm:text-xl">
                            Products <span class="font-mono text-sm font-medium text-link" x-text="'(' + $store.enquiryList.count + ')'"></span>
                        </h2>
                        <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                    </div>

                    <div class="mt-5 space-y-3">
                        <template x-for="(p, n) in $store.enquiryList.items" :key="p.slug">
                            <div :style="'--i:' + Math.min(n, 6)"
                                 class="row-in group flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm transition-all duration-300 hover:border-cyan/40 hover:shadow-md sm:p-4">
                                <span class="hidden w-6 shrink-0 text-center font-mono text-xs text-slate sm:block" x-text="n + 1"></span>
                                <a :href="'/products/' + p.slug" class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-ice p-2">
                                    <img x-show="p.image" :src="p.image" :alt="p.name" class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105">
                                    <span x-show="! p.image" class="font-mono text-xs text-slate">No image</span>
                                </a>
                                <div class="min-w-0 flex-1">
                                    <p x-show="p.brand" x-text="p.brand" class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-link"></p>
                                    <a :href="'/products/' + p.slug" class="block text-sm font-bold leading-snug text-navy transition-colors hover:text-link group-hover:text-link sm:text-base" x-text="p.name"></a>
                                    <p x-show="p.category" x-text="p.category" class="mt-0.5 text-xs text-slate"></p>
                                </div>

                                {{-- Quantity --}}
                                <div class="flex shrink-0 flex-col items-end gap-2">
                                    <div class="flex items-center overflow-hidden rounded-lg border border-gray-200 bg-white">
                                        <button type="button" @click="$store.enquiryList.setQty(p.slug, p.qty - 1)" :disabled="p.qty <= 1" aria-label="Decrease quantity"
                                                class="flex h-9 w-9 items-center justify-center text-navy hover:bg-ice disabled:opacity-30">&minus;</button>
                                        <input type="number" min="1" max="999" inputmode="numeric" :value="p.qty" aria-label="Quantity"
                                               @change="$store.enquiryList.setQty(p.slug, $event.target.value); $event.target.value = p.qty"
                                               class="h-9 w-12 border-x border-gray-200 bg-white text-center font-mono text-sm font-semibold text-navy outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                        <button type="button" @click="$store.enquiryList.setQty(p.slug, p.qty + 1)" aria-label="Increase quantity"
                                                class="flex h-9 w-9 items-center justify-center text-navy hover:bg-ice">+</button>
                                    </div>
                                    <button type="button" @click="$store.enquiryList.remove(p.slug)"
                                            class="font-mono text-[11px] font-medium uppercase tracking-wider text-slate transition-colors hover:text-alert">Remove &times;</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div data-reveal class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ route('verticals.index') }}" class="btn-ghost">+ Add more products</a>
                        <button type="button" @click="$store.enquiryList.clear()" class="text-xs font-semibold text-slate hover:text-navy">Clear list</button>
                    </div>
                </div>

                {{-- Form --}}
                <div class="lg:col-span-2">
                    <div data-reveal="right" id="enquiry-details" class="scroll-mt-6 rounded-3xl border border-cyan/20 bg-gradient-to-br from-ice to-cyan/10 p-3 sm:p-4 lg:sticky lg:top-6">
                        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                            <span class="section-badge">Your details</span>
                            <p class="mt-2 text-sm text-slate">
                                <span class="font-semibold text-navy" x-text="$store.enquiryList.count"></span> products,
                                <span class="font-semibold text-navy" x-text="$store.enquiryList.units"></span> units in total.
                            </p>

                            @if ($errors->any())
                                <div class="mt-4"><x-form.alert type="error">{{ $errors->has('items') ? $errors->first('items') : 'Please fix the highlighted fields and try again.' }}</x-form.alert></div>
                            @endif

                            <form method="POST" action="{{ route('enquiry-list.store') }}" class="mt-5 space-y-4" x-data="formGuard(@js(collect($errors->messages())->map(fn ($m) => $m[0])->all()))" @submit="submit($event)" novalidate>
                                @csrf
                                <div class="hidden" aria-hidden="true">
                                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                                </div>

                                {{-- The list is sent with the form --}}
                                <template x-for="(p, n) in $store.enquiryList.items" :key="p.slug">
                                    <span>
                                        <input type="hidden" :name="'items[' + n + '][slug]'" :value="p.slug">
                                        <input type="hidden" :name="'items[' + n + '][qty]'" :value="p.qty">
                                    </span>
                                </template>

                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-navy">Name <span class="text-alert">*</span></label>
                                    <input type="text" name="name" value="{{ old('name') }}" required data-rule="required name" data-clean="name" data-label="Name" maxlength="80" autocomplete="name" @input="clean($el)" @blur="check($el)" class="{{ $input }}">
                                    <x-form.error name="name" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-navy">Email <span class="text-alert">*</span></label>
                                    <input type="email" name="email" value="{{ old('email') }}" required data-rule="required email" data-label="Email" maxlength="255" autocomplete="email" @input="clean($el)" @blur="check($el)" class="{{ $input }}">
                                    <x-form.error name="email" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-navy">Phone</label>
                                    <x-form.phone :input="$input" rounded="rounded-xl" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-navy">Company</label>
                                    <input type="text" name="company" value="{{ old('company') }}" maxlength="255" class="{{ $input }}">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-navy">Where is the order coming from?</label>
                                    <input type="text" name="order_location" value="{{ old('order_location') }}" maxlength="255" placeholder="City / state" class="{{ $input }}">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-navy">Message</label>
                                    <textarea name="message" rows="3" maxlength="3000" placeholder="Anything we should know: application, delivery time, specifications..."
                                        class="w-full resize-y rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-navy placeholder:text-slate outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20">{{ old('message') }}</textarea>
                                </div>

                                <x-form.submit class="w-full" loading="Sending..."
                                    label-expr="'Send enquiry for ' + $store.enquiryList.count + ($store.enquiryList.count === 1 ? ' product' : ' products')" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
  </div>
</x-layouts.app>
