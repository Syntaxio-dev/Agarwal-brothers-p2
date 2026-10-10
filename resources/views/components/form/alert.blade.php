@props(['type' => 'success'])
{{-- Result message for a form: scrolls into view when it appears. --}}
@php($ok = $type === 'success')
<div x-data x-init="$nextTick(() => $el.scrollIntoView({ block: 'center', behavior: 'smooth' }))" role="{{ $ok ? 'status' : 'alert' }}"
     class="flex items-start gap-3 rounded-xl border px-5 py-4 text-sm text-navy {{ $ok ? 'border-success/30 bg-success/10' : 'border-alert/30 bg-alert/10' }}">
    <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $ok ? 'text-success' : 'text-alert' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        @if ($ok)
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        @else
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
        @endif
    </svg>
    <div class="font-medium">{{ $slot }}</div>
</div>
