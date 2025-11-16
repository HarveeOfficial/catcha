<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catch Analytics</h2>
    </x-slot>

<div class="py-6 max-w-7xl mx-auto space-y-6">

    <!-- Summary Report Section -->
    <div class="bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">📊 Catch Analytics Report</h2>
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Catches -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-5 border border-blue-200 hover:shadow-md transition">
                <p class="text-blue-700 text-xs font-semibold uppercase tracking-wide mb-2">Total Catches</p>
                <p class="text-4xl font-bold text-blue-900">{{ $totalSummary->catches }}</p>
                <p class="text-blue-600 text-xs mt-1">recorded</p>
            </div>
            
            <!-- Total Quantity -->
            <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-lg p-5 border border-cyan-200 hover:shadow-md transition">
                <p class="text-cyan-700 text-xs font-semibold uppercase tracking-wide mb-2">Total Quantity</p>
                <p class="text-4xl font-bold text-cyan-900">{{ number_format($totalSummary->total_qty, 2) }} <span class="text-lg">kg</span></p>
                <p class="text-cyan-600 text-xs mt-1">total weight</p>
            </div>
            
            <!-- Total Count -->
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg p-5 border border-emerald-200 hover:shadow-md transition">
                <p class="text-emerald-700 text-xs font-semibold uppercase tracking-wide mb-2">Fish Count</p>
                <p class="text-4xl font-bold text-emerald-900">{{ $totalSummary->total_count }} <span class="text-lg">pcs</span></p>
                <p class="text-emerald-600 text-xs mt-1">individual fish</p>
            </div>
            
            <!-- Average Size -->
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-5 border border-amber-200 hover:shadow-md transition">
                <p class="text-amber-700 text-xs font-semibold uppercase tracking-wide mb-2">Average Size</p>
                <p class="text-4xl font-bold text-amber-900">{{ $totalSummary->avg_size ? number_format($totalSummary->avg_size, 1) : '—' }} <span class="text-lg">cm</span></p>
                <p class="text-amber-600 text-xs mt-1">per fish</p>
            </div>
        </div>
        
        <!-- Insights Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Top Species -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-300">
                <p class="text-blue-800 text-xs font-semibold uppercase tracking-wide mb-3">🎯 Top Species</p>
                @if($topSpecies && $topSpecies->count() > 0)
                    <p class="text-gray-900 font-semibold text-sm">{{ $topSpecies->first()?->species?->common_name ?? 'Unknown' }}</p>
                    <p class="text-blue-700 text-xs mt-1">{{ number_format($topSpecies->first()?->qty_sum, 2) }} kg</p>
                @else
                    <p class="text-gray-500 italic text-sm">No data</p>
                @endif
            </div>
            
            <!-- Total Zones -->
            <div class="bg-emerald-50 rounded-lg p-4 border border-emerald-300">
                <p class="text-emerald-800 text-xs font-semibold uppercase tracking-wide mb-3">📍 Active Zones</p>
                <p class="text-gray-900 font-semibold text-3xl">{{ $zoneBreakdown ? $zoneBreakdown->count() : 0 }}</p>
                <p class="text-emerald-700 text-xs mt-1">zone{{ $zoneBreakdown && $zoneBreakdown->count() !== 1 ? 's' : '' }} with catches</p>
            </div>
            
            <!-- Gear Types -->
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-300">
                <p class="text-purple-800 text-xs font-semibold uppercase tracking-wide mb-3">🎣 Gear Types</p>
                <p class="text-gray-900 font-semibold text-3xl">{{ $gearBreakdown ? $gearBreakdown->count() : 0 }}</p>
                <p class="text-purple-700 text-xs mt-1">type{{ $gearBreakdown && $gearBreakdown->count() !== 1 ? 's' : '' }} in use</p>
            </div>
            
            <!-- Average Weight -->
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-300">
                <p class="text-orange-800 text-xs font-semibold uppercase tracking-wide mb-3">⚖️ Avg per Catch</p>
                <p class="text-gray-900 font-semibold text-3xl">{{ $totalSummary->catches > 0 ? number_format($totalSummary->total_qty / $totalSummary->catches, 2) : 0 }}</p>
                <p class="text-orange-700 text-xs mt-1">kg per catch</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid md:grid-cols-3 gap-6">
        <!-- Species Breakdown Pie Chart -->
        <div class="p-4 bg-white rounded border shadow">
            <h2 class="font-semibold text-gray-700 text-sm mb-4">Species Breakdown</h2>
            <canvas id="speciesChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Gear Breakdown Pie Chart -->
        <div class="p-4 bg-white rounded border shadow">
            <h2 class="font-semibold text-gray-700 text-sm mb-4">Gear Breakdown</h2>
            <canvas id="gearChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Zone Breakdown Pie Chart -->
        <div class="p-4 bg-white rounded border shadow">
            <h2 class="font-semibold text-gray-700 text-sm mb-4">Zone Breakdown</h2>
            @if($zoneBreakdown && $zoneBreakdown->count() > 0)
                <canvas id="zoneChart" style="max-height: 300px;"></canvas>
            @else
                <div class="flex items-center justify-center h-64 text-gray-400">
                    <p>No zone data available</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Map Section - Full Width -->
   <div style="display: flex; justify-content: center;">
  <div class="bg-white rounded border shadow overflow-hidden"
       style="width: 1000px; max-width: 100%; margin: 0 auto;">
    <div id="analyticsMap" style="width: 100%; height: 500px; min-height: 500px;"></div>
    <!-- Zone Legend -->
    <div class="p-4 border-t border-gray-200">
      <h3 class="text-sm font-semibold text-gray-700 mb-3">Zones</h3>
      <div id="zoneLegend" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 text-sm">
        <!-- Legend items will be populated here -->
      </div>
    </div>
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
                <button onclick="openCsvExportModal('gear')" class="text-xs text-sky-600 hover:text-sky-700">Download CSV</button>
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

        <!-- Zone Breakdown Table -->
        <div class="p-4 bg-white rounded border shadow">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-700 text-sm mb-2">Zone Breakdown</h2>
                <button onclick="openCsvExportModal('zone')" class="text-xs text-sky-600 hover:text-sky-700">Download CSV</button>
            </div>
            <div class="table-container">
                <table class="w-full text-xs">
                    <thead><tr class="text-left text-gray-500"><th class="py-1">Zone</th><th class="py-1">Qty (kg)</th><th class="py-1">Catches</th></tr></thead>
                    <tbody>
                    @forelse($zoneBreakdown as $z)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="py-1 font-medium">{{ $z->zone?->name ?? 'Unknown' }}</td>
                            <td class="py-1">{{ number_format($z->qty, 2) }}</td>
                            <td class="py-1">{{ $z->catches }}</td>
                        </tr>
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
                <h2 class="font-semibold text-gray-700 text-sm mb-2">Daily (Today)</h2>
                {{-- <button onclick="openCsvExportModal('daily')" class="text-xs text-sky-600 hover:text-sky-700">Download CSV</button> --}}
            </div>
            <div class="table-container">
                <table class="w-full text-xs">
                    <thead><tr class="text-left text-gray-500"><th class="py-1">Date</th><th class="py-1">Qty (kg)</th><th class="py-1">Count</th></tr></thead>
                    <tbody>
                    @forelse($dailySeries->sortByDesc('d') as $d)
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
                            @foreach($dailyBySpecies->sortByDesc(fn($group) => $group->first()->d) as $date => $rows)
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
                <button onclick="openCsvExportModal('monthly')" class="text-xs text-sky-600 hover:text-sky-700">Download CSV</button>
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
            {{-- @if(!empty($monthlyBySpecies) && $monthlyBySpecies->isNotEmpty())
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
            @endif --}}
        </div>
    </div>
    <div class="p-4 bg-white rounded border shadow">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-700 text-sm mb-2">Annual</h2>
            <button onclick="openCsvExportModal('annual')" class="text-xs text-sky-600 hover:text-sky-700">Download CSV</button>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

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
                        return;
                    }

                    // Add zones to map
                    data.zones.forEach(zone => {
                        if (!zone.geometry) {
                            return;
                        }

                        const color = zone.color || '#00FF00';
                        const geom = typeof zone.geometry === 'string' ? JSON.parse(zone.geometry) : zone.geometry;
                        
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

                    // Update legend after zones are loaded
                    updateZoneLegend(data.zones);
                    
                    // Detect overlaps after all zones are added
                    detectOverlaps();
                })
                .catch(err => {});

            // Fetch catch points
            fetch('/catches/heatmap/data')
                .then(resp => resp.json())
                .then(data => {
                    if (!data || !data.points || data.points.length === 0) {
                        return;
                    }

                    // Add markers for recent catches
                    data.points.slice(0, 500).forEach(point => {
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
                                    // Error silently
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

                })
                .catch(err => {});

        } catch (e) {
            // Error silently
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
                    // Error silently
                }
            }
        }
    }

    // Zone Popup Functions - Leaflet popup on map
    function showZonePopup(layer, zone) {
        if (!zone.species || zone.species.length === 0) {
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

    function updateZoneLegend(zones) {
        const legendContainer = document.getElementById('zoneLegend');
        if (!legendContainer) return;

        legendContainer.innerHTML = zones.map(zone => `
            <div class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer transition" 
                 title="${zone.name}"
                 onclick="zoomToZone('${zone.name}')">
                <div class="w-4 h-4 rounded" style="background-color: ${zone.color}; border: 2px solid ${zone.color}; flex-shrink: 0;"></div>
                <span class="text-gray-700 truncate text-xs font-medium">${zone.name}</span>
            </div>
        `).join('');
    }

    function zoomToZone(zoneName) {
        const targetLayer = zoneLayers.find(layer => layer.zoneName === zoneName);
        if (targetLayer && map) {
            const bounds = targetLayer.getBounds();
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    // Chart colors matching the design
    const chartColors = {
        species: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],
        gear: ['#3b82f6', '#06b6d4', '#6366f1', '#84cc16', '#f59e0b', '#ef4444'],
        zone: ['#3b82f6', '#06b6d4', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#10b981', '#f59e0b']
    };

    // Initialize charts on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
    });

    function initializeCharts() {
        // Species Chart
        @if($topSpecies && $topSpecies->count() > 0)
            const speciesCtx = document.getElementById('speciesChart');
            if (speciesCtx) {
                new Chart(speciesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            @foreach($topSpecies as $row)
                                '{{ $row->species?->common_name ?? 'Unknown' }}',
                            @endforeach
                        ],
                        datasets: [{
                            data: [
                                @foreach($topSpecies as $row)
                                    {{ $row->qty_sum }},
                                @endforeach
                            ],
                            backgroundColor: chartColors.species,
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: { size: 11 },
                                    padding: 8,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + ' kg';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        @endif

        // Gear Chart
        @if($gearBreakdown && $gearBreakdown->count() > 0)
            const gearCtx = document.getElementById('gearChart');
            if (gearCtx) {
                new Chart(gearCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            @foreach($gearBreakdown as $g)
                                '{{ $g->gear_type }}',
                            @endforeach
                        ],
                        datasets: [{
                            data: [
                                @foreach($gearBreakdown as $g)
                                    {{ $g->qty }},
                                @endforeach
                            ],
                            backgroundColor: chartColors.gear,
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: { size: 11 },
                                    padding: 8,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + ' kg';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        @endif

        // Zone Chart
        @if($zoneBreakdown && $zoneBreakdown->count() > 0)
            const zoneCtx = document.getElementById('zoneChart');
            if (zoneCtx) {
                new Chart(zoneCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            @foreach($zoneBreakdown as $z)
                                '{{ $z->zone?->name ?? 'Unknown' }}',
                            @endforeach
                        ],
                        datasets: [{
                            data: [
                                @foreach($zoneBreakdown as $z)
                                    {{ $z->qty }},
                                @endforeach
                            ],
                            backgroundColor: chartColors.zone,
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: { size: 11 },
                                    padding: 8,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + ' kg';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        @endif
    }

    // CSV Export Modal Functions
    function openCsvExportModal(series) {
        const modal = document.getElementById('csvExportModal');
        
        // Store the current series
        window.csvExportSeries = series;
        
        if (series === 'zone-summary') {
            document.getElementById('csvExportTitle').textContent = 'Export Zone Summary';
        } else if (series === 'annual') {
            document.getElementById('csvExportTitle').textContent = 'Export Annual Data';
        } else {
            document.getElementById('csvExportTitle').textContent = `Export ${series.charAt(0).toUpperCase() + series.slice(1)} Data`;
        }

        // Show/hide month/year filters based on series type
        const monthYearFilters = document.getElementById('monthYearFilters');
        if (series === 'monthly' || series === 'daily') {
            monthYearFilters.classList.remove('hidden');
            populateYearSelect();
        } else {
            monthYearFilters.classList.add('hidden');
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function populateYearSelect() {
        const yearSelect = document.getElementById('csvYearSelect');
        const currentYear = new Date().getFullYear();
        
        // Clear existing options except the first one
        while (yearSelect.options.length > 1) {
            yearSelect.remove(1);
        }
        
        // Add years from current year back 5 years
        for (let i = 0; i < 5; i++) {
            const year = currentYear - i;
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            yearSelect.appendChild(option);
        }
    }

    function closeCSVModal() {
        const modal = document.getElementById('csvExportModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function exportCSV(exportType) {
        const series = window.csvExportSeries || 'monthly';
        let url = `?format=csv&series=${series}`;
        
        // Add month/year parameters if available
        if (series === 'monthly' || series === 'daily') {
            const year = document.getElementById('csvYearSelect').value;
            const month = document.getElementById('csvMonthSelect').value;
            
            if (year) {
                url += `&year=${year}`;
            }
            if (month) {
                url += `&month=${month}`;
            }
        }
        
        if (exportType === 'all') {
            url += '&separated=species';
        }
        
        window.location.href = url;
        closeCSVModal();
    }
</script>

<!-- CSV Export Modal -->
<div id="csvExportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 id="csvExportTitle" class="text-lg font-semibold text-gray-900">Export Data</h3>
        </div>
        
        <div class="p-6 space-y-4">
            <!-- Month/Year filters for monthly/daily exports -->
            <div id="monthYearFilters" class="hidden space-y-3 pb-4 border-b border-gray-200">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                    <select id="csvYearSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Years</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <select id="csvMonthSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Months</option>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
            </div>

            <button onclick="exportCSV('all')" class="w-full px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition border border-blue-200 font-medium text-sm">
                📊 Export All Data
            </button>
        </div>
        
        <div class="border-t border-gray-200 px-6 py-3 bg-gray-50 rounded-b-lg flex justify-end">
            <button onclick="closeCSVModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition font-medium text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>


</x-app-layout>
