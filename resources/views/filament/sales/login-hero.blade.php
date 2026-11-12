@unless (filament()->auth()->check())
    <aside class="rl-hero" aria-label="AB Sales">
        <div class="rl-hero-in">
            <img class="rl-logo" src="{{ asset('sidebar-logo.png') }}" alt="Agarwal Brothers" width="160" height="40">

            <p class="rl-eyebrow">AB Sales</p>
            <h2 class="rl-title">Your sales desk</h2>
            <p class="rl-lead">Enquiries, follow-ups, clients and visits of the field team, on one screen. What you do on your phone is here, and what you do here reaches your phone.</p>

            <ul class="rl-ways">
                <li>
                    <strong>Field rep</strong>
                    <span>Employee code or phone number, and the same password as in the phone app.</span>
                </li>
                <li>
                    <strong>Manager</strong>
                    <span>Your e-mail and your password.</span>
                </li>
            </ul>
        </div>
    </aside>
@endunless
