<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Record Catch') }}</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-8">
            <form action="{{ route('catches.store') }}" method="post" class="space-y-8" id="catchForm">
                @csrf
                <input type="hidden" name="geohash" id="geohash" value="{{ old('geohash') }}" />
                <input type="hidden" name="geo_source" id="geo_source" value="{{ old('geo_source') }}" />
                <input type="hidden" name="geo_accuracy_m" id="geo_accuracy_m" value="{{ old('geo_accuracy_m') }}" />

                <!-- Catch Basics Section -->
                <fieldset class="border-b pb-8">
                    <legend class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 text-sm font-bold">1</span>
                        Catch Details
                    </legend>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label for="caught_at" value="Caught At" />
                            <x-text-input id="caught_at" type="datetime-local" name="caught_at" value="{{ old('caught_at') }}"
                                class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('caught_at')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="species_id" value="Species" />
                            <div class="space-y-2 mt-1">
                                <select id="category_filter" 
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- All Categories --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                                <select id="species_id" name="species_id"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Unknown --</option>
                                    @foreach ($species as $s)
                                        <option value="{{ $s->id }}" data-category="{{ $s->category }}" @selected(old('species_id') == $s->id)>{{ $s->common_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <x-input-error :messages="$errors->get('species_id')" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid gap-6 md:grid-cols-3 mt-6">
                        <div>
                            <x-input-label for="quantity" value="Quantity (kg)" />
                            <x-text-input id="quantity" type="number" step="0.01" name="quantity"
                                value="{{ old('quantity') }}" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="count" value="Count" />
                            <x-text-input id="count" type="number" name="count" value="{{ old('count') }}"
                                class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="avg_size_cm" value="Avg Size (cm)" />
                            <x-text-input id="avg_size_cm" type="number" step="0.01" name="avg_size_cm"
                                value="{{ old('avg_size_cm') }}" class="mt-1 block w-full" />
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
                            <select id="weather" name="weather"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Select Weather --</option>
                                <option value="sunny" @selected(old('weather') === 'sunny')>☀️Sunny</option>
                                <option value="rainy" @selected(old('weather') === 'rainy')>🌧️Rainy</option>
                                <option value="cloudy" @selected(old('weather') === 'cloudy')>☁️Cloudy</option>
                                <option value="windy" @selected(old('weather') === 'windy')>💨Windy</option>
                                <option value="stormy" @selected(old('weather') === 'stormy')>⛈️Stormy</option>
                                <option value="foggy" @selected(old('weather') === 'foggy')>🌫️Foggy</option>
                            </select>
                            <x-input-error :messages="$errors->get('weather')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="gear_type_id" value="Gear Type" />
                            <select id="gear_type_id" name="gear_type_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Select Gear Type --</option>
                                @foreach ($gearTypes as $gear)
                                    <option value="{{ $gear->id }}" @selected(old('gear_type_id') == $gear->id)>
                                        {{ $gear->name }}@if($gear->local_name) ({{ $gear->local_name }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('gear_type_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="vessel_name" value="Vessel Name" />
                            <x-text-input id="vessel_name" type="text" name="vessel_name"
                                value="{{ old('vessel_name') }}" class="mt-1 block w-full" />
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
                                    <x-text-input id="bycatch_quantity" type="number" step="0.01" name="bycatch_quantity"
                                        value="{{ old('bycatch_quantity') }}" placeholder="Optional" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('bycatch_quantity')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="bycatch_species_ids" value="Species" />
                                    <select id="bycatch_species_ids" name="bycatch_species_ids[]" multiple
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($species as $s)
                                            <option value="{{ $s->id }}" @selected(in_array($s->id, old('bycatch_species_ids', [])))>{{ $s->common_name }}</option>
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
                                    <x-text-input id="discard_quantity" type="number" step="0.01" name="discard_quantity"
                                        value="{{ old('discard_quantity') }}" placeholder="Optional" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('discard_quantity')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="discard_species_ids" value="Species" />
                                    <select id="discard_species_ids" name="discard_species_ids[]" multiple
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($species as $s)
                                            <option value="{{ $s->id }}" @selected(in_array($s->id, old('discard_species_ids', [])))>{{ $s->common_name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('discard_species_ids')" class="mt-1" />
                                    <p class="mt-2 text-xs text-gray-600">Hold Ctrl/Cmd to select multiple</p>
                                </div>
                                <div>
                                    <x-input-label for="discard_reason" value="Reason" />
                                    <select id="discard_reason" name="discard_reason"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- None --</option>
                                        <option value="too_small" @selected(old('discard_reason') === 'too_small')>Too Small</option>
                                        <option value="damaged" @selected(old('discard_reason') === 'damaged')>Damaged</option>
                                        <option value="dead" @selected(old('discard_reason') === 'dead')>Dead</option>
                                        <option value="species_not_allowed" @selected(old('discard_reason') === 'species_not_allowed')>Species Not Allowed</option>
                                        <option value="over_quota" @selected(old('discard_reason') === 'over_quota')>Over Quota</option>
                                        <option value="other" @selected(old('discard_reason') === 'other')>Other</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('discard_reason')" class="mt-1" />
                                </div>
                                <div id="discard_reason_other_container" class="hidden">
                                    <x-input-label for="discard_reason_other" value="Please specify" />
                                    <x-text-input id="discard_reason_other" type="text" name="discard_reason_other"
                                        value="{{ old('discard_reason_other') }}" placeholder="Describe the reason..." class="mt-1 block w-full" />
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
                        <x-text-input id="location" type="text" name="location" value="{{ old('location') }}"
                            placeholder="e.g. Zone A, GPS spot, etc." class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('location')" class="mt-1" />
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 mt-6 mb-6">
                        <div>
                            <x-input-label for="latitude" value="Latitude" />
                            <x-text-input id="latitude" type="number" step="0.000001" name="latitude"
                                value="{{ old('latitude') }}" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('latitude')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="longitude" value="Longitude" />
                            <x-text-input id="longitude" type="number" step="0.000001" name="longitude"
                                value="{{ old('longitude') }}" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('longitude')" class="mt-1" />
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6">
                        <button type="button" id="wayfareBtn"
                            class="inline-flex px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm transition">📍 Start Wayfare</button>
                        <button type="button" id="startWatchBtn"
                            class="inline-flex px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-md shadow-sm transition">📡 Track</button>
                        <button type="button" id="clearLocationBtn"
                            class="inline-flex px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-md transition">✕ Clear</button>
                        <button type="button" id="startWayfareInApp"
                            class="inline-flex px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white text-sm font-medium rounded-md shadow-sm transition ml-auto">📱 Open App</button>
                        <a id="startWayfareHref" href="testapp://?returnUrl={{ urlencode(request()->fullUrl()) }}"
                            class="text-xs text-indigo-600 hover:underline hidden">Deep Link</a>
                    </div>
                    <div id="geoStatus" class="text-sm text-gray-600 mb-4"></div>
                    <span id="wayfareStatus" class="text-sm text-gray-600 font-medium hidden">Wayfare: idle</span>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <x-input-label value="📍 Map (Click to set / drag marker)" />
                        </div>
                        <div id="catchMap"
                            class="w-full h-96 rounded-lg border-2 border-gray-300 overflow-hidden relative shadow-sm">
                            <div id="accuracyCircleLegend"
                                class="absolute top-2 left-2 bg-white/90 backdrop-blur px-3 py-1 rounded text-xs text-gray-700 font-medium hidden">
                                Accuracy radius</div>
                        </div>
                        <p class="mt-3 text-xs text-gray-600 leading-relaxed">🗺️ <strong>Map layers:</strong> OpenStreetMap (standard) & Esri (satellite). Coordinates update automatically. Geohash is computed for spatial grouping.</p>
                    </div>
                </fieldset>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex items-center gap-3">
                        <x-primary-button class="px-6">{{ __('Save Catch') }}</x-primary-button>
                        <a href="{{ route('catches.index') }}"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </div>
                <input type="hidden" name="environmental_data[wayfare_track_json]" id="wayfare_track_json"
                    value="">
                <input type="hidden" name="environmental_data[wayfare_summary]" id="wayfare_summary"
                    value="">
            </form>
        </div>
    </div>
    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            #catchMap,
            #catchShowMap {
                min-height: 18rem;
            }

            .leaflet-container {
                font: inherit;
                z-index: 0;
            }
        </style>
        <script id="leaflet-cdn" src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            window.addEventListener('error', function(e) {
                if (e.target && e.target.id === 'leaflet-cdn') {
                    const alt = document.createElement('script');
                    alt.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js';
                    alt.onload = initCatchMap;
                    document.head.appendChild(alt);
                }
            }, true);
        </script>
    @endonce
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof L === 'undefined') {
                setTimeout(function() {
                    if (typeof L === 'undefined') {
                        const el = document.getElementById('catchMap');
                        if (el) {
                            el.innerHTML =
                                '<div class="p-4 text-sm text-red-600">Map unavailable offline. Geotagging still works.</div>';
                        }
                        initGeoWithoutMap();
                        return;
                    }
                    initCatchMap();
                }, 600);
            } else {
                initCatchMap();
            }
        });

        // Persist current lat/lon as JSON so it survives refresh without internet
        const posStoreKey = 'catcha.position.current';

        function saveCurrentPositionFrom(lat, lon, accuracy, source) {
            try {
                const payload = {
                    lat,
                    lon,
                    accuracy: (accuracy ?? null),
                    source: (source ?? null),
                    ts: Date.now()
                };
                localStorage.setItem(posStoreKey, JSON.stringify(payload));
            } catch (e) {
                /* ignore quota errors */ }
        }

        function loadSavedPosition() {
            try {
                const raw = localStorage.getItem(posStoreKey);
                if (!raw) {
                    return null;
                }
                const obj = JSON.parse(raw);
                if (typeof obj?.lat === 'number' && typeof obj?.lon === 'number') {
                    return obj;
                }
            } catch (e) {
                /* ignore */ }
            return null;
        }

        function clearSavedPosition() {
            try {
                localStorage.removeItem(posStoreKey);
            } catch (e) {
                /* ignore */ }
        }

        function saveFromInputs() {
            const latInput = document.getElementById('latitude');
            const lonInput = document.getElementById('longitude');
            const accInput = document.getElementById('geo_accuracy_m');
            const srcInput = document.getElementById('geo_source');
            const lat = parseFloat(latInput?.value || '');
            const lon = parseFloat(lonInput?.value || '');
            if (Number.isFinite(lat) && Number.isFinite(lon)) {
                saveCurrentPositionFrom(lat, lon, accInput?.value ? parseFloat(accInput.value) : null, srcInput?.value ||
                    null);
            }
        }
        window.addEventListener('offline', saveFromInputs);
        window.addEventListener('beforeunload', saveFromInputs);

        function initGeoWithoutMap() {
            const latInput = document.getElementById('latitude');
            const lonInput = document.getElementById('longitude');
            const ghInput = document.getElementById('geohash');
            const srcInput = document.getElementById('geo_source');
            const accInput = document.getElementById('geo_accuracy_m');
            const statusEl = document.getElementById('geoStatus');
            const detectBtn = document.getElementById('geoDetectBtn');
            const watchBtn = document.getElementById('startWatchBtn');
            const wayfareBtn = document.getElementById('wayfareBtn');
            const wayfareStatus = document.getElementById('wayfareStatus');
            const clearBtn = document.getElementById('clearLocationBtn');
            let watchId = null;

            function geohashEncode(lat, lon, precision = 10) {
                const base32 = '0123456789bcdefghjkmnpqrstuvwxyz';
                let latInterval = [-90.0, 90.0];
                let lonInterval = [-180.0, 180.0];
                let hash = '';
                let isEven = true;
                let bit = 0;
                let ch = 0;
                const bits = [16, 8, 4, 2, 1];
                while (hash.length < precision) {
                    if (isEven) {
                        const mid = (lonInterval[0] + lonInterval[1]) / 2;
                        if (lon > mid) {
                            ch |= bits[bit];
                            lonInterval[0] = mid;
                        } else {
                            lonInterval[1] = mid;
                        }
                    } else {
                        const mid = (latInterval[0] + latInterval[1]) / 2;
                        if (lat > mid) {
                            ch |= bits[bit];
                            latInterval[0] = mid;
                        } else {
                            latInterval[1] = mid;
                        }
                    }
                    isEven = !isEven;
                    if (bit < 4) {
                        bit++;
                    } else {
                        hash += base32[ch];
                        bit = 0;
                        ch = 0;
                    }
                }
                return hash;
            }
            // Attempt to restore last saved position before user acts
            (function restore() {
                const saved = loadSavedPosition();
                if (saved) {
                    latInput.value = saved.lat.toFixed(6);
                    lonInput.value = saved.lon.toFixed(6);
                    if (saved.accuracy != null) {
                        accInput.value = Number(saved.accuracy).toFixed(2);
                    }
                    if (saved.source) {
                        srcInput.value = saved.source;
                    }
                    ghInput.value = geohashEncode(saved.lat, saved.lon, 10);
                    statusEl && (statusEl.textContent = 'Restored offline coordinates');
                }
            })();

            function updateInputs(lat, lon, opts = {}) {
                latInput.value = lat.toFixed(6);
                lonInput.value = lon.toFixed(6);
                ghInput.value = geohashEncode(lat, lon, 10);
                if (opts.accuracy) {
                    accInput.value = opts.accuracy.toFixed(2);
                }
                if (opts.source) {
                    srcInput.value = opts.source;
                }
                if (statusEl) {
                    statusEl.textContent = srcInput.value ? ('Source: ' + srcInput.value + (accInput.value ? ' (' + accInput
                        .value + 'm)' : '')) : '';
                }
                // persist latest
                saveCurrentPositionFrom(lat, lon, opts.accuracy ?? (accInput.value ? parseFloat(accInput.value) : null),
                    opts.source ?? srcInput.value);
            }
            // If the Expo app passed coordinates in the URL, prefill now
            (function applyFromQuery() {
                const q = getQueryLocation?.();
                if (q) {
                    updateInputs(q.lat, q.lon, {
                        accuracy: q.acc ?? undefined,
                        source: q.source || 'expo'
                    });
                    if (q.geohash) {
                        ghInput.value = q.geohash;
                    }
                }
            })();
            detectBtn?.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    return alert('Geolocation not supported');
                }
                detectBtn.disabled = true;
                const orig = detectBtn.textContent;
                detectBtn.textContent = 'Locating...';
                navigator.geolocation.getCurrentPosition(pos => {
                    detectBtn.disabled = false;
                    detectBtn.textContent = orig;
                    updateInputs(pos.coords.latitude, pos.coords.longitude, {
                        accuracy: pos.coords.accuracy,
                        source: 'html5'
                    });
                }, err => {
                    detectBtn.disabled = false;
                    detectBtn.textContent = orig;
                    alert('Location error: ' + err.message);
                }, {
                    enableHighAccuracy: true,
                    timeout: 15000
                });
            });
            watchBtn?.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    return alert('Geolocation not supported');
                }
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                    watchBtn.textContent = 'Track';
                    return;
                }
                watchBtn.textContent = 'Stop';
                watchId = navigator.geolocation.watchPosition(pos => {
                    updateInputs(pos.coords.latitude, pos.coords.longitude, {
                        accuracy: pos.coords.accuracy,
                        source: 'watch'
                    });
                }, err => {
                    alert('Watch error: ' + err.message);
                }, {
                    enableHighAccuracy: true,
                    maximumAge: 5000
                });
            });
            // Minimal Wayfare without map
            const wayfareStoreKey = 'catcha.wayfare.track';
            const wayfareMetaKey = 'catcha.wayfare.meta';
            const wayfare = {
                running: false,
                watchId: null,
                points: [],
                meta: {
                    startedAt: null,
                    stoppedAt: null,
                    total: 0
                }
            };

            function updateWayfareStatus(state) {
                if (!wayfareStatus) {
                    return;
                }
                wayfareStatus.classList.remove('hidden');
                const started = wayfare.meta.startedAt ? new Date(wayfare.meta.startedAt) : null;
                const durationMin = started ? Math.max(0, Math.round(((wayfare.meta.stoppedAt ? new Date(wayfare.meta
                    .stoppedAt) : new Date()) - started) / 60000)) : 0;
                wayfareStatus.textContent =
                    `Wayfare: ${state || (wayfare.running ? 'Running' : 'Idle')} · ${wayfare.meta.total} pts${started ? ' · ' + durationMin + 'm' : ''}`;
            }

            function saveWF() {
                try {
                    localStorage.setItem(wayfareStoreKey, JSON.stringify(wayfare.points));
                    localStorage.setItem(wayfareMetaKey, JSON.stringify(wayfare.meta));
                } catch (e) {}
            }

            function loadWF() {
                try {
                    wayfare.points = JSON.parse(localStorage.getItem(wayfareStoreKey) || '[]');
                    wayfare.meta = JSON.parse(localStorage.getItem(wayfareMetaKey) ||
                        '{"startedAt":null,"stoppedAt":null,"total":0}');
                } catch (e) {
                    wayfare.points = [];
                    wayfare.meta = {
                        startedAt: null,
                        stoppedAt: null,
                        total: 0
                    };
                }
                wayfare.meta.total = wayfare.points.length;
                updateWayfareStatus();
            }

            function haversine(lat1, lon1, lat2, lon2) {
                const toRad = d => d * Math.PI / 180;
                const R = 6371000;
                const dLat = toRad(lat2 - lat1);
                const dLon = toRad(lon2 - lon1);
                const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
                return 2 * R * Math.asin(Math.sqrt(a));
            }
            wayfareBtn?.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    return alert('Geolocation not supported');
                }
                if (wayfare.running) {
                    navigator.geolocation.clearWatch(wayfare.watchId);
                    wayfare.watchId = null;
                    wayfare.running = false;
                    wayfare.meta.stoppedAt = new Date().toISOString();
                    saveWF();
                    wayfareBtn.textContent = '<Start Wayfare>';
                    updateWayfareStatus('Idle');
                    return;
                }
                wayfare.running = true;
                wayfare.meta.startedAt = wayfare.meta.startedAt || new Date().toISOString();
                wayfare.meta.stoppedAt = null;
                wayfareBtn.textContent = '<Stop Wayfare>';
                updateWayfareStatus('Running');
                wayfare.watchId = navigator.geolocation.watchPosition(pos => {
                    const lat = pos.coords.latitude,
                        lon = pos.coords.longitude,
                        acc = pos.coords.accuracy,
                        ts = Date.now();
                    updateInputs(lat, lon, {
                        accuracy: acc,
                        source: 'wayfare'
                    });
                    if (acc && acc > 500) {
                        return;
                    }
                    const last = wayfare.points[wayfare.points.length - 1];
                    if (last) {
                        const d = haversine(last.lat, last.lon, lat, lon);
                        if (d < 5 && ts - last.ts < 15000) {
                            return;
                        }
                    }
                    wayfare.points.push({
                        lat,
                        lon,
                        acc: acc ?? null,
                        ts
                    });
                    wayfare.meta.total = wayfare.points.length;
                    saveWF();
                    updateWayfareStatus('Running');
                }, err => {
                    alert('Wayfare error: ' + err.message);
                }, {
                    enableHighAccuracy: true,
                    maximumAge: 3000,
                    timeout: 20000
                });
            });
            loadWF();
            const form = document.getElementById('catchForm');
            form?.addEventListener('submit', function() {
                try {
                    const payload = {
                        meta: wayfare.meta,
                        points: wayfare.points
                    };
                    document.getElementById('wayfare_track_json').value = JSON.stringify(payload);
                    const km = (function(pts) {
                        if (!pts || pts.length < 2) {
                            return 0;
                        }
                        let dist = 0;
                        for (let i = 1; i < pts.length; i++) {
                            dist += haversine(pts[i - 1].lat, pts[i - 1].lon, pts[i].lat, pts[i].lon);
                        }
                        return dist / 1000;
                    })(wayfare.points);
                    document.getElementById('wayfare_summary').value = km ?
                        `${km.toFixed(2)} km (${wayfare.points.length} pts)` : `${wayfare.points.length} pts`;
                } catch (e) {}
            });
            clearBtn?.addEventListener('click', function() {
                if (confirm('Also clear Wayfare track?')) {
                    localStorage.removeItem(wayfareStoreKey);
                    localStorage.removeItem(wayfareMetaKey);
                    wayfare.points = [];
                    wayfare.meta = {
                        startedAt: null,
                        stoppedAt: null,
                        total: 0
                    };
                    updateWayfareStatus('Idle');
                }
                clearSavedPosition();
            });

            // Explicit button to open the app (no-map flow)
            (function initStartWayfareInApp() {
                const btn = document.getElementById('startWayfareInApp');
                if (!btn) {
                    return;
                }
                btn.addEventListener('click', function() {
                    const lat = parseFloat(latInput.value || '');
                    const lon = parseFloat(lonInput.value || '');
                    const acc = accInput.value ? parseFloat(accInput.value) : null;
                    const gh = ghInput.value || (Number.isFinite(lat) && Number.isFinite(lon) ? geohashEncode(
                        lat, lon, 10) : '');
                    const deep = getAppDeepLink(Number.isFinite(lat) ? lat : undefined, Number.isFinite(lon) ?
                        lon : undefined, gh || undefined, Number.isFinite(acc) ? acc : undefined);
                    const manualLink = document.getElementById('startWayfareHref');
                    if (manualLink) {
                        manualLink.href = deep;
                        manualLink.classList.remove('hidden');
                    }
                    try {
                        window.location.href = deep;
                    } catch (e) {}
                });
            })();
        }

        function geohashEncode(lat, lon, precision = 10) {
            const base32 = '0123456789bcdefghjkmnpqrstuvwxyz';
            let latInterval = [-90.0, 90.0];
            let lonInterval = [-180.0, 180.0];
            let hash = '';
            let isEven = true;
            let bit = 0;
            let ch = 0;
            const bits = [16, 8, 4, 2, 1];
            while (hash.length < precision) {
                if (isEven) {
                    const mid = (lonInterval[0] + lonInterval[1]) / 2;
                    if (lon > mid) {
                        ch |= bits[bit];
                        lonInterval[0] = mid;
                    } else {
                        lonInterval[1] = mid;
                    }
                } else {
                    const mid = (latInterval[0] + latInterval[1]) / 2;
                    if (lat > mid) {
                        ch |= bits[bit];
                        latInterval[0] = mid;
                    } else {
                        latInterval[1] = mid;
                    }
                }
                isEven = !isEven;
                if (bit < 4) {
                    bit++;
                } else {
                    hash += base32[ch];
                    bit = 0;
                    ch = 0;
                }
            }
            return hash;
        }

        // Read location passed from the Expo mobile app via query params
        function getQueryLocation() {
            try {
                const qs = new URLSearchParams(window.location.search);
                const lat = parseFloat(qs.get('latitude') || '');
                const lon = parseFloat(qs.get('longitude') || '');
                if (!Number.isFinite(lat) || !Number.isFinite(lon)) {
                    return null;
                }
                const accStr = qs.get('geo_accuracy_m');
                const acc = accStr != null && accStr !== '' ? parseFloat(accStr) : null;
                const source = qs.get('geo_source') || 'expo';
                const ghParam = qs.get('geohash');
                const geohash = ghParam && ghParam.length ? ghParam : geohashEncode(lat, lon, 10);
                return {
                    lat,
                    lon,
                    acc,
                    source,
                    geohash
                };
            } catch (e) {
                return null;
            }
        }

        // Build the deep link to open the native app's Start Wayfare screen
        function getAppDeepLink(lat, lon, gh, acc) {
            try {
                const qs = new URLSearchParams();
                if (typeof lat === 'number' && Number.isFinite(lat)) {
                    qs.set('latitude', String(lat));
                }
                if (typeof lon === 'number' && Number.isFinite(lon)) {
                    qs.set('longitude', String(lon));
                }
                if (gh) {
                    qs.set('geohash', gh);
                }
                if (typeof acc === 'number' && Number.isFinite(acc)) {
                    qs.set('geo_accuracy_m', String(acc));
                }
                qs.set('returnUrl', window.location.href);
                const base = (new URLSearchParams(window.location.search).get('app_deeplink')) || 'testapp://start-wayfare';
                const query = qs.toString();
                return base + (query ? ('?' + query) : '');
            } catch (e) {
                return 'testapp://start-wayfare';
            }
        }

        function initCatchMap() {
            if (typeof L === 'undefined') {
                return;
            }
            const latInput = document.getElementById('latitude');
            const lonInput = document.getElementById('longitude');
            const ghInput = document.getElementById('geohash');
            const srcInput = document.getElementById('geo_source');
            const accInput = document.getElementById('geo_accuracy_m');
            const statusEl = document.getElementById('geoStatus');
            const detectBtn = document.getElementById('geoDetectBtn');
            const watchBtn = document.getElementById('startWatchBtn');
            const wayfareBtn = document.getElementById('wayfareBtn');
            const wayfareStatus = document.getElementById('wayfareStatus');
            const clearBtn = document.getElementById('clearLocationBtn');
            const accLegend = document.getElementById('accuracyCircleLegend');
            let watchId = null;

            // Restore saved position before initializing map center if available
            (function restore() {
                const saved = loadSavedPosition();
                if (saved) {
                    latInput.value = saved.lat.toFixed(6);
                    lonInput.value = saved.lon.toFixed(6);
                    if (saved.accuracy != null) {
                        accInput.value = Number(saved.accuracy).toFixed(2);
                    }
                    if (saved.source) {
                        srcInput.value = saved.source;
                    }
                    ghInput.value = geohashEncode(saved.lat, saved.lon, 10);
                    statusEl && (statusEl.textContent = 'Restored offline coordinates');
                }
            })();

            const defaultLat = latInput.value ? parseFloat(latInput.value) : 18.32916452647898;
            const defaultLon = lonInput.value ? parseFloat(lonInput.value) : 121.61577064268877;

            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OSM contributors'
            });
            const esri = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Imagery &copy; Esri'
                });
            const map = L.map('catchMap', {
                center: [defaultLat, defaultLon],
                zoom: 10,
                layers: [osm]
            });
            L.control.layers({
                'Street': osm,
                'Satellite': esri
            }, null, {
                position: 'topright'
            }).addTo(map);

            let marker = L.marker([defaultLat, defaultLon], {
                draggable: true
            }).addTo(map);
            if (!latInput.value || !lonInput.value) {
                marker.setOpacity(0.6);
            }
            let accuracyCircle = null;

            function updateInputs(lat, lon, opts = {}) {
                latInput.value = lat.toFixed(6);
                lonInput.value = lon.toFixed(6);
                ghInput.value = geohashEncode(lat, lon, 10);
                marker.setOpacity(1);
                if (!opts.fromDrag) {
                    marker.setLatLng([lat, lon]);
                }
                if (opts.accuracy) {
                    accInput.value = opts.accuracy.toFixed(2);
                    if (accuracyCircle) {
                        accuracyCircle.remove();
                    }
                    accuracyCircle = L.circle([lat, lon], {
                        radius: opts.accuracy,
                        color: '#2563eb',
                        weight: 1,
                        fillColor: '#3b82f6',
                        fillOpacity: 0.15
                    }).addTo(map);
                    accLegend.classList.remove('hidden');
                }
                if (opts.source) {
                    srcInput.value = opts.source;
                }
                if (statusEl) {
                    statusEl.textContent = srcInput.value ? ('Source: ' + srcInput.value + (accInput.value ? ' (' + accInput
                        .value + 'm)' : '')) : '';
                }
                // persist latest
                saveCurrentPositionFrom(lat, lon, opts.accuracy ?? (accInput.value ? parseFloat(accInput.value) : null),
                    opts.source ?? srcInput.value);
            }
            map.on('click', e => updateInputs(e.latlng.lat, e.latlng.lng, {
                source: 'click'
            }));
            marker.on('dragend', e => {
                const p = e.target.getLatLng();
                updateInputs(p.lat, p.lng, {
                    fromDrag: true,
                    source: 'drag'
                });
            });

            // If the Expo app passed coordinates in the URL, prefill now (with map awareness)
            (function applyFromQuery() {
                const q = getQueryLocation?.();
                if (q) {
                    updateInputs(q.lat, q.lon, {
                        accuracy: q.acc ?? undefined,
                        source: q.source || 'expo'
                    });
                    if (q.geohash) {
                        ghInput.value = q.geohash;
                    }
                    map.setView([q.lat, q.lon], 13);
                }
            })();

            if (detectBtn) {
                detectBtn.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        return alert('Geolocation not supported');
                    }
                    detectBtn.disabled = true;
                    const orig = detectBtn.textContent;
                    detectBtn.textContent = 'Locating...';
                    navigator.geolocation.getCurrentPosition(pos => {
                        detectBtn.disabled = false;
                        detectBtn.textContent = orig;
                        updateInputs(pos.coords.latitude, pos.coords.longitude, {
                            accuracy: pos.coords.accuracy,
                            source: 'html5'
                        });
                        map.setView([pos.coords.latitude, pos.coords.longitude], pos.coords.accuracy < 100 ?
                            14 : 12);
                    }, err => {
                        detectBtn.disabled = false;
                        detectBtn.textContent = orig;
                        alert('Location error: ' + err.message);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 15000
                    });
                });
            }
            if (watchBtn) {
                watchBtn.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        return alert('Geolocation not supported');
                    }
                    if (watchId !== null) {
                        navigator.geolocation.clearWatch(watchId);
                        watchId = null;
                        watchBtn.textContent = 'Track';
                        watchBtn.classList.remove('bg-red-500', 'hover:bg-red-600');
                        watchBtn.classList.add('bg-teal-500', 'hover:bg-teal-600');
                        statusEl.textContent = 'Tracking stopped';
                        return;
                    }
                    watchBtn.textContent = 'Stop';
                    watchBtn.classList.remove('bg-teal-500', 'hover:bg-teal-600');
                    watchBtn.classList.add('bg-red-500', 'hover:bg-red-600');
                    statusEl.textContent = 'Tracking...';
                    watchId = navigator.geolocation.watchPosition(pos => {
                        updateInputs(pos.coords.latitude, pos.coords.longitude, {
                            accuracy: pos.coords.accuracy,
                            source: 'watch'
                        });
                        if (pos.coords.accuracy < 40) {
                            map.setView([pos.coords.latitude, pos.coords.longitude]);
                        }
                    }, err => {
                        alert('Watch error: ' + err.message);
                    }, {
                        enableHighAccuracy: true,
                        maximumAge: 5000
                    });
                });
            }
            // Wayfare: offline-capable continuous logging buffered in localStorage
            const wayfareStoreKey = 'catcha.wayfare.track';
            const wayfareMetaKey = 'catcha.wayfare.meta';
            const wayfare = {
                running: false,
                watchId: null,
                points: [],
                meta: {
                    startedAt: null,
                    stoppedAt: null,
                    total: 0
                },
                load() {
                    try {
                        const raw = localStorage.getItem(wayfareStoreKey);
                        const meta = localStorage.getItem(wayfareMetaKey);
                        this.points = raw ? JSON.parse(raw) : [];
                        this.meta = meta ? JSON.parse(meta) : {
                            startedAt: null,
                            stoppedAt: null,
                            total: 0
                        };
                    } catch (e) {
                        this.points = [];
                        this.meta = {
                            startedAt: null,
                            stoppedAt: null,
                            total: 0
                        };
                    }
                    this.meta.total = this.points.length;
                    updateWayfareStatus();
                },
                save() {
                    try {
                        localStorage.setItem(wayfareStoreKey, JSON.stringify(this.points));
                        localStorage.setItem(wayfareMetaKey, JSON.stringify(this.meta));
                    } catch (e) {
                        /* storage may be full; ignore */ }
                },
                start() {
                    if (!navigator.geolocation) {
                        return alert('Geolocation not supported');
                    }
                    if (this.running) {
                        return;
                    }
                    this.running = true;
                    this.meta.startedAt = this.meta.startedAt || new Date().toISOString();
                    this.meta.stoppedAt = null;
                    updateWayfareStatus('Running');
                    wayfareBtn.textContent = '<Stop Wayfare>';
                    wayfareBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                    wayfareBtn.classList.add('bg-red-600', 'hover:bg-red-700');
                    this.watchId = navigator.geolocation.watchPosition(pos => {
                        const lat = pos.coords.latitude;
                        const lon = pos.coords.longitude;
                        const acc = pos.coords.accuracy;
                        const ts = Date.now();
                        // Update inputs with latest position
                        updateInputs(lat, lon, {
                            accuracy: acc,
                            source: 'wayfare'
                        });
                        if (acc && acc > 500) {
                            return;
                        } // skip very inaccurate points
                        const last = this.points[this.points.length - 1];
                        if (last) {
                            const d = haversine(last.lat, last.lon, lat, lon);
                            if (d < 5 && ts - last.ts < 15000) {
                                return;
                            } // skip near-duplicates (<5m or <15s)
                        }
                        this.points.push({
                            lat,
                            lon,
                            acc: acc ?? null,
                            ts
                        });
                        this.meta.total = this.points.length;
                        this.save();
                        updateWayfareStatus('Running');
                    }, err => {
                        alert('Wayfare error: ' + err.message);
                        this.stop();
                    }, {
                        enableHighAccuracy: true,
                        maximumAge: 3000,
                        timeout: 20000
                    });
                },
                stop() {
                    if (this.watchId !== null) {
                        navigator.geolocation.clearWatch(this.watchId);
                        this.watchId = null;
                    }
                    this.running = false;
                    this.meta.stoppedAt = new Date().toISOString();
                    this.save();
                    wayfareBtn.textContent = '<Start Wayfare>';
                    wayfareBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                    wayfareBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                    updateWayfareStatus('Idle');
                },
                clear() {
                    this.points = [];
                    this.meta = {
                        startedAt: null,
                        stoppedAt: null,
                        total: 0
                    };
                    this.save();
                    updateWayfareStatus('Idle');
                }
            };

            function updateWayfareStatus(state) {
                if (!wayfareStatus) {
                    return;
                }
                wayfareStatus.classList.remove('hidden');
                const started = wayfare.meta.startedAt ? new Date(wayfare.meta.startedAt) : null;
                const durationMin = started ? Math.max(0, Math.round(((wayfare.meta.stoppedAt ? new Date(wayfare.meta
                    .stoppedAt) : new Date()) - started) / 60000)) : 0;
                wayfareStatus.textContent =
                    `Wayfare: ${state || (wayfare.running ? 'Running' : 'Idle')} · ${wayfare.meta.total} pts${started ? ' · ' + durationMin + 'm' : ''}`;
            }

            function haversine(lat1, lon1, lat2, lon2) {
                const toRad = d => d * Math.PI / 180;
                const R = 6371000;
                const dLat = toRad(lat2 - lat1);
                const dLon = toRad(lon2 - lon1);
                const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
                return 2 * R * Math.asin(Math.sqrt(a));
            }
            if (wayfareBtn) {
                wayfare.load();
                wayfareBtn.addEventListener('click', function() {
                    if (wayfare.running) {
                        wayfare.stop();
                    } else {
                        wayfare.start();
                    }
                });
            }
            // Attach track to form on submit so it is saved with the catch
            const form = document.getElementById('catchForm');
            form?.addEventListener('submit', function() {
                try {
                    const payload = {
                        meta: wayfare.meta,
                        points: wayfare.points
                    };
                    document.getElementById('wayfare_track_json').value = JSON.stringify(payload);
                    const km = summarizeTrack(wayfare.points);
                    document.getElementById('wayfare_summary').value = km ?
                        `${km.toFixed(2)} km (${wayfare.points.length} pts)` : `${wayfare.points.length} pts`;
                } catch (e) {
                    /* ignore */ }
            });

            function summarizeTrack(points) {
                if (!points || points.length < 2) {
                    return 0;
                }
                let dist = 0;
                for (let i = 1; i < points.length; i++) {
                    dist += haversine(points[i - 1].lat, points[i - 1].lon, points[i].lat, points[i].lon);
                }
                return dist / 1000; // km
            }
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    latInput.value = '';
                    lonInput.value = '';
                    ghInput.value = '';
                    srcInput.value = '';
                    accInput.value = '';
                    marker.setOpacity(0.4);
                    if (accuracyCircle) {
                        accuracyCircle.remove();
                        accuracyCircle = null;
                    }
                    statusEl.textContent = '';
                    accLegend.classList.add('hidden');
                    // Also clear any Wayfare track if user desires a reset
                    if (confirm('Also clear Wayfare track?')) {
                        localStorage.removeItem(wayfareStoreKey);
                        localStorage.removeItem(wayfareMetaKey);
                        if (wayfare) {
                            wayfare.clear?.();
                        }
                    }
                    clearSavedPosition();
                });
            }
            // Explicit button to open the app (map flow)
            (function initStartWayfareInApp() {
                const btn = document.getElementById('startWayfareInApp');
                if (!btn) {
                    return;
                }
                btn.addEventListener('click', function() {
                    const lat = parseFloat(latInput.value || '');
                    const lon = parseFloat(lonInput.value || '');
                    const acc = accInput.value ? parseFloat(accInput.value) : null;
                    const gh = ghInput.value || (Number.isFinite(lat) && Number.isFinite(lon) ? geohashEncode(
                        lat, lon, 10) : '');
                    const deep = getAppDeepLink(Number.isFinite(lat) ? lat : undefined, Number.isFinite(lon) ?
                        lon : undefined, gh || undefined, Number.isFinite(acc) ? acc : undefined);
                    const manualLink = document.getElementById('startWayfareHref');
                    if (manualLink) {
                        manualLink.href = deep;
                        manualLink.classList.remove('hidden');
                    }
                    try {
                        window.location.href = deep;
                    } catch (e) {
                        alert('Could not open the app. Ensure the Catcha app is installed.');
                    }
                });
            })();
            setTimeout(() => {
                map.invalidateSize();
            }, 400);
        }
    </script>
    <script>
        (function() {
            function isMobile() {
                return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || '');
            }

            var btn = document.getElementById('startWayfareInApp');
            if (!btn) {
                return;
            }

            btn.addEventListener('click', function() {
                // Build deep link that returns to this page after starting wayfare
                var ret = encodeURIComponent(window.location.href);
                var deepLink = 'testapp://?returnUrl=' + ret;

                // Attempt to open the app
                // Using location.href is the most reliable for modern mobile browsers
                try {
                    window.location.href = deepLink;
                } catch (e) {}

                // If the app isn't installed, user will still be on this page.
                // After ~1.5s, show a fallback.
                setTimeout(function() {
                    if (document.visibilityState === 'visible') {
                        // Still here → likely app not installed
                        var msg =
                            'Could not open the Catcha app. Please install it on your phone and try again.';

                        // Optionally show store links or an instructions page
                        // Replace these with your real links if you have them:
                        var playStoreUrl =
                        ''; // e.g. 'https://play.google.com/store/apps/details?id=com.your.package'
                        var appStoreUrl = ''; // e.g. 'https://apps.apple.com/app/idXXXXXXXXXX'

                        if (isMobile() && (playStoreUrl || appStoreUrl)) {
                            if (confirm(msg + '\n\nOpen the store?')) {
                                if (/Android/i.test(navigator.userAgent) && playStoreUrl) {
                                    window.location.href = playStoreUrl;
                                } else if (/iPhone|iPad|iPod/i.test(navigator.userAgent) &&
                                    appStoreUrl) {
                                    window.location.href = appStoreUrl;
                                }
                            }
                        } else {
                            alert(msg);
                        }
                    }
                }, 1500);
            });
        })();
    </script>
    <script>
        // Category filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const categoryFilter = document.getElementById('category_filter');
            const speciesSelect = document.getElementById('species_id');
            const discardReasonSelect = document.getElementById('discard_reason');
            const discardReasonOtherContainer = document.getElementById('discard_reason_other_container');

            if (categoryFilter && speciesSelect) {
                // Disable species select initially if no category selected
                speciesSelect.disabled = true;

                categoryFilter.addEventListener('change', function() {
                    const selectedCategory = this.value;
                    const options = speciesSelect.querySelectorAll('option');

                    // Enable/disable based on category selection
                    if (selectedCategory === '') {
                        speciesSelect.disabled = true;
                        speciesSelect.value = '';
                    } else {
                        speciesSelect.disabled = false;
                    }

                    options.forEach(option => {
                        if (option.value === '') {
                            option.style.display = 'block'; // Always show "Unknown"
                            return;
                        }

                        const optionCategory = option.getAttribute('data-category');
                        if (selectedCategory === '' || optionCategory === selectedCategory) {
                            option.style.display = 'block';
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    // Reset species selection if it doesn't match the filter
                    const currentOption = speciesSelect.querySelector('option:checked');
                    if (currentOption && currentOption.style.display === 'none') {
                        speciesSelect.value = '';
                    }
                });
            }

            // Show/hide discard reason text box
            if (discardReasonSelect && discardReasonOtherContainer) {
                discardReasonSelect.addEventListener('change', function() {
                    if (this.value === 'other') {
                        discardReasonOtherContainer.classList.remove('hidden');
                    } else {
                        discardReasonOtherContainer.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</x-app-layout>
