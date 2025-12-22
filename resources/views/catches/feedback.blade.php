<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catch Feedback</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Catch Details</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="font-medium text-gray-600">Caught At</dt><dd>{{ $catch->caught_at->format('Y-m-d H:i') }}</dd>
                <dt class="font-medium text-gray-600">Species</dt><dd>{{ optional($catch->species)->common_name ?? '—' }}</dd>
                <dt class="font-medium text-gray-600">Quantity (kg)</dt><dd>{{ $catch->quantity }}</dd>
                <dt class="font-medium text-gray-600">Count</dt><dd>{{ $catch->count }}</dd>
                <dt class="font-medium text-gray-600">Location</dt><dd>{{ $catch->location }}</dd>
                <dt class="font-medium text-gray-600">Gear Type</dt><dd>{{ $catch->gearType?->name ?? '—' }}</dd>
                @php($w = $catch->weather)
                @if($w)
                    <dt class="font-medium text-gray-600">Weather Temp</dt><dd>{{ $w['temperature_c'] ?? '—' }} °C</dd>
                    <dt class="font-medium text-gray-600">Wind</dt><dd>{{ $w['wind_speed_kmh'] ?? '—' }} km/h @if(isset($w['wind_dir_deg'])) {{ $w['wind_dir_deg'] }}° @endif</dd>
                    <dt class="font-medium text-gray-600">Humidity</dt><dd>{{ $w['humidity_percent'] ?? '—' }}%</dd>
                    <dt class="font-medium text-gray-600">Recorded</dt><dd>{{ $w['time'] ?? '—' }}</dd>
                @endif
            </dl>
        </div>

        @if ($catch->latitude && $catch->longitude)
        <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Map Location</h3>
            <div id="feedbackMap" class="h-80 w-full rounded border border-gray-300 overflow-hidden"></div>
            <p class="mt-2 text-xs text-gray-500">Lat: {{ number_format($catch->latitude, 6) }} | Lon: {{ number_format($catch->longitude, 6) }}</p>
        </div>
        @endif

    <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Feedback</h3>
            @if (session('status'))
                <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
            @endif
            <div class="space-y-4">
                @forelse($feedbacks as $fb)
                    <div class="border rounded p-3 space-y-2">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>By {{ $fb->expert->name }}</span>
                            <span class="flex items-center gap-2">
                                <span>{{ $fb->created_at->diffForHumans() }}</span>
                                @if(auth()->id() === $fb->expert_id)
                                    <form method="POST" action="{{ route('catches.feedback.destroy',$fb) }}" onsubmit="return confirm('Delete this feedback?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline" type="submit">Delete</button>
                                    </form>
                                @endif
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            @if($fb->approved)
                                <span class="inline-block px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded">Approved</span>
                            @endif
                        </div>
                        <p class="text-sm whitespace-pre-line">{{ $fb->comments }}</p>
                        <div class="flex flex-wrap items-center gap-4 text-xs">
                            <div class="flex items-center gap-1">
                                @php($liked = $fb->likes->where('user_id',auth()->id())->count()>0)
                                @if($liked)
                                    <form method="POST" action="{{ route('catches.feedback.unlike',$fb) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-indigo-600 hover:underline" type="submit">Unlike</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('catches.feedback.like',$fb) }}">
                                        @csrf
                                        <button class="text-indigo-600 hover:underline" type="submit">Like</button>
                                    </form>
                                @endif
                                <span class="text-gray-500">({{ $fb->likes->count() }})</span>
                            </div>
                            <button type="button" class="text-gray-600 hover:underline" onclick="navigator.clipboard.writeText('{{ addslashes(Str::limit($fb->comments,140)) }}');this.textContent='Copied';setTimeout(()=>this.textContent='Share',1500);">Share</button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No feedback yet.</p>
                @endforelse
            </div>
        </div>

        @if(auth()->user()->isExpert() || auth()->user()->isAdmin())
        <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Add Feedback</h3>
            <form method="post" action="{{ route('catches.feedback.store', $catch) }}" class="space-y-4">
                @csrf
                <div class="flex items-center space-x-2">
                    <input id="approved" name="approved" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <label for="approved" class="text-sm font-medium text-gray-700">Approved</label>
                </div>
                <div>
                    <x-input-label for="comments" value="Comments" />
                    <textarea id="comments" name="comments" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="4"></textarea>
                    <x-input-error :messages="$errors->get('comments')" class="mt-2" />
                </div>

                <div>
                    <x-primary-button>Submit Feedback</x-primary-button>
                </div>
            </form>
        </div>
        @endif
    </div>
    
    @once
    <script>
        (function() {
            const lat = {{ $catch->latitude ?? 'null' }};
            const lon = {{ $catch->longitude ?? 'null' }};
            const hasCoords = lat !== null && lon !== null;
            
            if (!hasCoords) return;
            
            const mapContainer = document.getElementById('feedbackMap');
            if (!mapContainer) return;
            
            if (typeof L === 'undefined') {
                setTimeout(arguments.callee, 100);
                return;
            }
            
            const center = [lat, lon];
            const map = L.map('feedbackMap', {
                center: center,
                zoom: 11
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
                                let popupText = `<strong>${zone.name}</strong>`;
                                layer.bindPopup(popupText);
                            }
                        }).addTo(map);
                    });
                })
                .catch(err => console.error('Error loading zones:', err));
            
            L.marker([lat, lon]).addTo(map).bindPopup('Catch Location');
        })();
    </script>
    @endonce
</x-app-layout>
