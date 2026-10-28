<x-filament-panels::page>
    {{-- leaving this screen before the box is ticked asks once: the password is shown only now --}}
    <div class="sx-page rp-page" x-data x-on:beforeunload.window="if (! $wire.given) { $event.preventDefault(); $event.returnValue = ''; }">

        <header class="sx-head">
            <div>
                <p class="sx-eyebrow">Add a rep &middot; step 2 of 2</p>
                <h1 class="sx-h1">{{ $name }} can sign in</h1>
                <p class="sx-sub">Give these two to {{ $name }}. The password is shown only now.</p>
            </div>
        </header>

        <section class="sx-card rp-ready" aria-label="Sign-in details of {{ $name }}">
            <div class="rp-secret">
                <div>
                    <p class="sx-eyebrow">Employee code</p>
                    <p class="rp-secret-value" data-test="code">{{ $code }}</p>
                </div>
                <div>
                    <p class="sx-eyebrow">Password</p>
                    <p class="rp-secret-value" data-test="password">{{ $this->password }}</p>
                </div>
            </div>
            @if ($phoneText)<p class="sx-small">Phone number: {{ $phoneText }} (the rep can sign in with it too)</p>@endif

            <div class="rp-ready-actions">
                <button type="button" class="sx-btn" x-data="{ done: false }" x-on:click="navigator.clipboard?.writeText(@js($message)); done = true; setTimeout(() => done = false, 2000)">
                    <span x-show="! done">Copy message</span><span x-show="done" x-cloak>Copied</span>
                </button>
                @if ($whatsapp)
                    <a class="sx-btn" href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer">Send on WhatsApp</a>
                @endif
            </div>

            <label class="rp-check">
                <input type="checkbox" wire:model.live="given">
                <span>I have given these to {{ $name }}</span>
            </label>

            <div class="rp-ready-actions">
                <button type="button" class="sx-btn sx-btn-primary" wire:click="done" @disabled(! $this->given) aria-disabled="{{ $this->given ? 'false' : 'true' }}">Done</button>
            </div>
        </section>
    </div>
</x-filament-panels::page>
