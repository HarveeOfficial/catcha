<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catch #{{ $catch->id }}</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Main Catch Details Card -->
        <div class="bg-white shadow-sm rounded-lg p-8 mb-6">
            <div class="grid gap-8 md:grid-cols-2 mb-8">
                <!-- Left: Core Catch Info -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Catch Details</h3>
                        <div class="space-y-3">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Caught At</div>
                                <div class="font-medium text-gray-900">{{ $catch->caught_at->format('Y-m-d H:i') }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Species</div>
                                <div class="font-medium text-gray-900">{{ $catch->species?->common_name ?? '—' }}</div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-gray-500">Quantity (kg)</div>
                                    <div class="font-medium text-gray-900">{{ $catch->quantity ?? '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-gray-500">Count</div>
                                    <div class="font-medium text-gray-900">{{ $catch->count ?? '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-gray-500">Avg Size (cm)</div>
                                    <div class="font-medium text-gray-900">{{ $catch->avg_size_cm ?? '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-gray-500">Weather</div>
                                    <div class="font-medium text-gray-900">
                                        @if ($catch->environmental_data && isset($catch->environmental_data['weather']))
                                            {{ match($catch->environmental_data['weather']) {
                                                'sunny' => '☀️ Sunny',
                                                'rainy' => '🌧️ Rainy',
                                                'cloudy' => '☁️ Cloudy',
                                                'windy' => '💨 Windy',
                                                'stormy' => '⛈️ Stormy',
                                                'foggy' => '🌫️ Foggy',
                                                default => $catch->environmental_data['weather'],
                                            } }}
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Vessel & Gear</h3>
                        <div class="space-y-3">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Vessel Name</div>
                                <div class="font-medium text-gray-900">{{ $catch->vessel_name ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Gear Type</div>
                                <div class="font-medium text-gray-900">{{ $catch->gearType?->name ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Location Description</div>
                                <div class="font-medium text-gray-900 truncate" title="{{ $catch->location }}">{{ $catch->location ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6 text-xs text-gray-500">
                        Recorded by: <strong>{{ $catch->user?->name ?? '—' }}</strong>
                    </div>
                </div>

                <!-- Right: Map -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">Location Map</h3>
                        <div class="text-xs text-gray-500">Toggle layers</div>
                    </div>
                    <div id="catchShowMap" class="w-full h-80 rounded-lg border-2 border-gray-300 overflow-hidden shadow-sm"></div>
                    @if (!$catch->latitude || !$catch->longitude)
                        <p class="mt-3 text-xs text-amber-600">No precise coordinates recorded for this catch.</p>
                    @else
                        <p class="mt-3 text-xs text-gray-600"><strong>Coordinates:</strong> {{ number_format($catch->latitude, 6) }}, {{ number_format($catch->longitude, 6) }}</p>
                    @endif
                </div>
            </div>

            <!-- Environmental Impact Section -->
            @if ($catch->bycatch_quantity || $catch->bycatch_species_ids || $catch->discard_quantity || $catch->discard_species_ids)
                <div class="border-t pt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Environmental Impact</h3>
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Bycatch -->
                        @if ($catch->bycatch_quantity || $catch->bycatch_species_ids)
                            <div class="bg-amber-50 border-2 border-amber-200 rounded-lg p-4">
                                <h4 class="font-semibold text-amber-900 mb-3">Bycatch</h4>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="text-gray-600">Quantity (kg):</span>
                                        <span class="font-medium">{{ $catch->bycatch_quantity ?? '—' }}</span>
                                    </div>
                                    @if ($catch->bycatch_species_ids && count($catch->bycatch_species_ids) > 0)
                                        <div>
                                            <span class="text-gray-600">Species:</span>
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach ($catch->bycatch_species_ids as $speciesId)
                                                    @php($species = \App\Models\Species::find($speciesId))
                                                    @if ($species)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                            {{ $species->common_name }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Discard -->
                        @if ($catch->discard_quantity || $catch->discard_species_ids || $catch->discard_reason)
                            <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                                <h4 class="font-semibold text-red-900 mb-3">Discard</h4>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="text-gray-600">Quantity (kg):</span>
                                        <span class="font-medium">{{ $catch->discard_quantity ?? '—' }}</span>
                                    </div>
                                    @if ($catch->discard_species_ids && count($catch->discard_species_ids) > 0)
                                        <div>
                                            <span class="text-gray-600">Species:</span>
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach ($catch->discard_species_ids as $speciesId)
                                                    @php($species = \App\Models\Species::find($speciesId))
                                                    @if ($species)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            {{ $species->common_name }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if ($catch->discard_reason)
                                        <div>
                                            <span class="text-gray-600">Reason:</span>
                                            <span class="font-medium">
                                                {{ match($catch->discard_reason) {
                                                    'too_small' => 'Too Small',
                                                    'damaged' => 'Damaged',
                                                    'dead' => 'Dead',
                                                    'species_not_allowed' => 'Species Not Allowed',
                                                    'over_quota' => 'Over Quota',
                                                    'other' => 'Other',
                                                    default => $catch->discard_reason,
                                                } }}
                                            </span>
                                        </div>
                                        @if ($catch->discard_reason === 'other' && $catch->discard_reason_other)
                                            <div class="mt-2 p-2 bg-white rounded border border-red-100">
                                                <p class="text-xs text-gray-700">{{ $catch->discard_reason_other }}</p>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
            <div class="flex items-center gap-4 pt-2">
                <a href="{{ route('catches.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Back</a>
                @php($u = auth()->user())
                @php($hasFeedback = $catch->feedbacks()->exists())
                @if ($u && $u->id === $catch->user_id && (!$hasFeedback || $u->isExpert()))
                    <a href="{{ route('catches.edit', $catch) }}"
                        class="text-sm inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        ✏️ Edit
                    </a>
                @endif
            </div>
        </div>

    </div>
    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>
    @endonce
    <script>
        (function() {
            const lat = {{ $catch->latitude ?? 'null' }};
            const lon = {{ $catch->longitude ?? 'null' }};
            const hasCoords = lat !== null && lon !== null;
            const center = hasCoords ? [lat, lon] : [14.5995, 120.9842];
            const map = L.map('catchShowMap', {
                center: center,
                zoom: hasCoords ? 11 : 6
            });
            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OSM contributors'
            }).addTo(map);
            const esri = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Imagery &copy; Esri'
                });
            L.control.layers({
                'Street': osm,
                'Satellite': esri
            }).addTo(map);
            
            let zoneLayers = [];
            
            // Load zones from API
            fetch('/api/zones/data')
                .then(resp => resp.json())
                .then(data => {
                    if (!data || !data.zones) return;
                    
                    data.zones.forEach(zone => {
                        if (!zone.geometry) return;
                        
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
                                layer.zoneData = zone;
                                layer.zoneName = zone.name;
                                layer.zoneColor = color;
                                layer.zoneSpecies = (zone.species || []).map(s => s.name);
                                zoneLayers.push(layer);
                                
                                let popupText = `<strong>${zone.name}</strong>`;
                                layer.bindPopup(popupText);
                            }
                        }).addTo(map);
                    });
                    
                    // After zones are loaded, add current catch with validation
                    if (hasCoords) {
                        const weight = {{ $catch->quantity ?? 0 }};
                        const species = "{{ optional($catch->species)->common_name ?? 'Unknown' }}";
                        const size = Math.min(Math.max(weight / 2, 5), 15);
                        
                        // Validate current catch against zones
                        let isInCorrectZone = false;
                        let invalidZones = [];
                        let wrongSpeciesZones = [];
                        let catchZones = [];
                        
                        zoneLayers.forEach(zoneLayer => {
                            const geoJsonZone = zoneLayer.toGeoJSON();
                            let zoneGeom = geoJsonZone.type === 'FeatureCollection' ? geoJsonZone.features[0] : geoJsonZone;
                            if (zoneGeom.type === 'Feature') zoneGeom = zoneGeom.geometry;
                            
                            try {
                                const catchPoint = turf.point([lon, lat]);
                                const isInside = turf.booleanPointInPolygon(catchPoint, zoneGeom);
                                
                                const speciesInZone = zoneLayer.zoneSpecies.some(s => 
                                    s.toLowerCase() === species.toLowerCase()
                                );
                                
                                if (isInside) {
                                    catchZones.push({
                                        name: zoneLayer.zoneName,
                                        speciesMatch: speciesInZone
                                    });
                                    
                                    if (speciesInZone) {
                                        isInCorrectZone = true;
                                    } else {
                                        wrongSpeciesZones.push(zoneLayer.zoneName);
                                    }
                                }
                            } catch (e) {
                                console.log('Error checking zone geometry:', e);
                            }
                        });
                        
                        const hasIssues = wrongSpeciesZones.length > 0;
                        const markerColor = hasIssues ? '#ef4444' : '#3b82f6';
                        const markerBorder = hasIssues ? '#dc2626' : '#1e40af';
                        
                        const marker = L.circleMarker([lat, lon], {
                            radius: size,
                            fillColor: markerColor,
                            color: markerBorder,
                            weight: hasIssues ? 2 : 1,
                            opacity: 0.7,
                            fillOpacity: 0.6
                        }).addTo(map);

                        let popupHtml = `<strong>${species}</strong><br/>Weight: ${weight} kg`;
                        if (catchZones.length > 0) {
                            popupHtml += '<br/><div class="mt-2 text-sm"><strong>In zones:</strong><ul class="list-disc list-inside">';
                            catchZones.forEach(zone => {
                                let statusIcon = '✓';
                                let statusClass = 'text-green-600';
                                let warning = '';
                                
                                if (!zone.speciesMatch) {
                                    statusIcon = '⚠️';
                                    statusClass = 'text-orange-600';
                                    warning = ' <span class="text-xs">(species not in zone)</span>';
                                }
                                
                                popupHtml += `<li class="${statusClass}">${statusIcon} ${zone.name}${warning}</li>`;
                            });
                            popupHtml += '</ul></div>';
                        } else {
                            popupHtml += '<br/><span class="text-xs text-gray-500">Not in any zone</span>';
                        }
                        
                        marker.bindPopup(popupHtml);
                    }
                })
                .catch(err => console.error('Error loading zones:', err));
        })();
    </script>
    <script>
        // Optional: expose a simple re-run function used by the modal button
        document.addEventListener('alpine:init', () => {
            // no global registration needed; component-scoped method is used
        });
    </script>
</x-app-layout>
