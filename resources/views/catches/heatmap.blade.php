<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Fishing Grounds Heatmap</h2>
    </x-slot>
    <div class="py-6 max-w-7xl mx-auto space-y-6">
        <div class="bg-white p-4 rounded border shadow space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <p class="text-sm text-gray-600">Aggregated heatmap of ALL recorded catches (all species, all dates) within your visibility scope.</p>
                <div class="flex items-center gap-4 text-xs">
                    <label class="flex items-center gap-1">
                        <span class="font-medium w-12">Radius:</span>
                        <input id="radiusInput" type="range" min="10" max="50" value="22" class="align-middle w-24" />
                        <span id="radiusValue" class="w-6">22</span>
                    </label>
                    <label class="flex items-center gap-1">
                        <span class="font-medium w-12">Blur:</span>
                        <input id="blurInput" type="range" min="5" max="40" value="16" class="align-middle w-24" />
                        <span id="blurValue" class="w-6">16</span>
                    </label>
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
    </div>
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
                radiusInput.addEventListener('input', () => {
                    document.getElementById('radiusValue').textContent = radiusInput.value;
                    loadData();
                });
                blurInput.addEventListener('input', () => {
                    document.getElementById('blurValue').textContent = blurInput.value;
                    loadData();
                });
                // ensure proper sizing after layout
                requestAnimationFrame(()=>{ map.invalidateSize(); });
                setTimeout(()=>{ map.invalidateSize(); }, 400);
                loadData();
            } catch(e){
                if(loader){ loader.textContent = 'Map init failed'; }
            }
        }
    </script>
</x-app-layout>