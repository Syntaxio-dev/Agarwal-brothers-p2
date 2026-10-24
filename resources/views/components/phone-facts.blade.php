@props(['show' => ['years', 'customers', 'brands']])
{{-- Phone only: three quick facts as small pills (numbers come from App\Support\Company / config/company.php). --}}
@php
    $all = [
        'years' => [\App\Support\Company::yearsShown(), 'Years', 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'customers' => [number_format(config('company.customers')), 'Customers', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
        'industries' => [config('company.industries'), 'Sectors', 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21'],
        'brands' => [config('company.brands'), 'Brands', 'M12.75 3.03v.568c0 .334.148.65.405.864l1.068.89c.442.369.535 1.01.216 1.49l-.51.766a2.25 2.25 0 0 1-1.161.886l-.143.048a1.107 1.107 0 0 0-.57 1.664c.369.555.169 1.307-.427 1.605L9 13.125l.423 1.059a.956.956 0 0 1-1.652.928l-.679-.906a1.125 1.125 0 0 0-1.906.172L4.5 15.75l-.612.153M12.75 3.031a9 9 0 0 1 6.69 14.036m0 0-.177-.529A2.25 2.25 0 0 0 17.128 15H16.5l-.324-.324a1.453 1.453 0 0 0-2.328.377l-.036.073a1.586 1.586 0 0 1-.982.816l-.99.282c-.55.157-.894.702-.8 1.267l.073.438c.08.474.49.821.97.821.846 0 1.598.542 1.865 1.345l.215.643m-3.414 1.768A9.004 9.004 0 0 1 3.75 12c0-1.26.26-2.46.727-3.55'],
    ];
@endphp
<div {{ $attributes->merge(['class' => 'grid grid-cols-3 gap-2 md:hidden']) }} aria-label="Agarwal Brothers at a glance">
    @foreach ($show as $key)
        @php [$pillNumber, $pillLabel, $pillIcon] = $all[$key]; @endphp
        <div class="flex min-w-0 items-center gap-2 rounded-2xl border border-gray-100 bg-ice px-2 py-2">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-navy to-link text-white" aria-hidden="true">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $pillIcon }}"/></svg>
            </span>
            <span class="min-w-0">
                <span class="block text-sm font-extrabold leading-none text-navy">{{ $pillNumber }}+</span>
                <span class="mt-1 block truncate font-mono text-[9px] uppercase tracking-wide text-slate">{{ $pillLabel }}</span>
            </span>
        </div>
    @endforeach
</div>
