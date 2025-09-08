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

    @include('partials.analytics')
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
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-sky-600 dark:bg-sky-500 text-white px-6 py-3 text-sm font-semibold shadow-sm hover:bg-sky-500 dark:hover:bg-sky-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 transition">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-sky-600 dark:bg-sky-500 text-white px-6 py-3 text-sm font-semibold shadow-sm hover:bg-sky-500 dark:hover:bg-sky-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 transition">Log
                                In</a>
                        @endauth
                        @auth
                            <a href="{{ route('catches.analytics') }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-500 hover:to-cyan-500 text-white px-6 py-3 text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 transition">View
                                Analytics</a>
                        @endauth
                    </div>
                </div>

                @if(isset($landingTotalSummary))
                <div class="mt-14 space-y-10">
                    <div>
                        <h2 class="text-sm font-semibold tracking-wide text-sky-600 dark:text-sky-400 uppercase">Live Public Summary</h2>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400 max-w-xl">An anonymized snapshot of catches recorded across the platform. Sign in for personalized analytics.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                        <div class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white/70 dark:bg-neutral-900/60 backdrop-blur shadow-sm">
                            <div class="text-[11px] font-medium uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Total Catches</div>
                            <div class="mt-1 text-2xl font-bold text-neutral-900 dark:text-white">{{ $landingTotalSummary->catches }}</div>
                        </div>
                        <div class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white/70 dark:bg-neutral-900/60 backdrop-blur shadow-sm">
                            <div class="text-[11px] font-medium uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Total Quantity (kg)</div>
                            <div class="mt-1 text-2xl font-bold text-neutral-900 dark:text-white">{{ number_format($landingTotalSummary->total_qty, 2) }}</div>
                        </div>
                        <div class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white/70 dark:bg-neutral-900/60 backdrop-blur shadow-sm">
                            <div class="text-[11px] font-medium uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Total Count (pcs)</div>
                            <div class="mt-1 text-2xl font-bold text-neutral-900 dark:text-white">{{ $landingTotalSummary->total_count }}</div>
                        </div>
                        <div class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white/70 dark:bg-neutral-900/60 backdrop-blur shadow-sm">
                            <div class="text-[11px] font-medium uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Avg Size (cm)</div>
                            <div class="mt-1 text-2xl font-bold text-neutral-900 dark:text-white">{{ $landingTotalSummary->avg_size ? number_format($landingTotalSummary->avg_size,1) : '—' }}</div>
                        </div>
                    </div>
                    <div class="grid gap-8 lg:grid-cols-2">
                        <div>
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400 mb-2">Top Species (Qty)</h3>
                            <ul class="space-y-1 text-sm">
                                @forelse($landingTopSpecies as $row)
                                    <li class="flex justify-between border-b border-neutral-100 dark:border-neutral-800 py-1"><span>{{ $row->species?->common_name ?? 'Unknown' }}</span><span class="text-neutral-500 dark:text-neutral-400">{{ number_format($row->qty_sum,2) }} kg</span></li>
                                @empty
                                    <li class="text-neutral-400 italic">No data</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400 mb-2">Last 7 Days (Qty)</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs border-separate border-spacing-y-1">
                                    <thead>
                                        <tr class="text-left text-neutral-500 dark:text-neutral-400">
                                            <th class="py-1">Date</th>
                                            <th class="py-1">Qty (kg)</th>
                                            <th class="py-1">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($landingDailySeries as $d)
                                            <tr class="bg-white dark:bg-neutral-900/60 rounded">
                                                <td class="py-1 px-1 font-medium">{{ $d->d }}</td>
                                                <td class="py-1 px-1">{{ number_format($d->qty,2) }}</td>
                                                <td class="py-1 px-1">{{ $d->catch_count }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-neutral-400 italic py-2">No data</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
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
