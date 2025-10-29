<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Fishing Zones
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Global Map Section -->
            @if ($zones->isNotEmpty())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <h3 class="text-lg font-bold mb-4">All Zones Map</h3>
                    <div id="globalMap" class="w-full h-[600px] lg:h-[700px] rounded-lg bg-white border-2 border-gray-200"></div>
                </div>
            </div>
            @endif

            <!-- Zones List Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <a href="{{ route('admin.zones.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        + Create Zone
                    </a>
                </div>

                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($zones->isEmpty())
                    <div class="text-center py-12">
                        <p class="text-gray-500 mb-4">No zones created yet.</p>
                        <a href="{{ route('admin.zones.create') }}" class="text-blue-600 hover:underline">
                            Create your first zone
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($zones as $zone)
                            <div class="border rounded-lg p-4 hover:shadow-lg transition">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-xl font-semibold">{{ $zone->name }}</h3>
                                    <div class="w-6 h-6 rounded" style="background-color: {{ $zone->color }}"></div>
                                </div>
                                
                                @if ($zone->description)
                                    <p class="text-sm text-gray-600 mb-3">{{ $zone->description }}</p>
                                @endif

                                <div class="mb-3">
                                    <p class="text-xs text-gray-500 mb-1">Fish Species:</p>
                                    @if ($zone->species->isEmpty())
                                        <p class="text-sm text-gray-400">No species assigned</p>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($zone->species->take(3) as $species)
                                                <span class="inline-block px-2 py-1 bg-gray-200 text-xs rounded">
                                                    {{ $species->common_name }}
                                                </span>
                                            @endforeach
                                            @if ($zone->species->count() > 3)
                                                <span class="inline-block px-2 py-1 text-xs text-gray-600">
                                                    +{{ $zone->species->count() - 3 }} more
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="flex gap-2 pt-3 border-t">
                                    <a href="{{ route('admin.zones.show', $zone) }}" class="flex-1 text-center px-3 py-2 bg-gray-100 text-gray-900 rounded hover:bg-gray-200 transition text-sm">
                                        View
                                    </a>
                                    <a href="{{ route('admin.zones.edit', $zone) }}" class="flex-1 text-center px-3 py-2 bg-blue-100 text-blue-900 rounded hover:bg-blue-200 transition text-sm">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.zones.destroy', $zone) }}" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this zone?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-3 py-2 bg-red-100 text-red-900 rounded hover:bg-red-200 transition text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if ($zones->isNotEmpty())
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - starting map initialization');
    
    // Check if Leaflet is loaded
    if (typeof L === 'undefined') {
        console.error('Leaflet not loaded!');
        return;
    }
    console.log('Leaflet loaded successfully');
    
    // Check if map container exists
    const mapContainer = document.getElementById('globalMap');
    if (!mapContainer) {
        console.error('Map container #globalMap not found!');
        return;
    }
    console.log('Map container found:', mapContainer);
    console.log('Container dimensions:', mapContainer.offsetWidth, 'x', mapContainer.offsetHeight);
    
    try {
        // Initialize map
        const map = L.map('globalMap').setView([10.3157, 123.8854], 6);
        console.log('Map initialized');
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);
        console.log('Tiles added');

        // All zones data from backend
        const zonesData = {!! json_encode($zones->map(function($zone) {
        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'color' => $zone->color,
            'geometry' => $zone->geometry,
            'species' => $zone->species->pluck('common_name')->toArray()
        ];
    })->values()->toArray()) !!};
    
    const zones = Object.values(zonesData);
    console.log('Zones data:', zones);

    const zoneLayers = [];
    const bounds = [];

    // Add each zone to the map
    zones.forEach(zone => {
        if (zone.geometry && zone.geometry.features) {
            const geoJsonLayer = L.geoJSON(zone.geometry, {
                style: {
                    color: zone.color,
                    weight: 2,
                    fillOpacity: 0.3,
                    fillColor: zone.color
                },
                onEachFeature: function(feature, layer) {
                    // Store zone info for overlap detection
                    layer.zoneId = zone.id;
                    layer.zoneName = zone.name;
                    layer.zoneSpecies = zone.species;
                    layer.zoneColor = zone.color;
                    
                    zoneLayers.push(layer);
                    
                    // Add bounds
                    if (layer.getBounds) {
                        bounds.push(layer.getBounds());
                    }
                    
                    // Basic popup with zone info
                    let speciesHtml = '';
                    if (zone.species && zone.species.length > 0) {
                        speciesHtml = '<div class="mt-2"><strong>Species:</strong><br>' + 
                            zone.species.map(s => `<span class="inline-block px-2 py-1 bg-gray-100 rounded text-xs mr-1 mb-1">${s}</span>`).join('') +
                            '</div>';
                    }
                    
                    layer.bindPopup(`
                        <div class="p-2">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-4 h-4 rounded" style="background-color: ${zone.color}"></div>
                                <strong class="text-lg">${zone.name}</strong>
                            </div>
                            ${speciesHtml}
                        </div>
                    `);
                }
            }).addTo(map);
        }
    });

    // Fit map to show all zones
    if (bounds.length > 0) {
        const group = L.featureGroup(zoneLayers);
        map.fitBounds(group.getBounds().pad(0.1));
    }

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
        
        return overlaps;
    }

    // Detect overlaps after all zones are loaded
    setTimeout(() => {
        const overlaps = detectOverlaps();
        console.log('Detected overlaps:', overlaps);
    }, 500);
    
    } catch (error) {
        console.error('Error initializing map:', error);
    }
});
</script>
@endif
</x-app-layout>
