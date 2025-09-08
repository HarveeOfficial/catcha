<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Weather Map</h2>
    </x-slot>
    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="p-4 bg-white shadow rounded border border-gray-200">
                <p class="text-sm text-gray-600 mb-2">Click the map to load current + forecast weather (OpenWeather). Uses 5 min cache.</p>
                <div id="map" class="w-full h-[520px] rounded bg-gray-200"></div>
            </div>
            <div id="mapWeatherPanel" class="hidden p-4 bg-white shadow rounded border border-gray-200 text-sm">
                <div class="flex flex-wrap gap-6">
                    <div id="mwCurrent" class="space-y-1"></div>
                    <div>
                        <h4 class="font-semibold text-xs text-gray-600 mb-1">Daily</h4>
                        <div id="mwDaily" class="flex gap-2 overflow-x-auto"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const map = L.map('map').setView([12.5,123.5],6);
        // Base layers
        const osmBase = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        const satelliteBase = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: 'Imagery &copy; <a href="https://www.esri.com/">Esri</a>, Earthstar Geographics'
        });

        // Overlays (OpenWeather Clouds)
        const owLayer = L.tileLayer('https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid={{ config('services.openweather.key') }}',{opacity:0.55});
        owLayer.addTo(map); // default visible

        // Layer control
        L.control.layers({
            'OSM': osmBase,
            'Satellite': satelliteBase,
        }, {
            'Clouds': owLayer,
        }, {position: 'topright', collapsed: true}).addTo(map);
        let marker;
        map.on('click', e=>{
            const {lat,lng} = e.latlng;
            if(marker){ marker.setLatLng(e.latlng); } else { marker = L.marker(e.latlng).addTo(map); }
            loadWeather(lat,lng);
        });
    function loadWeather(lat,lon){
            Promise.all([
                fetch(`/weather/current?lat=${lat}&lon=${lon}`).then(r=>r.json()),
                fetch(`/weather/forecast?lat=${lat}&lon=${lon}`).then(r=>r.json())
            ]).then(([c,f])=>{ if(!c.error && !f.error){ render(c,f);} });
        }
        function render(c,f){
            const panel=document.getElementById('mapWeatherPanel'); panel.classList.remove('hidden');
            document.getElementById('mwCurrent').innerHTML = `
                <div class='font-semibold'>Lat ${c.latitude}, Lon ${c.longitude}</div>
                <div class='text-2xl font-bold'>${Math.round(c.temperature_c)}°C</div>
                <div class='capitalize'>${c.conditions||''}</div>
                <div class='text-xs text-gray-500'>Wind ${c.wind_speed_kmh||'--'} km/h | Hum ${c.humidity_percent||'--'}%</div>
                <div class='text-[10px] text-gray-400'>Source: ${c.source}</div>`;
            const daily = (f.daily||[]).slice(0,5);
            const container=document.getElementById('mwDaily'); container.innerHTML='';
            daily.forEach(d=>{
                const el=document.createElement('div'); el.className='flex flex-col items-center bg-gray-50 rounded p-2 min-w-16';
                el.innerHTML=`<div class='text-[10px] uppercase'>${new Date(d.date+'T00:00:00').toLocaleDateString(undefined,{weekday:'short'})}</div>
                    <img class='w-8 h-8' src='https://openweathermap.org/img/wn/${d.icon}@2x.png'>
                    <div class='font-semibold text-xs mt-1'>${Math.round(d.max_temp_c)}°/${Math.round(d.min_temp_c)}°</div>`;
                container.appendChild(el);
            });
        }
    </script>
</x-app-layout>
