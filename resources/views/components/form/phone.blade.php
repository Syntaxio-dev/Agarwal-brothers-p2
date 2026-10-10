{{-- Country picker + exactly 10 digits. Goes inside a <form x-data="formGuard(...)">. Submits phone_country (ISO) and the digits. --}}
@props(['name' => 'phone', 'required' => false, 'input', 'id' => null, 'label' => 'Phone', 'rounded' => 'rounded-lg'])

@php($id = $id ?? $name)

<div x-data="countryPicker(@js(\App\Support\DialCodes::options()), @js(old('phone_country', \App\Support\DialCodes::DEFAULT)))"
     @click.outside="open = false" @keydown.escape="open = false" class="relative">

    <input type="hidden" name="phone_country" :value="iso">

    <div class="flex gap-2">
        <button type="button" @click="open = ! open" :aria-expanded="open" aria-haspopup="listbox" aria-label="Country code"
                class="flex h-11 shrink-0 items-center gap-1.5 {{ $rounded }} border border-gray-200 bg-white px-3 font-mono text-sm text-navy transition hover:border-cyan focus:border-cyan focus:outline-none focus:ring-2 focus:ring-cyan/20">
            <span x-text="current.iso" class="font-medium"></span>
            <span class="text-slate" x-text="'+' + current.code"></span>
            <svg class="h-3.5 w-3.5 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
        </button>

        <input id="{{ $id }}" name="{{ $name }}" type="tel" inputmode="numeric" autocomplete="tel-national" maxlength="10"
               value="{{ old($name) }}" placeholder="10-digit mobile number" @if ($required) required @endif
               data-rule="{{ $required ? 'required ' : '' }}phone" data-clean="digits" data-label="{{ $label }}"
               @input="clean($el)" @blur="check($el)"
               class="{{ $input }} min-w-0 flex-1 font-mono tracking-wide">
    </div>

    {{-- Country list --}}
    <div x-cloak x-show="open" x-transition.opacity.duration.150ms
         class="absolute left-0 top-full z-30 mt-1.5 w-72 max-w-[85vw] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-[0_24px_48px_-20px_rgba(11,37,69,0.35)]">
        <div class="border-b border-gray-100 p-2">
            <input type="text" x-model="query" x-ref="search" x-effect="if (open) $nextTick(() => $refs.search.focus())"
                   placeholder="Search country or code" aria-label="Search country"
                   @keydown.enter.prevent="filtered[0] && pick(filtered[0])"
                   class="h-9 w-full rounded-lg border border-gray-200 bg-ice px-3 text-sm text-navy placeholder:text-slate outline-none focus:border-cyan">
        </div>
        <ul class="max-h-56 overflow-y-auto p-1" role="listbox">
            <template x-for="o in filtered" :key="o.iso">
                <li>
                    <button type="button" role="option" :aria-selected="o.iso === iso" @click="pick(o)"
                            :class="o.iso === iso ? 'bg-ice font-semibold' : ''"
                            class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm text-navy hover:bg-ice">
                        <span class="truncate" x-text="o.name"></span>
                        <span class="shrink-0 font-mono text-xs text-slate" x-text="'+' + o.code"></span>
                    </button>
                </li>
            </template>
            <li x-show="! filtered.length" class="px-3 py-4 text-center text-sm text-slate">No country found</li>
        </ul>
    </div>

    <p x-show="! errors['{{ $name }}']" class="mt-1 text-xs text-slate">Digits only, {{ $required ? '' : 'optional, ' }}without the country code.</p>
    <x-form.error :name="$name" />
</div>
