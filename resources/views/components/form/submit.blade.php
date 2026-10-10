@props(['label' => 'Submit', 'loading' => 'Sending...', 'labelExpr' => null, 'class' => ''])
{{-- Primary submit button with a loading state. Needs formGuard() on the form. labelExpr: optional JS expression for a dynamic label. --}}
<button type="submit" :disabled="sending"
        class="btn-primary inline-flex items-center justify-center gap-2 rounded-full px-9 py-3 text-sm font-semibold text-white disabled:cursor-wait disabled:opacity-70 {{ $class }}">
    <svg x-show="sending" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"/>
    </svg>
    <span x-text="sending ? '{{ $loading }}' : {!! $labelExpr ?: "'" . e($label) . "'" !!}">{{ $label }}</span>
    <svg x-show="! sending" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/></svg>
</button>
