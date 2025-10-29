<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catch Analytics</h2>
    </x-slot>
<div class="py-6 max-w-7xl mx-auto space-y-6">

    <div class="grid md:grid-cols-4 gap-4 text-sm">
        <div class="p-4 bg-white rounded border shadow">
            <div class="text-gray-500 text-xs uppercase">Total Catches</div>
            <div class="text-xl font-bold">{{ $totalSummary->catches }}</div>
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <div class="text-gray-500 text-xs uppercase">Total Quantity (kg)</div>
            <div class="text-xl font-bold">{{ number_format($totalSummary->total_qty,2) }}</div>
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <div class="text-gray-500 text-xs uppercase">Total Count (pcs)</div>
            <div class="text-xl font-bold">{{ $totalSummary->total_count }}</div>
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <div class="text-gray-500 text-xs uppercase">Avg Size (cm)</div>
            <div class="text-xl font-bold">{{ $totalSummary->avg_size ? number_format($totalSummary->avg_size,1) : '—' }}</div>
        </div>
    </div>

    <!-- Map Section - Full Width -->
   <div style="display: flex; justify-content: center;">
  <div class="bg-white rounded border shadow overflow-hidden" 
       style="width: 1000px; max-width: 100%; margin: 0 auto;">
    <div id="analyticsMap" style="width: 100%; height: 500px; min-height: 500px;"></div>
  </div>
</div>


    <div class="grid md:grid-cols-2 gap-6">
        <div class="p-4 bg-white rounded border shadow">
            <h2 class="font-semibold text-gray-700 text-sm mb-2">Top Species (by qty)</h2>
            <ul class="space-y-1 text-sm">
                @forelse($topSpecies as $row)
                    <li class="flex justify-between"><span>{{ $row->species?->common_name ?? 'Unknown' }}</span> <span class="text-gray-500">{{ number_format($row->qty_sum,2) }} kg</span></li>
                @empty
                    <li class="text-gray-400 italic">No data</li>
                @endforelse
            </ul>
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-700 text-sm mb-2">Gear Breakdown</h2>
                <div class="text-xs"><a href="?format=csv&series=gear" class="text-sky-600">Download CSV (Gear by species)</a></div>
            </div>
            <div class="table-container">
                <table class="w-full text-xs">
                    <thead><tr class="text-left text-gray-500"><th class="py-1">Gear Type</th><th class="py-1">Qty (kg)</th><th class="py-1">Catches</th></tr></thead>
                    <tbody>
                    @forelse($gearBreakdown as $g)
                        <tr class="border-t cursor-pointer hover:bg-gray-50 gear-row" data-gear="{{ $loop->index }}">
                            <td class="py-1 font-medium">{{ $g->gear_type }}</td>
                            <td class="py-1">{{ number_format($g->qty,2) }}</td>
                            <td class="py-1">{{ $g->catches }}</td>
                        </tr>
                        @php
                            $rows = $gearSpecies[$g->gear_type] ?? collect();
                            $top = $rows->sortByDesc('qty')->take(5);
                        @endphp
                        @if($top->isNotEmpty())
                            <tr class="gear-details hidden bg-gray-50" data-gear="{{ $loop->index }}">
                                <td colspan="3" class="py-2 pl-4">
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <div class="font-medium text-gray-700 mb-1">Top species:</div>
                                        @foreach($top as $r)
                                            <div>• {{ $r->species?->common_name ?? $r->species_id }}: {{ number_format($r->qty,2) }} kg</div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="3" class="text-gray-400 italic py-2">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="p-4 bg-white rounded border shadow">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-700 text-sm mb-2">Daily (last 14 days)</h2>
                <div class="text-xs"><a href="?format=csv&series=daily&separated=species" class="text-sky-600">Download CSV (Daily by species)</a></div>
            </div>
            <div class="table-container">
                <table class="w-full text-xs">
                    <thead><tr class="text-left text-gray-500"><th class="py-1">Date</th><th class="py-1">Qty (kg)</th><th class="py-1">Count</th></tr></thead>
                    <tbody>
                    @forelse($dailySeries as $d)
                        <tr class="border-t"><td class="py-1">{{ $d->d }}</td><td class="py-1">{{ number_format($d->qty,2) }}</td><td class="py-1">{{ $d->catch_count }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-gray-400 italic py-2">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if(!empty($dailyBySpecies) && $dailyBySpecies->isNotEmpty())
                <div class="mt-4 text-xs">
                    <div class="text-gray-600 font-medium mb-2">Daily by species</div>
                    <div class="table-container">
                        <table class="w-full text-xs">
                            <thead><tr class="text-left text-gray-500"><th class="py-1">Date</th><th class="py-1">Species</th><th class="py-1">Qty</th><th class="py-1">Count</th></tr></thead>
                            <tbody>
                            @foreach($dailyBySpecies as $date => $rows)
                                @foreach($rows as $r)
                                    <tr class="border-t"><td class="py-1">{{ $date }}</td><td class="py-1">{{ $r->species?->common_name ?? $r->species_id }}</td><td class="py-1">{{ number_format($r->qty,2) }}</td><td class="py-1">{{ $r->catch_count }}</td></tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-700 text-sm mb-2">Monthly (last 6)</h2>
                <div class="text-xs"><a href="?format=csv&series=monthly&separated=species" class="text-sky-600">Download CSV (Monthly by species)</a></div>
            </div>
            <div class="table-container">
                <table class="w-full text-xs">
                    <thead><tr class="text-left text-gray-500"><th class="py-1">Month</th><th class="py-1">Qty (kg)</th><th class="py-1">Count</th><th class="py-1">Species breakdown</th></tr></thead>
                    <tbody>
                    @forelse($monthlySeries as $m)
                        <tr class="border-t">
                            <td class="py-1">{{ $m->ym }}</td>
                            <td class="py-1">{{ number_format($m->qty,2) }}</td>
                            <td class="py-1">{{ $m->catch_count }}</td>
                            <td class="py-1 text-xs text-gray-600">
                                @php
                                    $rows = $monthlyBySpecies[$m->ym] ?? collect();
                                    $top = $rows->sortByDesc('qty')->take(3);
                                @endphp
                                @if($top->isEmpty())
                                    <span class="text-gray-400">—</span>
                                @else
                                    @foreach($top as $r)
                                        <div>{{ $r->species?->common_name ?? $r->species_id }}: {{ number_format($r->qty,2) }} kg</div>
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-gray-400 italic py-2">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if(!empty($monthlyBySpecies) && $monthlyBySpecies->isNotEmpty())
                <div class="mt-4 text-xs">
                    <div class="text-gray-600 font-medium mb-2">Monthly by species</div>
                    <div class="table-container">
                        <table class="w-full text-xs">
                            <thead><tr class="text-left text-gray-500"><th class="py-1">Period</th><th class="py-1">Species</th><th class="py-1">Qty</th><th class="py-1">Count</th></tr></thead>
                            <tbody>
                            @foreach($monthlyBySpecies as $period => $rows)
                                @foreach($rows as $r)
                                    <tr class="border-t"><td class="py-1">{{ $period }}</td><td class="py-1">{{ $r->species?->common_name ?? $r->species_id }}</td><td class="py-1">{{ number_format($r->qty,2) }}</td><td class="py-1">{{ $r->catch_count }}</td></tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="p-4 bg-white rounded border shadow">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-700 text-sm mb-2">Annual</h2>
            <div class="text-xs space-x-3">
                <a href="?format=csv&series=annual" class="text-sky-600">Download CSV</a>
                <a href="?format=csv&series=annual&separated=species" class="text-sky-600">Download CSV (Annual by species)</a>
            </div>
        </div>
        <table class="w-full text-xs">
            <thead><tr class="text-left text-gray-500"><th class="py-1">Year</th><th class="py-1">Qty (kg)</th><th class="py-1">Count</th></tr></thead>
            <tbody>
            @forelse($annualSeries as $y)
                <tr class="border-t">
                    <td class="py-1">{{ $y->y }}</td>
                    <td class="py-1">{{ number_format($y->qty,2) }}</td>
                    <td class="py-1">{{ $y->catch_count }}</td>
                    <td class="py-1 text-xs text-gray-600">
                        @php
                            $rows = $annualBySpecies[$y->y] ?? collect();
                            $top = $rows->sortByDesc('qty')->take(3);
                        @endphp
                        @if($top->isEmpty())
                            <span class="text-gray-400">—</span>
                        @else
                            @foreach($top as $r)
                                <div>{{ $r->species?->common_name ?? $r->species_id }}: {{ number_format($r->qty,2) }} kg</div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-gray-400 italic py-2">No data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #analyticsMap {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 100%;
        min-height: 400px;
    }
    
    .leaflet-container {
        background: #e5e7eb;
    }

    /* Scrollable table containers */
    .table-container {
        max-height: 400px;
        overflow-y: auto;
        border-radius: 0.25rem;
    }

    .table-container table {
        width: 100%;
    }

    /* Sticky table headers */
    .table-container thead {
        position: sticky;
        top: 0;
        background-color: #f9fafb;
        z-index: 10;
    }

    /* Zone Modal Styles */
    #zoneModal {
        display: none;
        position: fixed;
        z-index: 50;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    #zoneModal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .zone-modal-content {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }
</style>

<!-- Zone Detail Modal -->
<div id="zoneModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="zone-modal-content bg-white rounded-lg shadow-lg w-full max-w-md">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 id="modalZoneName" class="text-lg font-bold text-gray-900"></h3>
            <button onclick="closeZoneModal()" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
        </div>

        <div class="p-6 space-y-4">
            <!-- Search Input -->
            <div>
                <label for="zoneSpeciesSearch" class="block text-sm font-semibold mb-2 text-gray-700">Search Fish Species</label>
                <input
                    type="text"
                    id="zoneSpeciesSearch"
                    placeholder="Search by name..."
                    class="w-full px-3 py-2 border border-gray-300 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
            </div>

            <!-- Species Display as Chips -->
            <div id="modalSpeciesList" class="p-3 bg-gray-50 rounded-lg min-h-[60px]">
                <!-- Species chips will be populated here -->
            </div>

            <div class="text-xs text-gray-600 pt-2 border-t">
                <p>Fish species found in this zone based on catch records</p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Leaflet popup styling */
    .zone-popup {
        font-family: inherit;
    }
    
    .zone-popup .leaflet-popup-content {
        margin: 0;
        padding: 0;
        width: auto !important;
    }
    
    .zone-popup-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        font-size: 14px;
    }
    
    .zone-popup-content {
        padding: 12px;
        max-width: 300px;
    }
    
    .zone-popup-search {
        margin-bottom: 12px;
    }
    
    .zone-popup-search input {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .zone-species-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    
    .zone-species-chip {
        display: inline-block;
        padding: 4px 8px;
        background-color: #f3f4f6;
        border-radius: 4px;
        font-size: 12px;
        color: #374151;
    }
    
    .zone-popup-footer {
        padding: 8px 12px;
        border-top: 1px solid #e5e7eb;
        font-size: 11px;
        color: #6b7280;
    }
</style>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

<script>
    let zoneLayers = [];
    let map;

    function initAnalyticsMap() {
        if (typeof L === 'undefined') {
            setTimeout(initAnalyticsMap, 100);
            return;
        }

        const mapContainer = document.getElementById('analyticsMap');
        if (!mapContainer) return;

        try {
            map = L.map('analyticsMap').setView([18.3589, 121.8336], 10);

            // Ensure map takes full container size
            setTimeout(() => map.invalidateSize(), 100);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            // Fetch zones data via API
            fetch('/api/zones/data')
                .then(resp => resp.json())
                .then(data => {
                    if (!data || !data.zones || data.zones.length === 0) {
                        console.log('No zones to display');
                        return;
                    }

                    // Add zones to map
                    data.zones.forEach(zone => {
                        if (!zone.geometry) {
                            console.warn('Zone has no geometry:', zone.id);
                            return;
                        }

                        const color = zone.color || '#00FF00';
                        const geom = typeof zone.geometry === 'string' ? JSON.parse(zone.geometry) : zone.geometry;
                        
                        // Build species list for popup
                        let speciesList = '';
                        if (zone.species && zone.species.length > 0) {
                            speciesList = '<div class="mt-2 text-sm"><strong>Fish Species:</strong><ul class="list-disc list-inside">';
                            zone.species.forEach(sp => {
                                speciesList += `<li>${sp.name}: ${sp.qty.toFixed(1)} kg (${sp.catches} catch${sp.catches !== 1 ? 'es' : ''})</li>`;
                            });
                            speciesList += '</ul></div>';
                        }
                        
                        L.geoJSON(geom, {
                            style: {
                                color: color,
                                weight: 2,
                                opacity: 0.7,
                                fillOpacity: 0.2
                            },
                            onEachFeature: function(feature, layer) {
                                // Store zone data on layer for overlap detection
                                layer.zoneData = zone;
                                layer.zoneName = zone.name;
                                layer.zoneColor = color;
                                layer.zoneSpecies = (zone.species || []).map(s => s.name);
                                
                                layer.on('click', function(e) {
                                    showZonePopup(layer, zone);
                                });
                                let popupText = `<strong>${zone.name}</strong><br/><small class="text-gray-600">Click to view details</small>`;
                                layer.bindPopup(popupText);
                                
                                // Store in zoneLayers array for overlap detection
                                zoneLayers.push(layer);
                            }
                        }).addTo(map);
                    });

                    console.log('Zones loaded: ' + data.zones.length);
                    
                    // Detect overlaps after all zones are added
                    detectOverlaps();
                })
                .catch(err => console.error('Error loading zones:', err));

            // Fetch catch points
            fetch('/catches/heatmap/data')
                .then(resp => resp.json())
                .then(data => {
                    if (!data || !data.points || data.points.length === 0) {
                        console.log('No catches to display');
                        return;
                    }

                    // Add markers for recent catches
                    data.points.slice(0, 50).forEach(point => {
                        const lat = point.lat;
                        const lng = point.lng;
                        const weight = point.weight;
                        const species = point.species;
                        const zones = point.zones || [];
                        const catchPoint = turf.point([lng, lat]);
                        
                        // Check if catch is actually within the zones it claims to be in
                        // AND check if the species is found in those zones
                        let isInCorrectZone = false;
                        let invalidZones = [];
                        let wrongSpeciesZones = [];
                        
                        zones.forEach(zone => {
                            const zoneLayer = zoneLayers.find(layer => layer.zoneName === zone.name);
                            if (zoneLayer) {
                                const geoJsonZone = zoneLayer.toGeoJSON();
                                let zoneGeom = geoJsonZone.type === 'FeatureCollection' ? geoJsonZone.features[0] : geoJsonZone;
                                if (zoneGeom.type === 'Feature') zoneGeom = zoneGeom.geometry;
                                
                                try {
                                    const isInside = turf.booleanPointInPolygon(catchPoint, zoneGeom);
                                    
                                    // Check if species exists in this zone
                                    const speciesInZone = zoneLayer.zoneSpecies.some(s => 
                                        s.toLowerCase() === species.toLowerCase()
                                    );
                                    
                                    if (isInside && speciesInZone) {
                                        isInCorrectZone = true;
                                    } else if (!isInside) {
                                        invalidZones.push(zone.name);
                                    } else if (!speciesInZone) {
                                        wrongSpeciesZones.push(zone.name);
                                    }
                                } catch (e) {
                                    console.log('Error checking zone geometry:', e);
                                }
                            }
                        });
                        
                        const size = Math.min(Math.max(weight / 2, 5), 15);
                        const hasIssues = (zones.length > 0 && (!isInCorrectZone || wrongSpeciesZones.length > 0));
                        const markerColor = hasIssues ? '#ef4444' : '#3b82f6';
                        const markerBorder = hasIssues ? '#dc2626' : '#1e40af';
                        
                        const marker = L.circleMarker([lat, lng], {
                            radius: size,
                            fillColor: markerColor,
                            color: markerBorder,
                            weight: hasIssues ? 2 : 1,
                            opacity: 0.7,
                            fillOpacity: 0.6
                        }).addTo(map);

                        let popupHtml = `<strong>${species}</strong><br/>Weight: ${weight} kg`;
                        if (zones.length > 0) {
                            popupHtml += '<br/><div class="mt-2 text-sm"><strong>In zones:</strong><ul class="list-disc list-inside">';
                            zones.forEach(zone => {
                                let statusIcon = '✓';
                                let statusClass = 'text-green-600';
                                let warning = '';
                                
                                if (invalidZones.includes(zone.name)) {
                                    statusIcon = '✗';
                                    statusClass = 'text-red-600';
                                    warning = ' <span class="text-xs">(location outside zone)</span>';
                                } else if (wrongSpeciesZones.includes(zone.name)) {
                                    statusIcon = '⚠️';
                                    statusClass = 'text-orange-600';
                                    warning = ' <span class="text-xs">(species not in zone)</span>';
                                }
                                
                                popupHtml += `<li class="${statusClass}">${statusIcon} ${zone.name}${warning}</li>`;
                            });
                            popupHtml += '</ul></div>';
                            
                            if (invalidZones.length > 0 || wrongSpeciesZones.length > 0) {
                                if (invalidZones.length > 0 && wrongSpeciesZones.length > 0) {
                                    popupHtml += `<div class="mt-2 text-sm text-red-600 font-bold">⚠️ Location & species mismatch!</div>`;
                                } else if (invalidZones.length > 0) {
                                    popupHtml += `<div class="mt-2 text-sm text-red-600 font-bold">⚠️ Catch location outside zone!</div>`;
                                } else if (wrongSpeciesZones.length > 0) {
                                    popupHtml += `<div class="mt-2 text-sm text-orange-600 font-bold">⚠️ Species not found in zone!</div>`;
                                }
                            }
                        } else {
                            popupHtml += '<br/><div class="mt-2 text-sm"><em>Not in any zone</em></div>';
                        }

                        marker.bindPopup(popupHtml);
                    });

                    console.log('Catches loaded: ' + Math.min(data.points.length, 50));
                })
                .catch(err => console.error('Error loading catches:', err));

        } catch (e) {
            console.error('Map initialization error:', e);
        }
    }

    // Initialize map when DOM is ready
    document.addEventListener('DOMContentLoaded', initAnalyticsMap);

    // Gear breakdown toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.gear-row').forEach(row => {
            row.addEventListener('click', function(e) {
                e.preventDefault();
                const gearIndex = this.dataset.gear;
                const details = document.querySelector(`.gear-details[data-gear="${gearIndex}"]`);

                if (details) {
                    details.classList.toggle('hidden');
                }
            });
        });
    });

    // Detect and handle overlapping zones
    function detectOverlaps() {
        const overlaps = [];
        
        for (let i = 0; i < zoneLayers.length; i++) {
            for (let j = i + 1; j < zoneLayers.length; j++) {
                const layer1 = zoneLayers[i];
                const layer2 = zoneLayers[j];
                
                try {
                    // Get GeoJSON for both layers
                    const geojson1 = layer1.toGeoJSON();
                    const geojson2 = layer2.toGeoJSON();
                    
                    // Extract the actual geometry (handle FeatureCollection or Feature)
                    let poly1 = geojson1.type === 'FeatureCollection' ? geojson1.features[0] : geojson1;
                    let poly2 = geojson2.type === 'FeatureCollection' ? geojson2.features[0] : geojson2;
                    
                    // If it's a Feature, get the geometry
                    if (poly1.type === 'Feature') poly1 = poly1.geometry;
                    if (poly2.type === 'Feature') poly2 = poly2.geometry;
                    
                    // Check intersection using turf.js
                    const intersection = turf.intersect(
                        turf.feature(poly1),
                        turf.feature(poly2)
                    );
                    
                    if (intersection) {
                        // Combine species from both zones
                        const combinedSpecies = [...new Set([...layer1.zoneSpecies, ...layer2.zoneSpecies])];
                        
                        // Add intersection layer
                        const intersectionLayer = L.geoJSON(intersection, {
                            style: {
                                color: '#FF6B00',
                                weight: 3,
                                fillOpacity: 0.5,
                                fillColor: '#FFB84D',
                                dashArray: '5, 5'
                            }
                        }).addTo(map);
                        
                        // Create overlap popup
                        let speciesHtml = '';
                        if (combinedSpecies.length > 0) {
                            speciesHtml = '<div class="mt-2"><strong>Fish Species Found:</strong><br>' + 
                                combinedSpecies.map(s => `<span class="inline-block px-2 py-1 bg-blue-100 rounded text-xs mr-1 mb-1">${s}</span>`).join('') +
                                '</div>';
                        }
                        
                        intersectionLayer.bindPopup(`
                            <div class="p-2 max-w-xs">
                                <div class="font-bold text-lg mb-2 text-orange-600">⚠️ Overlapping Zones</div>
                                <div class="space-y-1 mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded" style="background-color: ${layer1.zoneColor}"></div>
                                        <span class="text-sm font-medium">${layer1.zoneName}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded" style="background-color: ${layer2.zoneColor}"></div>
                                        <span class="text-sm font-medium">${layer2.zoneName}</span>
                                    </div>
                                </div>
                                ${speciesHtml}
                            </div>
                        `);
                        
                        overlaps.push({
                            zone1: layer1.zoneName,
                            zone2: layer2.zoneName,
                            species: combinedSpecies
                        });
                    }
                } catch (e) {
                    console.log('Error detecting overlap:', e);
                }
            }
        }
        
        if (overlaps.length > 0) {
            console.log('Overlapping zones detected:', overlaps);
        }
    }

    // Zone Popup Functions - Leaflet popup on map
    function showZonePopup(layer, zone) {
        console.log('Opening popup for zone:', zone);

        if (!zone.species || zone.species.length === 0) {
            console.log('No species data for zone:', zone);
            layer.setPopupContent(`
                <div class="zone-popup">
                    <div class="zone-popup-header">
                        <div style="width: 12px; height: 12px; background-color: ${zone.color}; border-radius: 2px;"></div>
                        <span>${zone.name}</span>
                    </div>
                    <div class="zone-popup-content">
                        <div class="text-center text-gray-500 py-4">No species data available for this zone</div>
                    </div>
                </div>
            `);
            layer.openPopup();
            return;
        }

        console.log('Species found:', zone.species);

        // Build species chips HTML
        let chipsHtml = zone.species.map(sp => 
            `<span class="zone-species-chip" data-common-name="${sp.name.toLowerCase()}">${sp.name}</span>`
        ).join('');

        // Build popup content
        const popupContent = `
            <div class="zone-popup">
                <div class="zone-popup-header">
                    <div style="width: 12px; height: 12px; background-color: ${zone.color}; border-radius: 2px;"></div>
                    <span>${zone.name}</span>
                </div>
                <div class="zone-popup-content">
                    <div class="zone-popup-search">
                        <input type="text" placeholder="Search species..." class="zone-popup-search-input" onkeyup="filterZoneSpecies(this)">
                    </div>
                    <div class="zone-species-chips" id="popupChips">
                        ${chipsHtml}
                    </div>
                </div>
                <div class="zone-popup-footer">
                    Fish species found in this zone based on catch records
                </div>
            </div>
        `;

        layer.setPopupContent(popupContent);
        layer.openPopup();
    }

    function filterZoneSpecies(searchInput) {
        const searchTerm = searchInput.value.toLowerCase();
        const chipsContainer = searchInput.closest('.zone-popup-content').querySelector('#popupChips');
        const chips = chipsContainer.querySelectorAll('.zone-species-chip');

        chips.forEach(chip => {
            const commonName = chip.dataset.commonName || '';
            const matches = commonName.includes(searchTerm) || searchTerm === '';
            chip.style.display = matches ? '' : 'none';
        });
    }

    function closeZoneModal() {
        // No longer used - closing is handled by Leaflet
    }
</script>
</x-app-layout>
