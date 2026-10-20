{{-- Four company facts in a thin bar (Contact Us and Careers). Numbers come from App\Support\Company, so they stay correct by themselves. --}}
<div data-reveal class="mx-auto max-w-4xl grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-gray-100 bg-gray-100 shadow-sm sm:grid-cols-4 {{ $class ?? '' }}">
    @foreach ([
        [\App\Support\Company::yearsShown() . '+', 'Years of experience'],
        [\App\Support\Company::plus('customers'), 'Customers'],
        [\App\Support\Company::plus('brands'), 'Global brands'],
        [\App\Support\Company::plus('industries'), 'Industries served'],
    ] as [$stat, $statLabel])
        <div class="bg-white px-4 py-4 text-center">
            <p class="font-mono text-xl font-semibold text-navy sm:text-2xl">{{ $stat }}</p>
            <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.18em] text-slate">{{ $statLabel }}</p>
        </div>
    @endforeach
</div>
