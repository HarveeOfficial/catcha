<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900">
                {{ $zone->name }}
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.zones.edit', $zone) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    ✎ Edit Zone
                </a>
                <a href="{{ route('admin.zones.index') }}" class="px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 transition font-medium">
                    ← Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Description Banner -->
            @if ($zone->description)
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-6">
                    <p class="text-gray-700 text-lg leading-relaxed">{{ $zone->description }}</p>
                </div>
            @endif

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Map Section (2/3 width) -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div id="map" class="w-full h-96 lg:h-[500px] bg-gray-100"></div>
                    </div>
                </div>

                <!-- Sidebar (1/3 width) -->
                <div class="space-y-6">
                    <!-- Zone Status Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-lg border-4 border-gray-200" style="background-color: {{ $zone->color }}"></div>
                            <div>
                                @if ($zone->is_active)
                                    <div class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold inline-block">
                                        ✓ Active
                                    </div>
                                @else
                                    <div class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold inline-block">
                                        Inactive
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Zone Info Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Zone Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Created</dt>
                                <dd class="text-gray-900 mt-1">{{ $zone->created_at->format('M d, Y') }}</dd>
                            </div>
                            <div class="pt-3 border-t border-gray-200">
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Updated</dt>
                                <dd class="text-gray-900 mt-1">{{ $zone->updated_at->format('M d, Y h:i A') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Fish Species Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Fish Species in This Zone</h3>
                @if ($zone->species->isEmpty())
                    <div class="text-center py-12">
                        <p class="text-gray-500 text-lg">No species assigned to this zone yet.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($zone->species as $species)
                            <div class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-lg hover:shadow-md transition">
                                <div class="font-semibold text-gray-900">{{ $species->common_name }}</div>
                                @if ($species->category)
                                    <div class="text-xs text-blue-700 font-medium mt-1 inline-block bg-blue-100 px-2 py-1 rounded">
                                        {{ $species->category }}
                                    </div>
                                @endif
                                @if ($species->scientific_name)
                                    <div class="text-sm text-gray-600 italic mt-2">{{ $species->scientific_name }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
    const map = L.map('map').setView([10.3157, 123.8854], 7);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    // Display zone geometry
    const geometry = {!! json_encode($zone->geometry) !!};
    if (geometry && geometry.features) {
        L.geoJSON(geometry, {
            style: function() {
                return {
                    color: '{{ $zone->color }}',
                    weight: 2,
                    opacity: 0.8,
                    fillOpacity: 0.3,
                };
            }
        }).addTo(map);
        
        // Fit map to bounds
        const group = new L.featureGroup();
        L.geoJSON(geometry).eachLayer(layer => group.addLayer(layer));
        map.fitBounds(group.getBounds());
    }
</script>
</x-app-layout>
