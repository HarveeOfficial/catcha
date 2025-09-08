<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Record Catch') }}</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-md p-6">
            <form action="{{ route('catches.store') }}" method="post" class="space-y-6">
                @csrf
                <div>
                    <x-input-label for="caught_at" value="Caught At" />
                    <x-text-input id="caught_at" type="datetime-local" name="caught_at" value="{{ old('caught_at') }}" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('caught_at')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="species_id" value="Species" />
                    <select id="species_id" name="species_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Unknown --</option>
                        @foreach($species as $s)
                            <option value="{{ $s->id }}" @selected(old('species_id')==$s->id)>{{ $s->common_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('species_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="location" value="Location" />
                    <x-text-input id="location" type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Zone A, GPS spot, etc." class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('location')" class="mt-1" />
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <x-input-label for="latitude" value="Latitude" />
                        <x-text-input id="latitude" type="number" step="0.000001" name="latitude" value="{{ old('latitude') }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('latitude')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="longitude" value="Longitude" />
                        <x-text-input id="longitude" type="number" step="0.000001" name="longitude" value="{{ old('longitude') }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('longitude')" class="mt-1" />
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="button" id="geoDetectBtn" class="mt-6 inline-flex px-3 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-medium rounded shadow">Use My Location</button>
                        <button type="button" id="clearLocationBtn" class="mt-6 inline-flex px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-medium rounded">Clear</button>
                    </div>
                </div>
                <div>
                    <x-input-label value="Map (Click to set marker / drag marker)" />
                    <div id="catchMap" class="mt-2 w-full h-72 rounded border border-gray-300 overflow-hidden"></div>
                    <p class="mt-2 text-xs text-gray-500 leading-snug">Standard and satellite layers provided by OpenStreetMap & Esri. Ensure usage complies with provider terms. Latitude & Longitude fields update automatically.</p>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <x-input-label for="quantity" value="Quantity (kg)" />
                        <x-text-input id="quantity" type="number" step="0.01" name="quantity" value="{{ old('quantity') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="count" value="Count" />
                        <x-text-input id="count" type="number" name="count" value="{{ old('count') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="avg_size_cm" value="Avg Size (cm)" />
                        <x-text-input id="avg_size_cm" type="number" step="0.01" name="avg_size_cm" value="{{ old('avg_size_cm') }}" class="mt-1 block w-full" />
                    </div>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="gear_type" value="Gear Type" />
                        <x-text-input id="gear_type" type="text" name="gear_type" value="{{ old('gear_type') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="vessel_name" value="Vessel Name" />
                        <x-text-input id="vessel_name" type="text" name="vessel_name" value="{{ old('vessel_name') }}" class="mt-1 block w-full" />
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    <a href="{{ route('catches.index') }}" class="text-sm text-gray-600 hover:text-gray-800">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            /* Ensure map always has height & sits below nav */
            #catchMap, #catchShowMap { min-height: 18rem; }
            .leaflet-container { font: inherit; z-index:0; }
        </style>
        <script id="leaflet-cdn" src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            // Fallback loader if unpkg blocked
            window.addEventListener('error', function(e){
                if(e.target && e.target.id === 'leaflet-cdn'){
                    const alt = document.createElement('script');
                    alt.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js';
                    alt.onload = initCatchMap;
                    document.head.appendChild(alt);
                }
            }, true);
        </script>
    @endonce
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            if(typeof L === 'undefined') {
                // If still not loaded, wait briefly then try again
                setTimeout(function(){
                    if(typeof L === 'undefined') {
                        const el = document.getElementById('catchMap');
                        if(el){ el.innerHTML = '<div class="p-4 text-sm text-red-600">Map library failed to load. Check your internet connection or content blocker.</div>'; }
                        return;
                    }
                    initCatchMap();
                }, 600);
            } else {
                initCatchMap();
            }
        });

        function initCatchMap(){
            if(typeof L === 'undefined'){ return; }
            const latInput = document.getElementById('latitude');
            const lonInput = document.getElementById('longitude');
            if(!latInput || !lonInput){ return; }
            const detectBtn = document.getElementById('geoDetectBtn');
            const clearBtn = document.getElementById('clearLocationBtn');

            const defaultLat = latInput.value ? parseFloat(latInput.value) : 18.32916452647898;
            const defaultLon = lonInput.value ? parseFloat(lonInput.value) : 121.61577064268877;

            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM contributors' });
            const esri = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: 'Imagery &copy; Esri' });

            const map = L.map('catchMap', { center: [defaultLat, defaultLon], zoom: 10, layers: [osm] });
            L.control.layers({ 'Street': osm, 'Satellite': esri }, null, { position: 'topright' }).addTo(map);

            let marker = L.marker([defaultLat, defaultLon], { draggable: true }).addTo(map);
            if(!latInput.value || !lonInput.value){ marker.setOpacity(0.6); }

            function updateInputs(lat, lon, fromDrag = false){
                latInput.value = lat.toFixed(6);
                lonInput.value = lon.toFixed(6);
                marker.setOpacity(1);
                if(!fromDrag){ marker.setLatLng([lat, lon]); }
            }
            map.on('click', e => updateInputs(e.latlng.lat, e.latlng.lng));
            marker.on('dragend', e => { const p = e.target.getLatLng(); updateInputs(p.lat, p.lng, true); });

            if(detectBtn){
                detectBtn.addEventListener('click', function(){
                    if(!navigator.geolocation){ return alert('Geolocation not supported'); }
                    detectBtn.disabled = true; const orig = detectBtn.textContent; detectBtn.textContent = 'Locating...';
                    navigator.geolocation.getCurrentPosition(pos => {
                        detectBtn.disabled = false; detectBtn.textContent = orig;
                        updateInputs(pos.coords.latitude, pos.coords.longitude);
                        map.setView([pos.coords.latitude, pos.coords.longitude], 13);
                    }, err => {
                        detectBtn.disabled = false; detectBtn.textContent = orig;
                        alert('Location error: ' + err.message);
                    }, { enableHighAccuracy: true, timeout: 10000 });
                });
            }
            if(clearBtn){
                clearBtn.addEventListener('click', function(){ latInput.value=''; lonInput.value=''; marker.setOpacity(0.4); });
            }
            // Resize safeguard if inside hidden tab (not now, but future proof)
            setTimeout(()=>{ map.invalidateSize(); }, 400);
        }
    </script>
</x-app-layout>
