@php
    /** @var \App\Models\Insight $w */
    $start = $w->start_ist;
    $detail = route('insights.show', $w->slug);

    if ($past) {
        $when = $start ? $start->format('F j, Y') : $w->created_at->format('F j, Y');
        $chip = null;
        $href = $w->recording_url ?: $detail;
        $cta = $w->recording_url ? 'Watch Recording' : 'View Details';
        $external = (bool) $w->recording_url;
    } else {
        $when = $start->format('F j') . ' | ' . ($w->time_tbd ? 'Time to be announced' : $start->format('h:i A') . ' IST');
        $days = (int) now(\App\Models\Insight::TZ)->startOfDay()->diffInDays($start->copy()->startOfDay(), false);
        $chip = $days <= 0 ? 'Today' : ($days === 1 ? 'Tomorrow' : $days . ' days left');
        $href = $w->registration_url ?: $detail;
        $cta = $w->registration_url ? 'Register Here' : 'View Details';
        $external = (bool) $w->registration_url;
    }
@endphp

<div class="group flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6 rounded-2xl bg-white border border-gray-200 shadow-sm px-5 py-5
            hover:shadow-lg hover:border-cyan/50 transition-all duration-300">

    <div class="h-20 w-full sm:w-32 shrink-0 rounded-xl bg-white border border-gray-100 flex items-center justify-center p-3">
        @if ($w->brand?->logo)
            <img src="{{ asset('storage/' . $w->brand->logo) }}" alt="{{ $w->brand->name }}" class="max-h-full max-w-full object-contain">
        @elseif ($w->brand)
            <span class="text-sm font-bold text-navy text-center">{{ $w->brand->name }}</span>
        @else
            <svg class="h-8 w-8 text-link/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/>
            </svg>
        @endif
    </div>

    <div class="sm:w-44 shrink-0">
        <p class="text-sm font-semibold text-navy leading-snug">{{ $when }}</p>
        @if ($chip)
            <span class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-cyan/10 border border-cyan/30 px-2.5 py-0.5 text-[11px] font-bold text-link">
                <span class="h-1.5 w-1.5 rounded-full bg-cyan {{ $days <= 1 ? 'animate-pulse' : '' }}"></span>
                {{ $chip }}
            </span>
        @endif
    </div>

    <div class="flex-1 min-w-0">
        <a href="{{ $detail }}" class="text-base font-bold text-navy leading-snug hover:text-link transition-colors line-clamp-2">{{ $w->title }}</a>
        @if ($w->excerpt)
            <p class="mt-1 text-sm text-slate leading-relaxed line-clamp-2">{{ $w->excerpt }}</p>
        @endif
    </div>

    <a href="{{ $href }}" @if ($external) target="_blank" rel="noopener" @endif
       class="btn-primary shrink-0 inline-flex items-center justify-center gap-2 rounded-full bg-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-link">
        {{ $cta }}
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
        </svg>
    </a>
</div>
