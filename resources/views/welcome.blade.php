<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full antialiased">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            body {
                background: #f8fafc;
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                margin: 0;
                color: #1f2937;
            }

            a {
                text-decoration: none;
            }
        </style>
    @endif
</head>

<body
    class="h-full bg-gradient-to-br from-white via-white to-slate-50 dark:from-neutral-900 dark:via-neutral-900 dark:to-neutral-950 text-[#222] dark:text-neutral-100 flex flex-col">
    <header class="w-full border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
        @if (Route::has('login'))
            <nav class="mx-auto max-w-7xl flex items-center justify-between px-6 py-4 lg:py-5">

                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <img src="{{ asset('logo/catcha_logo.png') }}" alt="{{ config('app.name') }} Logo"
                        class="h-12 w-auto md:h-16 select-none" loading="lazy">
                </a>

                <!-- Right side buttons -->
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="inline-flex items-center gap-1 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-200 hover:border-neutral-300 dark:hover:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-750 transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white transition-colors">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-4 py-2 text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500 dark:focus-visible:ring-offset-neutral-900 transition">
                                Get Started
                            </a>
                        @endif
                    @endauth
                </div>
            </nav>
        @endif
    </header>


    <main class="flex-1">
        <section class="relative">
            <div class="absolute inset-0 pointer-events-none">
                <div
                    class="h-full w-full opacity-[0.15] dark:opacity-20 bg-[radial-gradient(circle_at_30%_40%,#6366f180,transparent_60%),radial-gradient(circle_at_70%_60%,#8b5cf680,transparent_55%)]">
                </div>
            </div>

            <div class="relative mx-auto max-w-7xl px-6 pt-10 pb-24 lg:pt-16 lg:pb-32">
                <div class="max-w-3xl">
                    {{-- <span class="inline-flex items-center gap-1 rounded-full border border-sky-300/60 dark:border-sky-500/30 bg-sky-50 dark:bg-sky-500/10 px-3 py-1 text-[11px] font-medium uppercase tracking-wide text-sky-700 dark:text-sky-300">Fisheries Intelligence</span> --}}
                    <h1
                        class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-neutral-900 dark:text-white leading-tight">
                        CatchA: Fish Catch Monitoring and Decision Support System for Improved Fishing Practices</h1>
                    <p class="mt-6 text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed max-w-2xl">Log
                        catches, Expert feedbacks, Guidance, AI chats, AI consultations. All in one focused workspace.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-sky-600 dark:bg-sky-500 text-white px-6 py-3 text-sm font-semibold shadow-sm hover:bg-sky-500 dark:hover:bg-sky-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 transition">Start
                                Logging Catches</a>
                        @endif
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-sky-200 dark:border-sky-800 bg-white dark:bg-neutral-800 px-6 py-3 text-sm font-medium text-neutral-700 dark:text-neutral-200 hover:bg-sky-50 dark:hover:bg-neutral-750 transition">Sign
                            In</a>
                        @auth
                            <a href="{{ route('catches.analytics') }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-500 hover:to-cyan-500 text-white px-6 py-3 text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 transition">View
                                Analytics</a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-6 pb-28">
            {{-- <div class="mt-20 rounded-3xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-br from-sky-50 via-white to-white dark:from-neutral-900 dark:via-neutral-900 dark:to-neutral-850 p-10 lg:p-14 flex flex-col lg:flex-row gap-10 items-center">
                    <div class="flex-1 max-w-xl space-y-4">
                        <h2 class="text-2xl font-semibold text-neutral-900 dark:text-white">Turn Catch Logs Into Operational Intelligence</h2>
                        <p class="text-neutral-600 dark:text-neutral-400 text-sm leading-relaxed">Your historical catch record is a predictive asset. By consolidating quantities, species composition, gear usage and temporal signals, the platform surfaces emerging opportunities & risk indicators earlier.</p>
                        <ul class="text-xs text-neutral-600 dark:text-neutral-400 grid gap-2 sm:grid-cols-2">
                            <li class="flex items-start gap-2"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-sky-500"></span> 14‑day velocity snapshots</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span> 6‑month biomass arcs</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-violet-500"></span> Gear ROI ratios</li>
                            <li class="flex items-start gap-2"><span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-fuchsia-500"></span> AI recommendation capture</li>
                        </ul>
                    </div>
                    <div class="flex-1 w-full max-w-md">
                        <div class="relative h-60 rounded-2xl bg-gradient-to-br from-sky-600 via-sky-500 to-cyan-500 dark:from-sky-700 dark:via-sky-600 dark:to-cyan-600 p-4 overflow-hidden flex items-end">
                            <div class="absolute inset-0 opacity-20 mix-blend-overlay bg-[radial-gradient(circle_at_70%_30%,white,transparent_60%),radial-gradient(circle_at_30%_70%,white,transparent_55%)]"></div>
                            <div class="grid grid-cols-4 gap-2 w-full text-[10px] font-mono text-white">
                                @foreach (array_pad([], 12, null) as $i => $n)
                                    @php $h = (int) ((($i+3)*7) % 52) + 8; @endphp
                                    <div class="flex flex-col justify-end gap-1">
                                        <div class="mx-auto w-full rounded bg-white/25" style="height: {{ $h }}px"></div>
                                    </div>
                                @endforeach
                                <span class="col-span-4 text-[10px] tracking-wide mt-2 opacity-80">Sample Trend Visualization</span>
                            </div>
                        </div>
                    </div>
                </div> --}}

            {{-- <div class="mt-20 rounded-3xl border border-sky-200 dark:border-sky-700 bg-white dark:bg-neutral-850 p-8 lg:p-12 flex flex-col md:flex-row items-center gap-10">
                    <div class="flex-1 space-y-3">
                        <h3 class="text-xl font-semibold text-neutral-900 dark:text-white">Ready To Elevate Your Fishery Data?</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">Join now and start building a longitudinal dataset that compounds in strategic value. The earlier you centralize, the sharper your predictive edge.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-sky-600 hover:bg-sky-500 text-white px-6 py-3 text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-850 transition">Create Account</a>
                        @endif
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg border border-sky-300 dark:border-sky-700 bg-white dark:bg-neutral-800 px-6 py-3 text-sm font-medium text-neutral-700 dark:text-neutral-200 hover:bg-sky-50 dark:hover:bg-neutral-750 transition">Sign In</a>
                        @auth
                            <a href="{{ route('catches.analytics') }}" class="inline-flex items-center rounded-lg bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-500 hover:to-cyan-500 text-white px-6 py-3 text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-850 transition">Analytics</a>
                        @endauth
                    </div>
                </div> --}}
            {{-- <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="group rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-850 p-6 hover:shadow-sm transition">
                        <div class="h-10 w-10 rounded-md bg-indigo-600/10 dark:bg-indigo-500/15 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-sm font-semibold">
                            1
                        </div>
                        <h3 class="mt-4 font-semibold text-neutral-800 dark:text-neutral-100">
                            Modern Stack
                        </h3>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Laravel 12, Breeze, Tailwind 3, Alpine, Vite. Fast local iteration + clean conventions.
                        </p>
                    </div>

                    <div class="group rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-850 p-6 hover:shadow-sm transition">
                        <div class="h-10 w-10 rounded-md bg-violet-600/10 dark:bg-violet-500/15 flex items-center justify-center text-violet-600 dark:text-violet-400 text-sm font-semibold">
                            2
                        </div>
                        <h3 class="mt-4 font-semibold text-neutral-800 dark:text-neutral-100">
                            Auth Ready
                        </h3>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Pre-wired authentication, registration, password reset flows. Extend policies and gates easily.
                        </p>
                    </div>

                    <div class="group rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-850 p-6 hover:shadow-sm transition">
                        <div class="h-10 w-10 rounded-md bg-fuchsia-600/10 dark:bg-fuchsia-500/15 flex items-center justify-center text-fuchsia-600 dark:text-fuchsia-400 text-sm font-semibold">
                            3
                        </div>
                        <h3 class="mt-4 font-semibold text-neutral-800 dark:text-neutral-100">
                            Scales Cleanly
                        </h3>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Encourage feature separation, test coverage, and maintainable growth from day one.
                        </p>
                    </div>
                </div> --}}

        </section>
    </main>

    <footer class="mt-auto border-t border-neutral-200 dark:border-neutral-800">
        <div
            class="mx-auto max-w-7xl px-6 py-8 text-xs text-neutral-500 dark:text-neutral-500 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; {{ now()->year }} Catcha. All rights reserved.</p>
            <div class="flex items-center gap-5">
                <a href="#" class="hover:text-neutral-800 dark:hover:text-neutral-300 transition">Docs</a>
                <a href="#" class="hover:text-neutral-800 dark:hover:text-neutral-300 transition">Status</a>
                <a href="#" class="hover:text-neutral-800 dark:hover:text-neutral-300 transition">Privacy</a>
            </div>
        </div>
    </footer>

    @if (Route::has('login'))
        <div class="hidden"></div>
    @endif
</body>

</html>
