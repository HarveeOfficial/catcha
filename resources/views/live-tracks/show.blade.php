
<x-app-layout>
    <x-slot name="header">
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6">
        <div class="bg-white p-4 rounded border shadow space-y-4">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="text-sm text-gray-600">Public track: <span class="font-mono">{{ $track->public_id }}</span></div>
                <div class="flex items-center gap-2 text-xs">
                    <button id="fitBtn" class="px-3 py-2 bg-indigo-600 text-white rounded">Fit All</button>
                    <button id="toggleSatellite" class="px-3 py-2 bg-blue-600 text-white rounded">Satellite View</button>
                </div>
            </div>
            <div id="liveTrackCard" class="w-full h-[520px] rounded border border-gray-300 overflow-hidden relative">
                <div id="liveTrackLoader" class="absolute inset-0 flex items-center justify-center text-xs text-gray-500">Loading map…</div>
                <div id="liveTrackStatus" class="absolute top-2 left-2 z-[1000] bg-gray-900/90 text-white text-xs px-2 py-1 rounded">Loading…</div>
                <div id="liveLegend" class="absolute top-2 right-2 z-[1000] bg-white/90 text-xs px-2 py-1 rounded shadow max-h-56 overflow-auto hidden"></div>
            </div>
        </div>
    </div>

    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            #liveTrackCard { min-height:520px; }
            .leaflet-container { font: inherit; z-index:0; }
        </style>
        <script id="leaflet-core" src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            // Fallback loader if CDN blocked
            window.addEventListener('error', function(e){
                if(e.target && e.target.id === 'leaflet-core'){
                    const alt = document.createElement('script');
                    alt.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js';
                    alt.onload = tryInitLiveMap;
                    document.head.appendChild(alt);
                }
            }, true);
        </script>
    @endonce

    <script>
        function tryInitLiveMap(){
            if(typeof L === 'undefined'){ return; }
            if(!document.getElementById('liveTrackCard')){ return; }
            if(!window.__liveMapInitDone){
                window.__liveMapInitDone = true;
                initLiveMap();
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            // Delay to ensure CSS applied and container has dimensions
            setTimeout(tryInitLiveMap, 150);
            setTimeout(tryInitLiveMap, 600);
        });

        function initLiveMap(){
            const container = document.getElementById('liveTrackCard');
            const loader = document.getElementById('liveTrackLoader');
            const status = document.getElementById('liveTrackStatus');
            const fitBtn = document.getElementById('fitBtn');
            const toggleSatelliteBtn = document.getElementById('toggleSatellite');
            if(!container){ return; }

            const map = L.map('liveTrackCard');
            map.setView([14.5995, 120.9842], 6);
            const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'&copy; OSM contributors' });
            osmLayer.addTo(map);
            const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles © Esri'
            });
            let isSatellite = false;
            toggleSatelliteBtn.addEventListener('click', function() {
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

            // Render all active tracks from server snapshot (no API calls)
            const activeTracks = @json($activeTracks ?? []);
            const colors = ['#1d4ed8','#059669','#dc2626','#a855f7','#ea580c','#0ea5e9','#16a34a','#eab308','#f43f5e','#10b981'];
            const layers = [];
            let allBounds = null;
            const legend = document.getElementById('liveLegend');
            if (Array.isArray(activeTracks) && activeTracks.length) {
                const list = document.createElement('div');
                list.className = 'space-y-1';
                activeTracks.forEach((t, idx) => {
                    const latlngs = (t.points || []).map(p => [p.lat, p.lng]);
                    if (!latlngs.length) { return; }
                    const color = colors[idx % colors.length];
                    const poly = L.polyline(latlngs, { color, weight: 4 }).addTo(map);
                    const last = latlngs[latlngs.length - 1];
                    const marker = L.circleMarker(last, { radius: 6, color, fillColor: color, fillOpacity: 1 }).addTo(map);
                    marker.bindTooltip((t.user?.name || 'Unknown') + ` (${t.publicId})`, { permanent: false });
                    layers.push(poly, marker);
                    const b = poly.getBounds();
                    allBounds = allBounds ? allBounds.extend(b) : b;

                    const row = document.createElement('div');
                    row.className = 'flex items-center gap-2';
                    const sw = document.createElement('span');
                    sw.className = 'inline-block w-3 h-3 rounded';
                    sw.style.backgroundColor = color;
                    const label = document.createElement('span');
                    label.textContent = (t.user?.name || 'Unknown') + ` (${t.publicId})`;
                    row.appendChild(sw);
                    row.appendChild(label);
                    list.appendChild(row);
                });
                if (list.children.length) {
                    legend.appendChild(list);
                    legend.classList.remove('hidden');
                }
            }

            function fitAll() {
                if (allBounds) {
                    map.fitBounds(allBounds, { padding:[40,40], maxZoom: 17 });
                }
            }

            fitBtn.addEventListener('click', fitAll);

            // ensure sizing after layout
            requestAnimationFrame(()=>{ map.invalidateSize(); });
            setTimeout(()=>{ map.invalidateSize(); }, 400);

            // initial render
            if (activeTracks.length) {
                status.textContent = `Active: ${activeTracks.length} track(s)`;
                fitAll();
            } else {
                status.textContent = 'No active tracks';
            }
            if(loader){ loader.classList.add('hidden'); }
        }
    </script>
</x-app-layout>
