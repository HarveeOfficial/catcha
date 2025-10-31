<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full antialiased">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/catcha_logo_updated.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('logo/catcha_logo_updated.png') }}" />

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

<body class="h-full bg-white text-black flex flex-col">
    <header class="w-full border-b border-neutral-200 bg-white text-black">
        @if (Route::has('login'))
            <nav class="mx-auto max-w-7xl flex items-center justify-between px-6 py-4 lg:py-5">

                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <img src="{{ asset('logo/catcha_logo_updated.png') }}" alt="{{ config('app.name') }} Logo"
                        class="h-12 w-auto md:h-16 select-none" loading="lazy">
                </a>

                <!-- Right side buttons -->
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="inline-flex items-center gap-1 rounded-lg border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-black hover:border-neutral-300 hover:bg-neutral-50 transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-black hover:text-black transition-colors">
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

            <div class="relative mx-auto max-w-7xl px-6 pt-10 pb-24 lg:pt-16 lg:pb-32">
                <div class="grid items-start lg:grid-cols-2 gap-8">
                    <div class="max-w-3xl order-2 lg:order-1">
                        <span class="inline-flex items-center gap-1 rounded-full border border-sky-300/60 dark:border-sky-500/30 bg-sky-50 dark:bg-sky-500/10 px-3 py-1 text-[11px] font-medium uppercase tracking-wide text-sky-700 dark:text-sky-300">CATCHA</span>
                        <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-black leading-tight">CATCHA: Catch Assessment and Tracking for Coastal Harvest Activities for Improved Fishing Practices</h1>
                        <p class="mt-6 text-lg text-black leading-relaxed max-w-2xl">Log catches, Analyze catches, Guides, Weather forecast. All in one focused workspace.</p>
                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-sky-600 dark:bg-sky-500 text-white px-6 py-3 text-sm font-semibold shadow-sm hover:bg-sky-500 dark:hover:bg-sky-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 transition">Go to Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg bg-sky-600 dark:bg-sky-500 text-white px-6 py-3 text-sm font-semibold shadow-sm hover:bg-sky-500 dark:hover:bg-sky-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 transition">Log In</a>
                            @endauth
                            @auth
                                <a href="{{ route('catches.analytics') }}" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-500 hover:to-cyan-500 text-white px-6 py-3 text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-neutral-900 transition">View Analytics</a>
                            @endauth
                        </div>
                    </div>

                    <!-- Logo column: appears to the right on lg+ and above on small screens -->
                    <div class="flex justify-end items-start order-1 lg:order-2">
                        <img src="{{ asset('logo/catcha_logo_updated.png') }}" alt="{{ config('app.name') }} large logo" class="h-56 sm:h-64 md:h-72 lg:h-96 w-auto select-none" loading="lazy">
                    </div>

                </div>

                @if(isset($landingTotalSummary))
                <div class="mt-14 space-y-10">
                    <div>
                        <h2 class="text-sm font-semibold tracking-wide text-black uppercase">Live Public Summary</h2>
                        <p class="mt-2 text-sm text-black max-w-xl">An anonymized snapshot of catches recorded across the platform. Sign in for personalized analytics.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                        <div class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white backdrop-blur shadow-sm">
                            <div class="text-[11px] font-medium uppercase tracking-wide text-black">Total Catches</div>
                            <div class="mt-1 text-2xl font-bold text-neutral-900">{{ $landingTotalSummary->catches }}</div>
                        </div>
                        <div class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white backdrop-blur shadow-sm">
                            <div class="text-[11px] font-medium uppercase tracking-wide text-black">Total Quantity (kg)</div>
                            <div class="mt-1 text-2xl font-bold text-neutral-900">{{ number_format($landingTotalSummary->total_qty, 2) }}</div>
                        </div>
                        <div class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white backdrop-blur shadow-sm">
                            <div class="text-[11px] font-medium uppercase tracking-wide text-black">Total Count (pcs)</div>
                            <div class="mt-1 text-2xl font-bold text-neutral-900">{{ $landingTotalSummary->total_count }}</div>
                        </div>
                        <div class="p-4 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white backdrop-blur shadow-sm">
                            <div class="text-[11px] font-medium uppercase tracking-wide text-black">Avg Size (cm)</div>
                            <div class="mt-1 text-2xl font-bold text-neutral-900">{{ $landingTotalSummary->avg_size ? number_format($landingTotalSummary->avg_size,1) : '—' }}</div>
                        </div>
                    </div>
                    <div class="grid gap-8 lg:grid-cols-2">
                        <div>
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-black mb-2">Top Species (Qty)</h3>
                            <ul class="space-y-1 text-sm">
                                @forelse($landingTopSpecies as $row)
                                    <li class="flex justify-between border-b border-neutral-100 py-1"><span>{{ $row->species?->common_name ?? 'Unknown' }}</span><span class="text-black">{{ number_format($row->qty_sum,2) }} kg</span></li>
                                @empty
                                    <li class="text-neutral-400 italic">No data</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-black mb-2">Last 7 Days (Qty)</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs table-auto">
                                    <thead>
                                        <tr class="text-left text-black">
                                            <th class="py-2 px-3 text-[11px] font-semibold uppercase tracking-wide">Date</th>
                                            <th class="py-2 px-3 text-[11px] font-semibold uppercase tracking-wide">Qty (kg)</th>
                                            <th class="py-2 px-3 text-[11px] font-semibold uppercase tracking-wide">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-neutral-100">
                                        @forelse($landingDailySeries as $d)
                                            <tr>
                                                <td class="py-2 px-3 font-medium text-neutral-800">{{ $d->d }}</td>
                                                <td class="py-2 px-3 text-neutral-800">{{ number_format($d->qty,2) }}</td>
                                                <td class="py-2 px-3 text-neutral-800">{{ $d->catch_count }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-neutral-400 italic py-3 px-3">No data</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                    <!-- Public Heatmap Section -->
                    <section class="max-w-7xl mx-auto my-12">
                        <div class="bg-white p-4 rounded border shadow space-y-4">
                            <h2 class="text-lg font-semibold text-black mb-2">Fishing Grounds Heatmap</h2>
                            <div class="flex items-center justify-between flex-wrap gap-3">
                                <p class="text-sm text-gray-600">Aggregated heatmap of ALL recorded catches (all species, all dates). Publicly viewable.</p>
                                <div class="flex items-center gap-2 text-xs">
                                    <label>Radius <input id="radiusInput" type="range" min="10" max="50" value="22" class="align-middle" /></label>
                                    <label>Blur <input id="blurInput" type="range" min="5" max="40" value="16" class="align-middle" /></label>
                                    <button id="refreshBtn" class="px-3 py-2 bg-indigo-600 text-white rounded font-medium">Reload</button>
                                </div>
                            </div>
                            <div class="flex gap-4 mb-4">
                                <button id="toggleSatellite" class="px-4 py-2 bg-blue-600 text-white rounded">Satellite View</button>
                            </div>
                            <div class="text-xs text-gray-500">Intensity reflects relative catch weight (kg) or count if weight missing. No date filtering applied.</div>
                            <div id="heatmap" class="w-full h-[600px] rounded border border-gray-300 overflow-hidden relative">
                                <div id="heatmapLoader" class="absolute inset-0 flex items-center justify-center text-xs text-gray-500">Loading map…</div>
                            </div>
                        </div>
                    </section>
                    @once
                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
                        <style>
                            #heatmap { min-height:600px; }
                            .leaflet-container { font: inherit; z-index:0; }
                        </style>
                        <script id="leaflet-core" src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
                        <script id="leaflet-heat" src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>
                        <script>
                            // Fallback loaders if a CDN blocked
                            window.addEventListener('error', function(e){
                                if(e.target && e.target.id === 'leaflet-core'){
                                    const alt = document.createElement('script');
                                    alt.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js';
                                    alt.onload = tryInitHeatmap;
                                    document.head.appendChild(alt);
                                }
                                if(e.target && e.target.id === 'leaflet-heat'){
                                    const altH = document.createElement('script');
                                    altH.src = 'https://cdn.jsdelivr.net/npm/leaflet.heat@0.2.0/dist/leaflet-heat.js';
                                    altH.onload = tryInitHeatmap;
                                    document.head.appendChild(altH);
                                }
                            }, true);
                        </script>
                    @endonce
                    <script>
                        function tryInitHeatmap(){
                            if(typeof L === 'undefined'){ return; }
                            if(!document.getElementById('heatmap')){ return; }
                            if(!window.__heatmapInitDone){
                                window.__heatmapInitDone = true;
                                initHeatmap();
                            }
                        }
                        document.addEventListener('DOMContentLoaded', () => {
                            // Delay to ensure CSS applied and container has dimensions
                            setTimeout(tryInitHeatmap, 150);
                            setTimeout(() => { tryInitHeatmap(); }, 600); // second attempt
                        });

                        function initHeatmap(){
                            const container = document.getElementById('heatmap');
                            const loader = document.getElementById('heatmapLoader');
                            if(!container){ return; }
                            try {
                                const map = L.map('heatmap');
                                map.setView([18.33, 121.61], 6);
                                const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'&copy; OSM contributors' });
                                osmLayer.addTo(map);
                                const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                                    maxZoom: 19,
                                    attribution: 'Tiles © Esri'
                                });
                                let isSatellite = false;
                                document.getElementById('toggleSatellite').addEventListener('click', function() {
                                    if (isSatellite) {
                                        map.removeLayer(satelliteLayer);
                                        osmLayer.addTo(map);
                                    } else {
                                        map.removeLayer(osmLayer);
                                        satelliteLayer.addTo(map);
                                    }
                                    isSatellite = !isSatellite;
                                    this.textContent = isSatellite ? 'Standard View' : 'Satellite View';
                                });
                                let heatLayer = null;
                                const radiusInput = document.getElementById('radiusInput');
                                const blurInput = document.getElementById('blurInput');
                                async function loadData(){
                                    if(loader){ loader.textContent = 'Loading data…'; loader.classList.remove('hidden'); }
                                    try {
                                        const res = await fetch('/catches/heatmap/data', { headers: { 'Accept':'application/json' } });
                                        if(!res.ok){ throw new Error('Network '+res.status); }
                                        const json = await res.json();
                                        const pts = json.points || [];
                                        if(heatLayer){
                                            try {
                                                if(map && map.removeLayer && map.hasLayer && map.hasLayer(heatLayer)){
                                                    map.removeLayer(heatLayer);
                                                } else if(typeof heatLayer.remove === 'function'){
                                                    heatLayer.remove();
                                                }
                                            } catch(_) {
                                                // fallback: attempt to call remove if available
                                                try { heatLayer.remove && heatLayer.remove(); } catch(e) {}
                                            }
                                            heatLayer = null;
                                        }
                                        if(!pts.length){ if(loader){ loader.textContent='No points'; } return; }

                                        // Backend may return points as objects {lat,lng,weight} or as arrays [lat,lng,weight].
                                        // Normalize and compute relative intensity (0..1) for leaflet.heat.
                                        const weights = pts.map(p => Array.isArray(p) ? (p[2] ?? 1) : (p.weight ?? p.qty ?? 1));
                                        const max = Math.max(...weights.map(w => (Number.isFinite(w) ? w : 0)));
                                        const scaled = pts.map((p, i) => {
                                            const lat = Array.isArray(p) ? p[0] : p.lat;
                                            const lng = Array.isArray(p) ? p[1] : p.lng;
                                            const raw = Array.isArray(p) ? (p[2] ?? 1) : (p.weight ?? p.qty ?? 1);
                                            const w = max ? (raw / max) : 0.2;
                                            return [lat, lng, w];
                                        });

                                        heatLayer = L.heatLayer(scaled, { radius: parseInt(radiusInput.value,10), blur: parseInt(blurInput.value,10), maxZoom: 11, minOpacity: 0.25 }).addTo(map);
                                        if(loader){ loader.classList.add('hidden'); }
                                    } catch(err){
                                        if(loader){ loader.textContent = 'Failed loading data'; }
                                    }
                                }
                                // Popup info on click
                                map.on('click', async (e) => {
                                    const { lat, lng } = e.latlng;
                                    const url = `/catches/heatmap/point-info?lat=${lat}&lon=${lng}&zoom=${map.getZoom()}`;
                                    const popup = L.popup({ maxWidth: 320 }).setLatLng(e.latlng).setContent('<div class="text-xs text-gray-500 p-2">Loading…</div>').openOn(map);
                                    try {
                                        const res = await fetch(url, { headers: { 'Accept':'application/json' } });
                                        if(!res.ok){ throw new Error('Bad status'); }
                                        const data = await res.json();
                                        const s = data.summary;
                                        let html = `<div class='p-2 text-[11px] leading-snug'><div class='font-semibold mb-1'>Area (~${data.radius_km} km radius)</div>`;
                                        html += `<div class='mb-1'>Catches: <strong>${s.catches}</strong> | Qty: <strong>${s.total_qty.toFixed(2)}</strong> kg | Count: <strong>${s.total_count}</strong></div>`;
                                        if(data.species.length){
                                            html += '<div class="max-h-40 overflow-auto border-t pt-1 mt-1"><table class="w-full text-[10px]">';
                                            html += '<thead><tr class="text-gray-500"><th class="text-left">Species</th><th>Qty</th><th>Catch</th></tr></thead><tbody>';
                                            data.species.forEach(r => { html += `<tr><td>${r.name}</td><td class='text-right'>${r.qty.toFixed(2)}</td><td class='text-right'>${r.catches}</td></tr>`; });
                                            html += '</tbody></table></div>';
                                        } else {
                                            html += '<div class="italic text-gray-400">No species data here.</div>';
                                        }
                                        html += '</div>';
                                        popup.setContent(html);
                                    } catch(err){
                                        popup.setContent('<div class="p-2 text-xs text-red-600">Failed to load details.</div>');
                                    }
                                });
                                document.getElementById('refreshBtn').addEventListener('click', e => { e.preventDefault(); loadData(); });
                                // Always reload on slider change so radius/blur updates immediately.
                                radiusInput.addEventListener('input', () => { loadData(); });
                                blurInput.addEventListener('input', () => { loadData(); });
                                // ensure proper sizing after layout
                                requestAnimationFrame(()=>{ map.invalidateSize(); });
                                setTimeout(()=>{ map.invalidateSize(); }, 400);
                                loadData();
                            } catch(e){
                                if(loader){ loader.textContent = 'Map init failed'; }
                            }
                        }
                    </script>
            </div>
        </section>
    </main>

    @include('layouts.footer')

    @if (Route::has('login'))
        <div class="hidden"></div>
    @endif
</body>

</html>
