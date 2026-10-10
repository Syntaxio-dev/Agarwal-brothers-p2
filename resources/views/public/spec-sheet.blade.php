@php
    $category = $product->category;
    $brand = $category->brand;
    $specs = collect($product->specs ?? [])->filter(fn ($v, $k) => filled($k) && filled($v));
    $features = collect($product->features ?? [])->filter(fn ($f) => filled($f['title'] ?? null));
    $advantages = collect($product->advantages ?? [])->filter(fn ($f) => filled($f['title'] ?? null));
    $overview = filled($product->overview) ? \Illuminate\Support\Str::sanitizeHtml((string) $product->overview) : null;
    $c = config('contact');
    $productUrl = route('product.show', $product->slug);
    $name = $product->heading ?: $product->name;

    // The same content as plain text, for the "Copy specs" button
    $plain = collect([$name, $brand->name . ' | ' . $category->name]);
    if (filled($product->short_description)) {
        $plain->push('', $product->short_description);
    }
    if ($specs->count()) {
        $plain->push('', 'Technical specifications');
        foreach ($specs as $label => $value) {
            $plain->push($label . ': ' . $value);
        }
    }
    foreach ([['Key features', $features], ['Key advantages', $advantages]] as [$heading, $list]) {
        if ($list->count()) {
            $plain->push('', $heading);
            foreach ($list as $item) {
                $plain->push('- ' . $item['title'] . (filled($item['text'] ?? null) ? ': ' . $item['text'] : ''));
            }
        }
    }
    $plain->push('', 'Agarwal Brothers | ' . $c['call'] . ' | ' . $c['mail_24x7'], $productUrl);
    $plainText = $plain->implode("\n");

    $btn = 'inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $name }} | Specification sheet | Agarwal Brothers</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { size: A4; margin: 14mm; }
        @media print {
            html, body { background: #fff !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>

<body class="min-h-screen bg-ice text-navy antialiased print:bg-white" x-data="{ copied: false,
        copy() { const t = @js($plainText); (navigator.clipboard ? navigator.clipboard.writeText(t) : Promise.reject()).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2200) }).catch(() => window.prompt('Copy the specifications', t)) } }"
      @if (request()->boolean('print')) x-init="window.addEventListener('load', () => setTimeout(() => window.print(), 600))" @endif>

    {{-- Toolbar (not printed) --}}
    <div class="sticky top-0 z-10 border-b border-gray-200 bg-white/95 print:hidden" style="box-shadow: inset 0 -2px 0 rgb(0 180 216 / 0.5);">
        <div class="mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <a href="{{ $productUrl }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-link">
                <span class="inline-block transition-transform duration-200 group-hover:-translate-x-1">&larr;</span> Back to product
            </a>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="copy()" class="btn-ghost">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
                    <span x-text="copied ? 'Copied' : 'Copy specs'">Copy specs</span>
                </button>
                <button type="button" onclick="window.print()" class="btn-primary {{ $btn }} text-white">
                    <svg class="arrow-nudge-down h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Print / Save as PDF
                </button>
            </div>
        </div>
    </div>

    {{-- The sheet --}}
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 print:max-w-none print:p-0">
        <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-10 print:rounded-none print:border-0 print:p-0 print:shadow-none">

            <header class="flex items-center justify-between gap-4 border-b-2 border-cyan pb-5">
                <img src="{{ asset('sidebar-logo.png') }}" alt="Agarwal Brothers" class="h-12 w-auto object-contain" {!! \App\Support\Img::publicAttrs('sidebar-logo.png') !!}>
                <p class="text-right font-mono text-[11px] font-medium uppercase leading-relaxed tracking-[0.2em] text-link">
                    Specification sheet<br><span class="text-slate">{{ now()->format('d M Y') }}</span>
                </p>
            </header>

            <section class="mt-8 grid grid-cols-1 items-center gap-6 sm:grid-cols-3 print:grid-cols-3">
                <div class="flex h-48 items-center justify-center rounded-2xl border border-gray-100 bg-ice p-4 sm:col-span-1">
                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">
                    @else
                        <span class="font-mono text-xs text-slate">No image</span>
                    @endif
                </div>
                <div class="sm:col-span-2">
                    <p class="font-mono text-xs font-medium uppercase tracking-[0.2em] text-link">{{ $brand->name }} &middot; {{ $category->name }}</p>
                    <h1 class="mt-2 text-2xl font-bold leading-tight text-navy sm:text-3xl">{{ $name }}</h1>
                    @if ($product->short_description)
                        <p class="mt-3 text-sm leading-relaxed text-slate">{{ $product->short_description }}</p>
                    @endif
                </div>
            </section>

            @if ($overview)
                <section class="mt-8 break-inside-avoid">
                    <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.2em] text-link">Overview</h2>
                    <div class="mt-2 text-sm leading-relaxed text-slate [&_p]:mb-3 [&_ul]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:mb-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_strong]:text-navy">{!! $overview !!}</div>
                </section>
            @endif

            @if ($specs->count())
                <section class="mt-8">
                    <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.2em] text-link">Technical specifications</h2>
                    <table class="mt-3 w-full border-collapse overflow-hidden rounded-xl border border-gray-200 text-left text-sm">
                        <tbody>
                            @foreach ($specs as $label => $value)
                                <tr class="break-inside-avoid border-b border-gray-100 last:border-0 odd:bg-ice">
                                    <th scope="row" class="w-2/5 px-4 py-2.5 align-top text-xs font-semibold uppercase tracking-wide text-slate">{{ $label }}</th>
                                    <td class="px-4 py-2.5 align-top font-medium text-navy">{{ $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endif

            @foreach ([['Key features', $features], ['Key advantages', $advantages]] as [$heading, $list])
                @if ($list->count())
                    <section class="mt-8">
                        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.2em] text-link">{{ $heading }}</h2>
                        <ul class="mt-3 grid grid-cols-1 gap-x-8 gap-y-3 sm:grid-cols-2 print:grid-cols-2">
                            @foreach ($list as $item)
                                <li class="flex break-inside-avoid items-start gap-2.5 text-sm">
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-sm bg-cyan"></span>
                                    <span>
                                        <span class="font-semibold text-navy">{{ $item['title'] }}</span>
                                        @if (filled($item['text'] ?? null))
                                            <span class="block text-slate">{{ $item['text'] }}</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endforeach

            <footer class="mt-10 break-inside-avoid rounded-2xl border border-cyan/25 bg-ice p-5 text-sm print:border-gray-200">
                <p class="font-semibold text-navy">Agarwal Brothers</p>
                <p class="mt-1 text-slate">{{ $c['head_office']['address'] }}</p>
                <p class="mt-1 text-slate">{{ $c['call'] }} &middot; {{ $c['mail_24x7'] }}</p>
                <p class="mt-3 break-all font-mono text-xs text-link">{{ $productUrl }}</p>
                <p class="mt-3 text-xs text-slate">Specifications are indicative; please confirm with our team before ordering.</p>
            </footer>
        </article>
    </main>
</body>
</html>
