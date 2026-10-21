{{-- A tiny do / don't picture next to the main-image upload: a clean photo on a plain light background versus a busy, dark, small one. --}}
<div class="grid grid-cols-2 gap-3 sm:max-w-md" aria-label="Photo examples">
    <figure class="m-0">
        <svg viewBox="0 0 160 110" class="w-full rounded-lg ring-1 ring-success-600/40" role="img" aria-label="Good photo: plain light background, product in the middle and large">
            <rect width="160" height="110" fill="#ffffff"/>
            <ellipse cx="80" cy="92" rx="38" ry="5" fill="#0B2545" opacity=".08"/>
            <rect x="52" y="62" width="56" height="26" rx="4" fill="#0B2545"/>
            <rect x="60" y="40" width="40" height="24" rx="3" fill="#F4F9FB" stroke="#0B2545" stroke-width="3"/>
            <rect x="66" y="46" width="28" height="8" rx="2" fill="#00B4D8"/>
            <circle cx="136" cy="18" r="11" fill="#2A9D8F"/>
            <path d="M130.5 18.5l4 4 7-8" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <figcaption class="mt-1.5 text-xs font-medium text-success-700 dark:text-success-400">Good: plain white background, product large and in the middle, sharp.</figcaption>
    </figure>

    <figure class="m-0">
        <svg viewBox="0 0 160 110" class="w-full rounded-lg ring-1 ring-danger-600/40" role="img" aria-label="Avoid: dark cluttered background, product small and off to the side">
            <rect width="160" height="110" fill="#6C757D"/>
            <rect x="8" y="8" width="38" height="52" rx="3" fill="#0B2545" opacity=".55"/>
            <rect x="60" y="14" width="30" height="20" rx="3" fill="#F4F9FB" opacity=".35"/>
            <circle cx="30" cy="84" r="14" fill="#0B2545" opacity=".5"/>
            <rect x="104" y="70" width="26" height="14" rx="3" fill="#0B2545"/>
            <rect x="109" y="60" width="16" height="12" rx="2" fill="#F4F9FB" stroke="#0B2545" stroke-width="2"/>
            <circle cx="136" cy="18" r="11" fill="#E76F51"/>
            <path d="M131 13l10 10M141 13l-10 10" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <figcaption class="mt-1.5 text-xs font-medium text-danger-700 dark:text-danger-400">Avoid: busy or dark background, product small, cut off or blurry.</figcaption>
    </figure>
</div>
