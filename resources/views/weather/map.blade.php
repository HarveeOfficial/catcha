<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Weather Map</h2>
    </x-slot>
    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="p-4 bg-white shadow rounded border border-gray-200">
                <p class="text-sm text-gray-600 mb-3">Click the map to load current + forecast weather (OpenWeather). Uses 5 min cache.</p>
                <div class="mb-3 flex flex-wrap gap-2 text-xs">
                    <button id="toggleSatellite" class="px-4 py-2 bg-blue-600 text-white rounded">Satellite View</button>
                    <button id="btnDistance" class="px-3 py-1 rounded bg-indigo-500 text-white hover:bg-indigo-600">Measure Distance</button>
                    <button id="btnArea" class="px-3 py-1 rounded bg-green-600 text-white hover:bg-green-700">Measure Area</button>
                    <button id="btnFinish" class="hidden px-3 py-1 rounded bg-amber-600 text-white hover:bg-amber-700">Finish</button>
                    <button id="btnClearMeasure" class="px-3 py-1 rounded bg-gray-500 text-white hover:bg-gray-600">Clear</button>
                    <span id="measureStatus" class="text-gray-500 leading-6"></span>
                </div>
                <div id="map" class="relative w-full h-[520px] rounded bg-gray-200"></div>
                    <div id="measureTooltip" class="hidden absolute z-20 pointer-events-none bg-black/70 text-white text-[10px] px-2 py-1 rounded"></div>
                </div>
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
        // Satellite toggle logic
        let isSatellite = false;
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('toggleSatellite');
            btn.addEventListener('click', function() {
                if (isSatellite) {
                    map.removeLayer(satelliteBase);
                    osmBase.addTo(map);
                } else {
                    map.removeLayer(osmBase);
                    satelliteBase.addTo(map);
                }
                isSatellite = !isSatellite;
                btn.textContent = isSatellite ? 'Standard View' : 'Satellite View';
            });
        });
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

        /* Measurement tools */
        const btnDistance = document.getElementById('btnDistance');
    const btnArea = document.getElementById('btnArea');
    const btnFinish = document.getElementById('btnFinish');
        const btnClear = document.getElementById('btnClearMeasure');
        const measureStatus = document.getElementById('measureStatus');
        const tooltip = document.getElementById('measureTooltip');
        let mode = null; // 'distance' | 'area'
        let tempPoints = [];
        let tempLayer = null;
        let overlays = [];

        function haversine(lat1, lon1, lat2, lon2){
            const R = 6371e3; // m
            const toRad = d=> d*Math.PI/180;
            const dLat = toRad(lat2-lat1);
            const dLon = toRad(lon2-lon1);
            const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLon/2)**2;
            const c = 2*Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R*c; // meters
        }

        function updateTooltip(latlng){
            if(!mode || tempPoints.length===0){ tooltip.classList.add('hidden'); return; }
            const mapPos = map.latLngToContainerPoint(latlng);
            tooltip.style.left = mapPos.x + 12 + 'px';
            tooltip.style.top = mapPos.y + 12 + 'px';
            let content='';
            if(mode==='distance'){
                let dist=0; for(let i=1;i<tempPoints.length;i++){ const a=tempPoints[i-1], b=tempPoints[i]; dist+=haversine(a.lat,a.lng,b.lat,b.lng); }
                content = dist>1000? (dist/1000).toFixed(2)+' km' : dist.toFixed(0)+' m';
            } else if(mode==='area' && tempPoints.length>=3){
                // Approx area using planar approximation (small regions) - convert to meters
                const pts = tempPoints.map(p=>{ const latRad = p.lat*Math.PI/180; const lonRad=p.lng*Math.PI/180; return [lonRad*Math.cos(latRad), latRad]; });
                let area=0; for(let i=0;i<pts.length;i++){ const [x1,y1]=pts[i]; const [x2,y2]=pts[(i+1)%pts.length]; area += x1*y2 - x2*y1; }
                area = Math.abs(area)/2 * 6371e3 * 6371e3; // crude scaling
                content = area>1e6? (area/1e6).toFixed(2)+' km²' : area.toFixed(0)+' m²';
            }
            tooltip.textContent = content; tooltip.classList.remove('hidden');
        }

        map.on('mousemove', e=> updateTooltip(e.latlng));

        function toggleFinish(show){ btnFinish.classList.toggle('hidden', !show); }

        function resetMeasurement(){
            mode=null; tempPoints=[]; if(tempLayer){ map.removeLayer(tempLayer); tempLayer=null; }
            tooltip.classList.add('hidden');
            measureStatus.textContent='';
            toggleFinish(false);
        }
        btnClear.addEventListener('click', resetMeasurement);

        btnDistance.addEventListener('click', ()=>{ resetMeasurement(); mode='distance'; measureStatus.textContent='Click map to add points. Double-click or press Finish.'; toggleFinish(true); });
        btnArea.addEventListener('click', ()=>{ resetMeasurement(); mode='area'; measureStatus.textContent='Click to add polygon vertices. Double-click inside or press Finish.'; toggleFinish(true); });
        btnFinish.addEventListener('click', ()=> finalizeMeasurement());

        function finalizeMeasurement(){
            if(!mode){ return; }
            if(mode==='distance' && tempPoints.length>=2){
                let dist=0; for(let i=1;i<tempPoints.length;i++){ const a=tempPoints[i-1], b=tempPoints[i]; dist+=haversine(a.lat,a.lng,b.lat,b.lng); }
                const label = dist>1000? (dist/1000).toFixed(2)+' km' : dist.toFixed(0)+' m';
                L.marker(tempPoints[tempPoints.length-1], {icon:L.divIcon({className:'bg-indigo-600 text-white text-[10px] px-1.5 py-0.5 rounded shadow', html:label})}).addTo(map);
            } else if(mode==='area' && tempPoints.length>=3){
                const center = tempLayer.getBounds().getCenter();
                // approximate area again
                const pts = tempPoints.map(p=>{ const latRad = p.lat*Math.PI/180; const lonRad=p.lng*Math.PI/180; return [lonRad*Math.cos(latRad), latRad]; });
                let area=0; for(let i=0;i<pts.length;i++){ const [x1,y1]=pts[i]; const [x2,y2]=pts[(i+1)%pts.length]; area += x1*y2 - x2*y1; }
                area = Math.abs(area)/2 * 6371e3 * 6371e3;
                const label = area>1e6? (area/1e6).toFixed(2)+' km²' : area.toFixed(0)+' m²';
                L.marker(center, {icon:L.divIcon({className:'bg-green-600 text-white text-[10px] px-1.5 py-0.5 rounded shadow', html:label})}).addTo(map);
            }
            mode=null; tooltip.classList.add('hidden'); measureStatus.textContent='';
        }

        map.on('click', e=>{
            if(!mode){ return; }
            tempPoints.push(e.latlng);
            if(tempLayer){ map.removeLayer(tempLayer); }
            if(mode==='distance'){
                tempLayer = L.polyline(tempPoints, {color:'#6366f1', weight:3, dashArray:'4 4'}).addTo(map).on('dblclick', e=>{ L.DomEvent.stop(e); finalizeMeasurement(); });
            } else if(mode==='area'){
                tempLayer = L.polygon(tempPoints, {color:'#059669', weight:2, fillColor:'#10b981', fillOpacity:0.2}).addTo(map).on('dblclick', e=>{ L.DomEvent.stop(e); finalizeMeasurement(); });
            }
            updateTooltip(e.latlng);
        });
        map.on('dblclick', e=>{ if(mode){ finalizeMeasurement(); } });
        map.doubleClickZoom.disable();
    </script>
</x-app-layout>
