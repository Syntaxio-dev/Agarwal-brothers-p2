@php
    $crumbs = [['Home', '/'], ['Compare products', null]];
    $count = $products->count();
    $diffCount = $rows->where('differs', true)->count();
@endphp

<x-layouts.app title="Compare products" description="Compare technical specifications of laboratory instruments side by side." :noindex="true">
  <div class="relative overflow-hidden"
       x-data="{ diff: false }"
       x-init="$store.compare.set(@js($products->map(fn ($p) => ['slug' => $p->slug, 'name' => $p->name, 'image' => $p->image ? asset('storage/' . $p->image) : ''])->values()))">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[480px]
                bg-[radial-gradient(900px_380px_at_50%_-8%,rgba(0,180,216,0.14),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-16 py-10 sm:py-14">
        @include('public.partials.breadcrumb', ['items' => $crumbs])

        <div class="flex flex-col items-start justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <span data-reveal class="section-badge">Side by side</span>
                <h1 data-reveal class="mt-3 text-3xl font-bold leading-tight text-navy sm:text-4xl">Compare products</h1>
                <p data-reveal class="mt-2 max-w-2xl text-sm leading-relaxed text-slate">
                    See the technical specifications of up to {{ $max }} instruments next to each other.
                </p>
            </div>

            @if ($count > 1 && $diffCount)
                <label data-reveal class="chip cursor-pointer select-none" :class="{ 'chip-active': diff }">
                    <input type="checkbox" x-model="diff" class="sr-only">
                    <span x-text="diff ? 'Showing differences only' : 'Show differences only'">Show differences only</span>
                </label>
            @endif
        </div>

        @if ($count < 2)
            <div data-reveal class="mt-12 rounded-2xl border border-gray-200 bg-white px-6 py-16 text-center">
                <p class="text-lg font-semibold text-navy">Pick at least two products to compare.</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate">Use the <span class="font-mono font-semibold text-link">+ Compare</span> button on any product card, then press "Compare now".</p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('verticals.index') }}" class="btn-primary inline-flex items-center rounded-full px-6 py-2.5 text-sm font-semibold text-white">Browse products</a>
                    <a href="{{ route('search') }}" class="btn-ghost">Search</a>
                </div>
                <x-help-links :verticals="false" />
            </div>
        @else
            <div data-reveal class="mt-8 overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
                <table class="w-full min-w-[640px] border-collapse text-left">
                    <thead>
                        <tr class="align-top">
                            <th class="sticky left-0 z-10 w-40 min-w-[9rem] border-b border-r border-gray-200 bg-ice p-4 font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-link sm:w-52">
                                Specification
                            </th>
                            @foreach ($products as $p)
                                @php($others = $products->reject(fn ($o) => $o->id === $p->id)->pluck('slug')->implode(','))
                                <th class="min-w-[11rem] border-b border-gray-200 p-4 align-top font-normal {{ ! $loop->last ? 'border-r' : '' }}">
                                    <div class="flex justify-end">
                                        <a href="{{ $others ? route('compare', ['p' => $others]) : route('compare') }}"
                                           @click="$store.compare.remove('{{ $p->slug }}')"
                                           class="font-mono text-[11px] font-medium uppercase tracking-wider text-slate hover:text-navy">Remove &times;</a>
                                    </div>
                                    <a href="{{ route('product.show', $p->slug) }}" class="group mt-1 flex h-32 items-center justify-center overflow-hidden rounded-xl bg-ice p-3">
                                        @if ($p->image)
                                            <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" class="img-load max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($p->image) !!} onload="this.classList.add('is-loaded')">
                                        @else
                                            <span class="font-mono text-xs text-slate">No image</span>
                                        @endif
                                    </a>
                                    <p class="mt-3 font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-link">{{ $p->category->brand->name }}</p>
                                    <a href="{{ route('product.show', $p->slug) }}" class="mt-1 block text-base font-bold leading-snug text-navy hover:text-link">{{ $p->name }}</a>
                                    <p class="mt-0.5 text-xs text-slate">{{ $p->category->name }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a href="{{ route('product.show', $p->slug) }}#enquiry-form" class="btn-primary inline-flex items-center rounded-full px-4 py-2 text-xs font-semibold text-white">Enquire</a>
                                        <a href="{{ route('product.show', $p->slug) }}" class="group/d self-center text-xs font-semibold text-link hover:underline">Details <span class="inline-block transition-transform duration-200 group-hover/d:translate-x-1">&rarr;</span></a>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr x-show="! diff || {{ $row['differs'] ? 'true' : 'false' }}" x-transition.opacity.duration.250ms class="border-b border-gray-100 transition-colors last:border-0 hover:bg-ice/70">
                                <th scope="row" class="sticky left-0 z-10 border-r border-gray-200 bg-ice p-4 align-top text-sm font-semibold text-navy">
                                    <span class="flex items-start gap-2">
                                        @if ($row['differs'])
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-sm bg-cyan" title="Differs between these products"></span>
                                        @endif
                                        {{ $row['label'] }}
                                    </span>
                                </th>
                                @foreach ($row['cells'] as $cell)
                                    <td class="p-4 align-top text-sm {{ ! $loop->last ? 'border-r border-gray-100' : '' }} {{ $row['differs'] ? 'font-medium text-navy' : 'text-slate' }}">
                                        @if ($cell !== null)
                                            <span class="spec-value">{{ $cell }}</span>
                                        @else
                                            <span class="text-slate">&mdash;</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $count + 1 }}" class="p-8 text-center text-sm text-slate">No specifications have been added for these products yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p data-reveal="fade" class="mt-3 text-xs text-slate">
                @if ($diffCount)
                    <span class="mr-1 inline-block h-2 w-2 rounded-sm bg-cyan align-middle"></span> marks a specification that differs between these products.
                @else
                    These products share the same values for every listed specification.
                @endif
                Specifications are indicative; please confirm with our team before ordering.
            </p>

            @if ($count < $max)
                <div data-reveal class="mt-6">
                    <a href="{{ route('verticals.index') }}" class="btn-ghost">+ Add another product</a>
                </div>
            @endif
        @endif

        <div data-reveal="zoom" class="mt-16 flex flex-col items-center justify-between gap-5 rounded-3xl bg-gradient-to-br from-navy to-link px-8 py-9 sm:flex-row sm:px-12">
            <div class="text-center sm:text-left">
                <h3 class="text-xl font-bold text-white sm:text-2xl">Not sure which one fits your lab?</h3>
                <p class="mt-1.5 text-sm text-white/75">Tell us your application and our specialists will recommend a model.</p>
            </div>
            <a href="{{ route('contact') }}" class="btn-glass shrink-0 px-7 py-3">Talk to an expert</a>
        </div>
    </div>
  </div>
</x-layouts.app>
