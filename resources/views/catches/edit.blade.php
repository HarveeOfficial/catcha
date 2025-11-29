<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Catch') }} #{{ $catch->id }}</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-8">
            <form action="{{ route('catches.update', $catch) }}" method="post" class="space-y-8" id="catchForm">
                @csrf
                @method('PATCH')
                <input type="hidden" name="geohash" id="geohash" value="{{ old('geohash', $catch->geohash) }}" />
                <input type="hidden" name="geo_source" id="geo_source" value="{{ old('geo_source', $catch->geo_source) }}" />
                <input type="hidden" name="geo_accuracy_m" id="geo_accuracy_m" value="{{ old('geo_accuracy_m', $catch->geo_accuracy_m) }}" />

                <!-- Catch Basics Section -->
                <fieldset class="border-b pb-8">
                    <legend class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 text-sm font-bold">1</span>
                        Catch Details
                    </legend>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label for="caught_at" value="Caught At" />
                            <x-text-input id="caught_at" type="datetime-local" name="caught_at" value="{{ old('caught_at', $catch->caught_at?->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('caught_at')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="species_id" value="Species" />
                            <select id="species_id" name="species_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Unknown --</option>
                                @foreach($species as $s)
                                    <option value="{{ $s->id }}" @selected(old('species_id', $catch->species_id)==$s->id)>{{ $s->common_name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('species_id')" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid gap-6 md:grid-cols-3 mt-6">
                        <div>
                            <x-input-label for="quantity" value="Quantity (kg)" />
                            <x-text-input id="quantity" type="number" step="0.01" name="quantity" value="{{ old('quantity', $catch->quantity) }}" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="count" value="Count" />
                            <x-text-input id="count" type="number" name="count" value="{{ old('count', $catch->count) }}" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="avg_size_cm" value="Avg Size (cm)" />
                            <x-text-input id="avg_size_cm" type="number" step="0.01" name="avg_size_cm" value="{{ old('avg_size_cm', $catch->avg_size_cm) }}" class="mt-1 block w-full" />
                        </div>
                    </div>
                </fieldset>

                <!-- Environmental Conditions Section -->
                <fieldset class="border-b pb-8">
                    <legend class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-sm font-bold">2</span>
                        Environmental & Vessel Info
                    </legend>
                    <div class="grid gap-6 md:grid-cols-3">
                        <div>
                            <x-input-label for="weather" value="Weather" />
                            <select id="weather" name="weather" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Select Weather --</option>
                                <option value="sunny" @selected(old('weather', $catch->weather) === 'sunny')>☀️Sunny</option>
                                <option value="rainy" @selected(old('weather', $catch->weather) === 'rainy')>🌧️Rainy</option>
                                <option value="cloudy" @selected(old('weather', $catch->weather) === 'cloudy')>☁️Cloudy</option>
                                <option value="windy" @selected(old('weather', $catch->weather) === 'windy')>💨Windy</option>
                                <option value="stormy" @selected(old('weather', $catch->weather) === 'stormy')>⛈️Stormy</option>
                                <option value="foggy" @selected(old('weather', $catch->weather) === 'foggy')>🌫️Foggy</option>
                            </select>
                            <x-input-error :messages="$errors->get('weather')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="gear_type_id" value="Gear Type" />
                            <select id="gear_type_id" name="gear_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Select Gear Type --</option>
                                @foreach ($gearTypes as $gear)
                                    <option value="{{ $gear->id }}" @selected(old('gear_type_id', $catch->gear_type_id) == $gear->id)>
                                        {{ $gear->name }}@if($gear->local_name) ({{ $gear->local_name }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('gear_type_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="vessel_name" value="Vessel Name" />
                            <x-text-input id="vessel_name" type="text" name="vessel_name" value="{{ old('vessel_name', $catch->vessel_name) }}" class="mt-1 block w-full" />
                        </div>
                    </div>
                </fieldset>

                <!-- Environmental Impact Section -->
                <fieldset class="border-b pb-8">
                    <legend class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-700 text-sm font-bold">3</span>
                        Bycatch & Discard
                    </legend>
                    <div class="grid gap-6 md:grid-cols-2 mb-6">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <h3 class="font-semibold text-amber-900 mb-4">Bycatch</h3>
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="bycatch_quantity" value="Quantity (kg)" />
                                    <x-text-input id="bycatch_quantity" type="number" step="0.01" name="bycatch_quantity" value="{{ old('bycatch_quantity', $catch->bycatch_quantity) }}" placeholder="Optional" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('bycatch_quantity')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="bycatch_species_ids" value="Species" />
                                    <select id="bycatch_species_ids" name="bycatch_species_ids[]" multiple class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($species as $s)
                                            <option value="{{ $s->id }}" @selected(in_array($s->id, old('bycatch_species_ids', $catch->bycatch_species_ids ?? [])))>{{ $s->common_name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('bycatch_species_ids')" class="mt-1" />
                                    <p class="mt-2 text-xs text-gray-600">Hold Ctrl/Cmd to select multiple</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h3 class="font-semibold text-red-900 mb-4">Discard</h3>
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="discard_quantity" value="Quantity (kg)" />
                                    <x-text-input id="discard_quantity" type="number" step="0.01" name="discard_quantity" value="{{ old('discard_quantity', $catch->discard_quantity) }}" placeholder="Optional" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('discard_quantity')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="discard_species_ids" value="Species" />
                                    <select id="discard_species_ids" name="discard_species_ids[]" multiple class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($species as $s)
                                            <option value="{{ $s->id }}" @selected(in_array($s->id, old('discard_species_ids', $catch->discard_species_ids ?? [])))>{{ $s->common_name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('discard_species_ids')" class="mt-1" />
                                    <p class="mt-2 text-xs text-gray-600">Hold Ctrl/Cmd to select multiple</p>
                                </div>
                                <div>
                                    <x-input-label for="discard_reason" value="Reason" />
                                    <select id="discard_reason" name="discard_reason" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- None --</option>
                                        <option value="too_small" @selected(old('discard_reason', $catch->discard_reason) === 'too_small')>Too Small</option>
                                        <option value="damaged" @selected(old('discard_reason', $catch->discard_reason) === 'damaged')>Damaged</option>
                                        <option value="dead" @selected(old('discard_reason', $catch->discard_reason) === 'dead')>Dead</option>
                                        <option value="species_not_allowed" @selected(old('discard_reason', $catch->discard_reason) === 'species_not_allowed')>Species Not Allowed</option>
                                        <option value="over_quota" @selected(old('discard_reason', $catch->discard_reason) === 'over_quota')>Over Quota</option>
                                        <option value="other" @selected(old('discard_reason', $catch->discard_reason) === 'other')>Other</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('discard_reason')" class="mt-1" />
                                </div>
                                <div id="discard_reason_other_container" class="{{ old('discard_reason', $catch->discard_reason) === 'other' ? '' : 'hidden' }}">
                                    <x-input-label for="discard_reason_other" value="Please specify" />
                                    <x-text-input id="discard_reason_other" type="text" name="discard_reason_other"
                                        value="{{ old('discard_reason_other', $catch->discard_reason_other ?? '') }}" placeholder="Describe the reason..." class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('discard_reason_other')" class="mt-1" />
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Location Section -->
                <fieldset class="border-b pb-8">
                    <legend class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-700 text-sm font-bold">4</span>
                        Location
                    </legend>
                    <div>
                        <x-input-label for="location" value="Location Description" />
                        <x-text-input id="location" type="text" name="location" value="{{ old('location', $catch->location) }}" placeholder="e.g. Zone A, GPS spot, etc." class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('location')" class="mt-1" />
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 mt-6 mb-6">
                        <div>
                            <x-input-label for="latitude" value="Latitude" />
                            <x-text-input id="latitude" type="number" step="0.000001" name="latitude" value="{{ old('latitude', $catch->latitude) }}" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('latitude')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="longitude" value="Longitude" />
                            <x-text-input id="longitude" type="number" step="0.000001" name="longitude" value="{{ old('longitude', $catch->longitude) }}" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('longitude')" class="mt-1" />
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6">
                        <button type="button" id="geoDetectBtn" class="inline-flex px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm transition">📍 My Location</button>
                        <button type="button" id="startWatchBtn" class="inline-flex px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-md shadow-sm transition">📡 Track</button>
                        <button type="button" id="clearLocationBtn" class="inline-flex px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-md transition">✕ Clear</button>
                    </div>
                    <div id="geoStatus" class="text-sm text-gray-600 mb-4"></div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <x-input-label value="📍 Map (Click to set / drag marker)" />
                        </div>
                        <div id="catchMap" class="w-full h-96 rounded-lg border-2 border-gray-300 overflow-hidden relative shadow-sm">
                            <div id="accuracyCircleLegend" class="absolute top-2 left-2 bg-white/90 backdrop-blur px-3 py-1 rounded text-xs text-gray-700 font-medium hidden">Accuracy radius</div>
                        </div>
                        <p class="mt-3 text-xs text-gray-600 leading-relaxed">🗺️ <strong>Map layers:</strong> OpenStreetMap (standard) & Esri (satellite). Coordinates update automatically.</p>
                    </div>
                </fieldset>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex items-center gap-3">
                        <x-primary-button class="px-6">{{ __('Update Catch') }}</x-primary-button>
                        <a href="{{ route('catches.show', $catch) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            #catchMap { min-height: 18rem; }
            .leaflet-container { font: inherit; z-index:0; }
        </style>
        <script id="leaflet-cdn" src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
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
                setTimeout(function(){
                    if(typeof L === 'undefined') {
                        const el = document.getElementById('catchMap');
                        if(el){ el.innerHTML = '<div class="p-4 text-sm text-red-600">Map library failed to load.</div>'; }
                        return;
                    }
                    initCatchMap();
                }, 600);
            } else {
                initCatchMap();
            }
        });

        function geohashEncode(lat, lon, precision = 10){
            const base32 = '0123456789bcdefghjkmnpqrstuvwxyz';
            let latInterval = [-90.0, 90.0];
            let lonInterval = [-180.0, 180.0];
            let hash = '';
            let isEven = true;
            let bit = 0;
            let ch = 0;
            const bits = [16,8,4,2,1];
            while(hash.length < precision){
                if(isEven){
                    const mid = (lonInterval[0] + lonInterval[1]) / 2;
                    if(lon > mid){ ch |= bits[bit]; lonInterval[0] = mid; } else { lonInterval[1] = mid; }
                } else {
                    const mid = (latInterval[0] + latInterval[1]) / 2;
                    if(lat > mid){ ch |= bits[bit]; latInterval[0] = mid; } else { latInterval[1] = mid; }
                }
                isEven = !isEven;
                if(bit < 4){ bit++; } else { hash += base32[ch]; bit = 0; ch = 0; }
            }
            return hash;
        }

        function initCatchMap(){
            if(typeof L === 'undefined'){ return; }
            const latInput = document.getElementById('latitude');
            const lonInput = document.getElementById('longitude');
            const ghInput = document.getElementById('geohash');
            const srcInput = document.getElementById('geo_source');
            const accInput = document.getElementById('geo_accuracy_m');
            const statusEl = document.getElementById('geoStatus');
            const detectBtn = document.getElementById('geoDetectBtn');
            const watchBtn = document.getElementById('startWatchBtn');
            const clearBtn = document.getElementById('clearLocationBtn');
            const accLegend = document.getElementById('accuracyCircleLegend');
            let watchId = null;

            const defaultLat = latInput.value ? parseFloat(latInput.value) : 18.32916452647898;
            const defaultLon = lonInput.value ? parseFloat(lonInput.value) : 121.61577064268877;

            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM contributors' });
            const esri = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: 'Imagery &copy; Esri' });
            const map = L.map('catchMap', { center: [defaultLat, defaultLon], zoom: 10, layers: [osm] });
            L.control.layers({ 'Street': osm, 'Satellite': esri }, null, { position: 'topright' }).addTo(map);

            let marker = L.marker([defaultLat, defaultLon], { draggable: true }).addTo(map);
            if(!latInput.value || !lonInput.value){ marker.setOpacity(0.6); }
            let accuracyCircle = null;

            function updateInputs(lat, lon, opts = {}){
                latInput.value = lat.toFixed(6);
                lonInput.value = lon.toFixed(6);
                ghInput.value = geohashEncode(lat, lon, 10);
                marker.setOpacity(1);
                if(!opts.fromDrag){ marker.setLatLng([lat, lon]); }
                if(opts.accuracy){
                    accInput.value = opts.accuracy.toFixed(2);
                    if(accuracyCircle){ accuracyCircle.remove(); }
                    accuracyCircle = L.circle([lat, lon], { radius: opts.accuracy, color: '#2563eb', weight: 1, fillColor: '#3b82f6', fillOpacity: 0.15 }).addTo(map);
                    accLegend.classList.remove('hidden');
                }
                if(opts.source){ srcInput.value = opts.source; }
                if(statusEl){ statusEl.textContent = srcInput.value ? ('Source: '+srcInput.value + (accInput.value ? ' ('+accInput.value+'m)':'') ) : ''; }
            }
            map.on('click', e => updateInputs(e.latlng.lat, e.latlng.lng, { source: 'click' }));
            marker.on('dragend', e => { const p = e.target.getLatLng(); updateInputs(p.lat, p.lng, { fromDrag:true, source:'drag' }); });

            if(detectBtn){
                detectBtn.addEventListener('click', function(){
                    if(!navigator.geolocation){ return alert('Geolocation not supported'); }
                    detectBtn.disabled = true; const orig = detectBtn.textContent; detectBtn.textContent = 'Locating...';
                    navigator.geolocation.getCurrentPosition(pos => {
                        detectBtn.disabled = false; detectBtn.textContent = orig;
                        updateInputs(pos.coords.latitude, pos.coords.longitude, { accuracy: pos.coords.accuracy, source: 'html5' });
                        map.setView([pos.coords.latitude, pos.coords.longitude], pos.coords.accuracy < 100 ? 14: 12);
                    }, err => {
                        detectBtn.disabled = false; detectBtn.textContent = orig;
                        alert('Location error: ' + err.message);
                    }, { enableHighAccuracy: true, timeout: 15000 });
                });
            }
            if(watchBtn){
                watchBtn.addEventListener('click', function(){
                    if(!navigator.geolocation){ return alert('Geolocation not supported'); }
                    if(watchId !== null){
                        navigator.geolocation.clearWatch(watchId);
                        watchId = null; watchBtn.textContent = 'Track'; watchBtn.classList.remove('bg-red-500','hover:bg-red-600'); watchBtn.classList.add('bg-teal-500','hover:bg-teal-600');
                        statusEl.textContent = 'Tracking stopped';
                        return;
                    }
                    watchBtn.textContent = 'Stop';
                    watchBtn.classList.remove('bg-teal-500','hover:bg-teal-600');
                    watchBtn.classList.add('bg-red-500','hover:bg-red-600');
                    statusEl.textContent = 'Tracking...';
                    watchId = navigator.geolocation.watchPosition(pos => {
                        updateInputs(pos.coords.latitude, pos.coords.longitude, { accuracy: pos.coords.accuracy, source: 'watch' });
                        if(pos.coords.accuracy < 40){ map.setView([pos.coords.latitude, pos.coords.longitude]); }
                    }, err => {
                        alert('Watch error: ' + err.message);
                    }, { enableHighAccuracy: true, maximumAge: 5000 });
                });
            }
            if(clearBtn){
                clearBtn.addEventListener('click', function(){
                    latInput.value=''; lonInput.value=''; ghInput.value=''; srcInput.value=''; accInput.value=''; marker.setOpacity(0.4); if(accuracyCircle){ accuracyCircle.remove(); accuracyCircle=null; }
                    statusEl.textContent=''; accLegend.classList.add('hidden');
                });
            }
            setTimeout(()=>{ map.invalidateSize(); }, 400);

            // Show/hide discard reason text box
            const discardReasonSelect = document.getElementById('discard_reason');
            const discardReasonOtherContainer = document.getElementById('discard_reason_other_container');

            if (discardReasonSelect && discardReasonOtherContainer) {
                discardReasonSelect.addEventListener('change', function() {
                    if (this.value === 'other') {
                        discardReasonOtherContainer.classList.remove('hidden');
                    } else {
                        discardReasonOtherContainer.classList.add('hidden');
                    }
                });
            }
        }
    </script>
</x-app-layout>
