<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Agarwal Brothers</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Standalone on purpose: no database access, so this page still renders when the database is down. --}}
<body class="min-h-screen bg-white text-navy antialiased">
    <div class="relative flex min-h-screen flex-col overflow-hidden">
        <div class="pointer-events-none absolute inset-0
                    bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.20),transparent_70%),radial-gradient(700px_380px_at_6%_16%,rgba(0,119,182,0.10),transparent_70%),radial-gradient(700px_380px_at_96%_22%,rgba(0,180,216,0.12),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>
        <div class="pointer-events-none absolute inset-0 opacity-40
                    bg-[linear-gradient(to_right,rgba(11,37,69,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(11,37,69,0.05)_1px,transparent_1px)] bg-[size:44px_44px]
                    [mask-image:linear-gradient(to_bottom,black,transparent_75%)]"></div>

        <header class="relative px-5 py-5 sm:px-10">
            <a href="/" aria-label="Agarwal Brothers home">
                <img src="{{ asset('sidebar-logo.png') }}" alt="Agarwal Brothers" class="img-load h-11 w-auto sm:h-12" decoding="async" onload="this.classList.add('is-loaded')" {!! \App\Support\Img::publicAttrs('sidebar-logo.png') !!}>
            </a>
        </header>

        <main class="relative flex flex-1 items-center justify-center px-5 py-10">
            <div class="w-full max-w-3xl text-center">
                <span class="section-badge">@yield('eyebrow')</span>

                <p class="mt-6 select-none bg-gradient-to-br from-navy to-link bg-clip-text font-mono text-[7rem] font-normal leading-none tracking-tighter text-transparent sm:text-[10rem]">
                    @yield('code')
                </p>

                <h1 class="mt-4 text-2xl font-bold text-navy sm:text-4xl">@yield('heading')</h1>
                <p class="mx-auto mt-4 max-w-xl text-base leading-relaxed text-slate">@yield('message')</p>

                @yield('extra')

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    @hasSection('actions')
                        @yield('actions')
                    @else
                        <a href="/" class="btn-primary inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white">
                            Back to home
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/></svg>
                        </a>
                        <a href="/verticals" class="btn-ghost">Browse verticals</a>
                    @endif
                </div>

                <x-help-links />
            </div>
        </main>

        <footer class="relative px-5 py-5 text-center text-xs text-slate">
            &copy; {{ date('Y') }} Agarwal Brothers
        </footer>
    </div>
</body>
</html>
