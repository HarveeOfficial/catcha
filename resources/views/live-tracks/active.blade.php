<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Active Live Tracks</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6">
        <div class="bg-white p-4 rounded border shadow space-y-4">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="text-sm text-gray-600">Showing all actively streaming users (snapshot)</div>
                <div class="flex items-center gap-2 text-xs">
                    <button id="fitBtn" class="px-3 py-2 bg-indigo-600 text-white rounded">Fit All</button>
                    <button id="toggleSatellite" class="px-3 py-2 bg-blue-600 text-white rounded">Satellite View</button>
                </div>
            </div>
            <div id="activeMapCard" class="w-full h-[520px] rounded border border-gray-300 overflow-hidden relative">
                <div id="activeMapLoader" class="absolute inset-0 flex items-center justify-center text-xs text-gray-500">Loading map…</div>
                <div id="activeMapStatus" class="absolute top-2 left-2 z-[1000] bg-gray-900/90 text-white text-xs px-2 py-1 rounded">Loading…</div>
                {{-- <div id="activeLegend" class="absolute top-2 right-2 z-[1000] bg-white/90 text-xs px-2 py-1 rounded shadow max-h-56 overflow-auto hidden"></div> --}}
            </div>
        </div>
    </div>

    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            #activeMapCard { min-height:520px; }
            .leaflet-container { font: inherit; z-index:0; }
        </style>
        <script id="leaflet-core" src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endonce

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(initActiveMap, 150);
            setTimeout(initActiveMap, 600);
        });

        function initActiveMap(){
            if(typeof L === 'undefined'){ return; }
            const container = document.getElementById('activeMapCard');
            if(!container){ return; }
            const loader = document.getElementById('activeMapLoader');
            const status = document.getElementById('activeMapStatus');
            const legend = document.getElementById('activeLegend');
            const fitBtn = document.getElementById('fitBtn');
            const toggleSatelliteBtn = document.getElementById('toggleSatellite');

            const map = L.map('activeMapCard');
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

            const activeTracks = @json($activeTracks ?? []);
            const colors = ['#1d4ed8','#059669','#dc2626','#a855f7','#ea580c','#0ea5e9','#16a34a','#eab308','#f43f5e','#10b981'];
            let allBounds = null;
            const trackLayers = new Map(); // publicId -> { poly, marker, color, latlngs, isActive }
            let rendered = 0;
            if (Array.isArray(activeTracks) && activeTracks.length) {
                const list = document.createElement('div');
                list.className = 'space-y-1';
                activeTracks.forEach((t, idx) => {
                    const latlngs = (t.points || []).map(p => [p.lat, p.lng]);
                    if (!latlngs.length) {
                        // Add a label row even if no points yet
                        const row = document.createElement('div');
                        row.className = 'flex items-center gap-2 opacity-60';
                        const sw = document.createElement('span');
                        sw.className = 'inline-block w-3 h-3 rounded bg-gray-400';
                        const label = document.createElement('span');
                        label.textContent = (t.user?.name || 'Unknown') + ` (${t.publicId})`;
                        row.appendChild(sw);
                        row.appendChild(label);
                        list.appendChild(row);
                        return;
                    }
                    const color = colors[idx % colors.length];
                    // Use solid line for active tracks, dashed line for inactive/ended tracks
                    const dashArray = t.isActive ? undefined : '5 5';
                    const poly = L.polyline(latlngs, { 
                        color, 
                        weight: t.isActive ? 4 : 3,
                        dashArray,
                        opacity: t.isActive ? 1 : 0.7
                    }).addTo(map);
                    const last = latlngs[latlngs.length - 1];
                    const markerRadius = t.isActive ? 6 : 4;
                    const marker = L.circleMarker(last, { radius: markerRadius, color, fillColor: color, fillOpacity: t.isActive ? 1 : 0.6 }).addTo(map);
                    const statusLabel = t.isActive ? '' : ' (ended)';
                    marker.bindTooltip((t.user?.name || 'Unknown') + ` (${t.publicId})${statusLabel}`, { permanent: false });
                    const b = poly.getBounds();
                    allBounds = allBounds ? allBounds.extend(b) : b;
                    rendered++;
                    trackLayers.set(t.publicId, { poly, marker, color, latlngs, isActive: t.isActive });

                    const row = document.createElement('div');
                    row.className = 'flex items-center gap-2';
                    const sw = document.createElement('span');
                    sw.className = 'inline-block w-3 h-3 rounded';
                    sw.style.backgroundColor = color;
                    const label = document.createElement('span');
                    const statusText = t.isActive ? '' : ' (ended)';
                    label.textContent = (t.user?.name || 'Unknown') + ` (${t.publicId})${statusText}`;
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

            requestAnimationFrame(()=>{ map.invalidateSize(); });
            setTimeout(()=>{ map.invalidateSize(); }, 400);

            if (activeTracks.length) {
                const activeCount = activeTracks.filter(t => t.isActive).length;
                const completedCount = activeTracks.length - activeCount;
                const statusStr = completedCount > 0 
                    ? `Active: ${activeCount} • Completed: ${completedCount}` 
                    : `Active: ${activeCount}`;
                status.textContent = statusStr + (rendered === 0 ? ' • no points yet' : '');
                if (rendered > 0) { fitAll(); }
            } else {
                status.textContent = 'No active tracks';
            }
            if(loader){ loader.classList.add('hidden'); }

            // Realtime polling for new points across all tracks
            const pollUrl = @json(route('live-tracks.active.points'));
            let lastTs = null;
            async function poll() {
                try {
                    const url = new URL(pollUrl, window.location.origin);
                    if (lastTs) { url.searchParams.set('since', lastTs); }
                    const res = await fetch(url.toString(), { headers: { 'Accept':'application/json' } });
                    if (!res.ok) { return; }
                    const data = await res.json();
                    const updates = data.tracks || [];
                    if (updates.length) {
                        updates.forEach(tu => {
                            const layer = trackLayers.get(tu.publicId);
                            const pts = (tu.points || []).map(p => [p.lat, p.lng]);
                            if (!layer) {
                                if (!pts.length) { return; }
                                const color = colors[(trackLayers.size) % colors.length];
                                // Use solid line for active tracks, dashed line for inactive/ended tracks
                                const dashArray = tu.isActive ? undefined : '5 5';
                                const poly = L.polyline(pts, { 
                                    color, 
                                    weight: tu.isActive ? 4 : 3,
                                    dashArray,
                                    opacity: tu.isActive ? 1 : 0.7
                                }).addTo(map);
                                const last = pts[pts.length - 1];
                                const markerRadius = tu.isActive ? 6 : 4;
                                const marker = L.circleMarker(last, { radius: markerRadius, color, fillColor: color, fillOpacity: tu.isActive ? 1 : 0.6 }).addTo(map);
                                const statusLabel = tu.isActive ? '' : ' (ended)';
                                marker.bindTooltip((tu.user?.name || 'Unknown') + ` (${tu.publicId})${statusLabel}`, { permanent: false });
                                trackLayers.set(tu.publicId, { poly, marker, color, latlngs: pts, isActive: tu.isActive });
                                const b = poly.getBounds();
                                allBounds = allBounds ? allBounds.extend(b) : b;
                                return;
                            }
                            if (!pts.length) { return; }
                            // Update polyline style if track status changed
                            if (layer.isActive !== tu.isActive) {
                                const dashArray = tu.isActive ? undefined : '5 5';
                                layer.poly.setStyle({ 
                                    dashArray,
                                    weight: tu.isActive ? 4 : 3,
                                    opacity: tu.isActive ? 1 : 0.7
                                });
                                layer.marker.setRadius(tu.isActive ? 6 : 4);
                                layer.marker.setStyle({ fillOpacity: tu.isActive ? 1 : 0.6 });
                                layer.isActive = tu.isActive;
                            }
                            layer.latlngs.push(...pts);
                            layer.poly.setLatLngs(layer.latlngs);
                            layer.marker.setLatLng(layer.latlngs[layer.latlngs.length - 1]);
                        });
                        const activeCount = Array.from(trackLayers.values()).filter(l => l.isActive).length;
                        const totalCount = trackLayers.size;
                        status.textContent = `Active: ${activeCount}/${totalCount} track(s) • Updated: ${new Date().toLocaleTimeString()}`;
                    }
                    lastTs = data.serverTime || lastTs;
                } catch (e) {
                    // ignore transient network errors
                }
            }
            setInterval(poll, 5000);
        }
    </script>
</x-app-layout>
