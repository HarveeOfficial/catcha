<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Edit Fishing Zone
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Main Container -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Map Side (Takes 2/3 width) -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-md rounded-lg h-full">
                        <div id="map" class="w-full h-96 md:h-full md:min-h-screen rounded-lg bg-gray-100"></div>
                    </div>
                </div>

                <!-- Form Side (Takes 1/3 width) -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-md rounded-lg sticky top-6">
                        <div class="p-6 text-gray-900 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Zone Details</h3>
                            <p class="text-xs text-gray-500 mt-1">Edit zone information and species</p>
                        </div>

                        <div class="p-6 space-y-5">
                            @if ($errors->any())
                                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded text-sm">
                                    <p class="font-semibold text-red-700 mb-2">Errors:</p>
                                    <ul class="list-disc list-inside space-y-1 text-red-600">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.zones.update', $zone) }}" id="zoneForm" class="space-y-5">
                                @csrf
                                @method('PATCH')

                                <!-- Zone Name -->
                                <div>
                                    <label for="name" class="block text-sm font-semibold mb-2 text-gray-700">Zone Name <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        class="w-full px-3 py-2 border border-gray-300 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                        value="{{ old('name', $zone->name) }}"
                                        required
                                    >
                                </div>

                                <!-- Zone Color -->
                                <div>
                                    <label class="block text-sm font-semibold mb-2 text-gray-700">Color <span class="text-red-500">*</span></label>
                                    <div class="flex gap-3 items-center">
                                        <input
                                            type="color"
                                            name="color"
                                            id="color"
                                            class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer"
                                            value="{{ old('color', $zone->color) }}"
                                            required
                                        >
                                        <input
                                            type="text"
                                            id="colorText"
                                            class="flex-1 px-3 py-2 border border-gray-300 bg-gray-50 text-gray-900 rounded-lg font-mono text-sm"
                                            readonly
                                        >
                                    </div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-semibold mb-2 text-gray-700">Description</label>
                                    <textarea
                                        name="description"
                                        id="description"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    >{{ old('description', $zone->description) }}</textarea>
                                </div>

                                <!-- Species -->
                                <div>
                                    <label for="speciesSearch" class="block text-sm font-semibold mb-2 text-gray-700">Fish Species</label>
                                    
                                    <!-- Search Input -->
                                    <input
                                        type="text"
                                        id="speciesSearch"
                                        placeholder="Search fish by name..."
                                        class="w-full px-3 py-2 border border-gray-300 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm mb-2"
                                    >
                                    
                                    <!-- Select All Checkbox -->
                                    <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border border-gray-300 rounded-lg bg-gray-50 mb-2">
                                        <input
                                            type="checkbox"
                                            id="selectAllSpecies"
                                            class="w-4 h-4 text-blue-600 rounded cursor-pointer"
                                        >
                                        <span class="ml-3 font-medium text-sm text-gray-900">Select All Species</span>
                                    </label>
                                    
                                    <!-- Selected Species Display -->
                                    <div id="selectedSpecies" class="mb-3 p-3 bg-gray-50 rounded-lg min-h-[40px] flex flex-wrap gap-2 items-center">
                                        <span class="text-xs text-gray-500 w-full" id="emptyMessage">No species selected</span>
                                    </div>
                                    
                                    <!-- Species List -->
                                    <div class="border border-gray-300 rounded-lg bg-white max-h-64 overflow-y-auto">
                                        <div id="speciesList" class="divide-y">
                                            @foreach ($species as $sp)
                                                <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer species-item" data-common-name="{{ strtolower($sp->common_name) }}" data-scientific-name="{{ strtolower($sp->scientific_name ?? '') }}" data-species-id="{{ $sp->id }}">
                                                    <input
                                                        type="checkbox"
                                                        name="species_ids[]"
                                                        value="{{ $sp->id }}"
                                                        class="w-4 h-4 text-blue-600 rounded cursor-pointer species-checkbox"
                                                        @checked(in_array($sp->id, old('species_ids', $selectedSpeciesIds)))
                                                    >
                                                    <span class="ml-3 flex-1">
                                                        <span class="block text-sm font-medium text-gray-900">{{ $sp->common_name }}</span>
                                                        <span class="block text-xs text-gray-500">{{ $sp->scientific_name }}</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Active Status -->
                                <div class="border-t border-gray-200 pt-5">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            @checked(old('is_active', $zone->is_active))
                                            class="w-4 h-4 rounded border-gray-300 text-blue-600"
                                        >
                                        <span class="text-sm font-medium text-gray-700">Zone is active</span>
                                    </label>
                                </div>

                                <!-- Geometry (Hidden) -->
                                <input
                                    type="hidden"
                                    name="geometry"
                                    id="geometry"
                                    value="{{ old('geometry', json_encode($zone->geometry)) }}"
                                >

                                <!-- Status -->
                                <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm font-medium">
                                    ✓ Zone shape loaded and ready to edit
                                </div>

                                <!-- Drawing Tools Info -->
                                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-sm font-semibold text-blue-900">🖍️ Drawing Tools</p>
                                    <p class="text-xs text-blue-700 mt-1">Use the toolbar in the top-left corner of the map to draw polygons or rectangles, edit shapes, or delete them.</p>
                                </div>

                                <!-- Instructions -->
                                <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600 space-y-1.5">
                                    <p><strong class="text-gray-900">How to edit:</strong></p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Click buttons to draw or edit</li>
                                        <li>Drag points to move shapes</li>
                                        <li>Use delete tool to remove shapes</li>
                                    </ul>
                                </div>

                                <!-- Submit -->
                                <div class="flex gap-3 pt-5 border-t border-gray-200">
                                    <button
                                        type="submit"
                                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-sm"
                                    >
                                        Update Zone
                                    </button>
                                    <a
                                        href="{{ route('admin.zones.show', $zone) }}"
                                        class="px-4 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200 transition font-semibold text-sm"
                                    >
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<style>
    #map {
        position: relative;
        z-index: 1;
    }
    .leaflet-control-container {
        position: relative;
        z-index: 1000 !important;
    }
    .leaflet-top {
        z-index: 1000 !important;
    }
    .leaflet-draw-toolbar {
        z-index: 1001 !important;
        pointer-events: auto !important;
    }
    .leaflet-top .leaflet-control {
        z-index: 1000 !important;
        pointer-events: auto !important;
    }
    .leaflet-draw-actions {
        z-index: 1002 !important;
        pointer-events: auto !important;
    }
    .leaflet-draw-toolbar a {
        pointer-events: auto !important;
    }
</style>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script>
    let map;
    let drawnItems;

    function initializeMap() {
        if (typeof L === 'undefined') {
            setTimeout(initializeMap, 50);
            return;
        }

        try {
            // Initialize map - Aparri, Cagayan
            map = L.map('map').setView([18.3589, 121.8336], 11);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            // Create feature group for drawn items
            drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            // Check if L.Draw is available before creating control
            if (typeof L.Draw === 'undefined') {
                console.error('L.Draw is not available');
                setTimeout(() => {
                    if (typeof L.Draw !== 'undefined') {
                        addDrawControl();
                    }
                }, 500);
            } else {
                addDrawControl();
            }

            // Color picker sync
            const colorInput = document.getElementById('color');
            const colorText = document.getElementById('colorText');
            if (colorInput && colorText) {
                colorInput.addEventListener('change', function() {
                    colorText.value = this.value;
                });
                colorText.value = colorInput.value;
            }

            // Load existing geometry
            const existing = {!! json_encode($zone->geometry) !!};
            if (existing && existing.features && existing.features.length > 0) {
                L.geoJSON(existing, {
                    onEachFeature: function(feature, layer) {
                        drawnItems.addLayer(layer);
                    }
                });
                
                const group = new L.featureGroup();
                L.geoJSON(existing).eachLayer(layer => group.addLayer(layer));
                map.fitBounds(group.getBounds());
                updateGeometry();
            }

            console.log('Map initialized');
        } catch (e) {
            console.error('Map initialization error:', e);
        }
    }

    function addDrawControl() {
        try {
            const drawControl = new L.Control.Draw({
                position: 'topleft',
                draw: {
                    polygon: { allowIntersection: true },
                    rectangle: {},
                    polyline: false,
                    circle: false,
                    marker: false,
                    circlemarker: false,
                },
                edit: {
                    featureGroup: drawnItems,
                    edit: true,
                    remove: true,
                    poly: { allowIntersection: true },
                },
            });
            map.addControl(drawControl);

            // Store draw control globally so buttons can access it
            window.drawControl = drawControl;

            // Handle draw events
            map.on('draw:created', function (e) {
                const layer = e.layer;
                // Style the newly drawn layer based on selected color
                const colorInput = document.getElementById('color');
                const color = colorInput ? colorInput.value : '#3388ff';
                if (layer.setStyle) {
                    layer.setStyle({ color: color, weight: 2, fillOpacity: 0.2 });
                }
                drawnItems.addLayer(layer);
                updateGeometry();
            });

            map.on('draw:edited', function () {
                updateGeometry();
            });

            map.on('draw:deleted', function () {
                updateGeometry();
            });

            // Prevent draw mode from auto-activating
            map.on('draw:drawstart', function() {
                map.dragging.disable();
            });

            map.on('draw:drawstop', function() {
                map.dragging.enable();
            });

            map.on('draw:editstart', function() {
                map.dragging.disable();
            });

            map.on('draw:editstop', function() {
                map.dragging.enable();
            });

            console.log('Draw control added successfully');
            console.log('Draw control object:', drawControl);
            console.log('Map controls:', map._controlContainer);
        } catch (e) {
            console.error('Error adding draw control:', e);
            alert('Failed to add drawing tools: ' + e.message);
        }
    }

    function updateGeometry() {
        if (!drawnItems) return;
        
        const geoJson = drawnItems.toGeoJSON();
        document.getElementById('geometry').value = JSON.stringify(geoJson);
        
        const statusEl = document.getElementById('drawingStatus');
        
        if (geoJson.features.length > 0) {
            if (statusEl) {
                statusEl.textContent = `Zone shape ready (${geoJson.features.length} shape${geoJson.features.length > 1 ? 's' : ''})`;
                statusEl.className = 'p-3 bg-green-50 text-green-700 rounded-lg text-sm font-medium';
            }
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeMap();
        initializeSpeciesSearch();
    });

    // Species search functionality
    function initializeSpeciesSearch() {
        const searchInput = document.getElementById('speciesSearch');
        const speciesItems = document.querySelectorAll('.species-item');
        const speciesCheckboxes = document.querySelectorAll('.species-checkbox');
        const selectedSpeciesDiv = document.getElementById('selectedSpecies');
        const emptyMessage = document.getElementById('emptyMessage');

        // Search filter
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                speciesItems.forEach(item => {
                    const commonName = item.dataset.commonName || '';
                    const scientificName = item.dataset.scientificName || '';
                    
                    const matches = commonName.includes(searchTerm) || scientificName.includes(searchTerm) || searchTerm === '';
                    item.style.display = matches ? '' : 'none';
                });
            });
        }

        // Update selected species display
        function updateSelectedDisplay() {
            const selected = Array.from(speciesCheckboxes).filter(cb => cb.checked);
            selectedSpeciesDiv.innerHTML = '';
            
            if (selected.length === 0) {
                selectedSpeciesDiv.appendChild(emptyMessage.cloneNode(true));
            } else {
                selected.forEach(checkbox => {
                    const item = checkbox.closest('.species-item');
                    const commonName = item.querySelector('.text-gray-900').textContent;
                    
                    const tag = document.createElement('span');
                    tag.className = 'inline-flex items-center gap-2 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full';
                    tag.innerHTML = `
                        ${commonName}
                        <button type="button" class="ml-1 hover:text-blue-600" onclick="document.querySelector('input[value=\'${checkbox.value}\']').click(); event.preventDefault();">
                            ×
                        </button>
                    `;
                    selectedSpeciesDiv.appendChild(tag);
                });
            }
        }

        // Handle checkbox changes
        speciesCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedDisplay);
        });

        // Handle Select All checkbox
        const selectAllCheckbox = document.getElementById('selectAllSpecies');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                speciesCheckboxes.forEach(checkbox => {
                    const item = checkbox.closest('.species-item');
                    // Only check/uncheck visible checkboxes (not hidden by search)
                    if (item.style.display !== 'none') {
                        checkbox.checked = this.checked;
                    }
                });
                updateSelectedDisplay();
            });
        }

        // Initialize display with pre-selected items
        updateSelectedDisplay();
    }

    // No custom draw buttons needed; use the map's toolbar
</script>
</x-app-layout>
