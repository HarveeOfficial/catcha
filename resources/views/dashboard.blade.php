<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if($showAdmin)
                <div class="grid gap-6 md:grid-cols-4">
                    <a href="{{ route('catches.analytics') }}" class="block p-4 bg-white shadow rounded border border-gray-200 hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-300" aria-labelledby="card-site-totals">
                        <h3 id="card-site-totals" class="text-sm font-semibold text-gray-600">Site Totals</h3>
                        @if($siteTotals)
                            <div class="mt-3 text-lg font-bold">{{ number_format($siteTotals->total_qty,2) }} kg</div>
                            <div class="text-xs text-gray-500">{{ $siteTotals->catches }} catches • {{ $siteTotals->total_count }} pcs</div>
                        @else
                            <div class="text-gray-400 italic">No data</div>
                        @endif
                    </a>

                    {{-- <a href="{{ route('profile.edit') }}" class="block p-4 bg-white shadow rounded border border-gray-200 hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-300" aria-labelledby="card-users">
                        <h3 id="card-users" class="text-sm font-semibold text-gray-600">Users</h3>
                        <div class="mt-3 text-lg font-bold">{{ $userCount ?? '—' }}</div>
                        <div class="text-xs text-gray-500">Registered users</div>
                    </a> --}}

                    <a href="{{ route('catches.index') }}" class="block p-4 bg-white shadow rounded border border-gray-200 hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-300" aria-labelledby="card-species">
                        <h3 id="card-species" class="text-sm font-semibold text-gray-600">Species</h3>
                        <div class="mt-3 text-lg font-bold">{{ $speciesCount ?? '—' }}</div>
                        <div class="text-xs text-gray-500">Species in catalogue</div>
                    </a>

                    {{-- <a href="{{ route('guidances.index') }}" class="block p-4 bg-white shadow rounded border border-gray-200 hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-300" aria-labelledby="card-pending-guidances">
                        <h3 id="card-pending-guidances" class="text-sm font-semibold text-gray-600">Pending Guidances</h3>
                        @if($pendingGuidances && $pendingGuidances->isNotEmpty())
                            <ul class="mt-2 space-y-1 text-sm max-h-48 overflow-auto">
                                @foreach($pendingGuidances as $pg)
                                    <li class="flex justify-between"><span class="truncate" title="{{ $pg->title }}">{{ Str::limit($pg->title,28) }}</span><span class="text-gray-400">#{{ $pg->id }}</span></li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-gray-400 italic mt-2">No pending guidances</div>
                        @endif
                    </a> --}}
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="p-4 bg-white shadow rounded border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-600">Recent Catches</h3>
                        <ul class="mt-2 space-y-1 text-sm max-h-48 overflow-auto">
                            @forelse($recentCatches as $c)
                                <li class="flex justify-between">
                                    <span>{{ $c->species?->common_name ?? 'Unknown' }}</span>
                                    <span class="text-gray-500">{{ $c->quantity }} pcs</span>
                                </li>
                            @empty
                                <li class="text-gray-400 italic">None yet</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="p-4 bg-white shadow rounded border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-600">Monthly Totals (qty)</h3>
                        <ul class="mt-2 space-y-1 text-sm">
                            @foreach($monthlyTotals as $m)
                                <li class="flex justify-between">
                                    <span>{{ $m->ym }}</span>
                                    <span class="text-gray-500">{{ $m->total_qty }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    {{-- <div class="p-4 bg-white shadow rounded border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-600">Active Guidances</h3>
                        <ul class="mt-2 space-y-1 text-sm max-h-48 overflow-auto">
                            @forelse($activeGuidances as $g)
                                <li class="flex justify-between">
                                    <span class="truncate" title="{{ $g->title }}">{{ Str::limit($g->title,28) }}</span>
                                    <span class="text-gray-400">#{{ $g->id }}</span>
                                </li>
                            @empty
                                <li class="text-gray-400 italic">No active guidance</li>
                            @endforelse
                        </ul>
                    </div> --}}
                </div>
            @endif

            @unless($showAdmin)
            <div id="weather" class="p-4 bg-white shadow rounded border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Weather (Current & 5 Day Forecast)</h3>
                <div class="flex flex-wrap gap-4 items-start">
                    <div class="w-full md:w-64 space-y-3">
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-gray-600">Auto Detect Location</label>
                            <button id="detectBtn" class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs shadow">Detect</button>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <label class="block text-[10px] uppercase tracking-wide text-gray-500">Lat</label>
                                <input id="latInput" type="number" step="0.001" class="w-full border-gray-300 rounded text-xs bg-white" placeholder="13.412" />
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-wide text-gray-500">Lon</label>
                                <input id="lonInput" type="number" step="0.001" class="w-full border-gray-300 rounded text-xs bg-white" placeholder="122.562" />
                            </div>
                        </div>
                        <button id="loadWeatherBtn" class="w-full px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded text-xs shadow">Load Weather</button>
                        <div id="weatherStatus" class="text-xs text-gray-500"></div>
                        <div id="currentWeather" class="hidden border rounded p-2 text-xs space-y-2 bg-gray-100">
                            <div class="flex items-center gap-2">
                                <img id="cwIcon" class="w-8 h-8" alt="" />
                                <div>
                                    <div id="cwTemp" class="font-semibold text-base"></div>
                                    <div id="cwCond" class="capitalize"></div>
                                </div>
                            </div>
                            <div id="weatherAdvice" class="hidden text-[11px] leading-snug font-medium px-2 py-1 rounded border"></div>
                            <div class="grid grid-cols-2 gap-x-2">
                                <div>Humidity: <span id="cwHum"></span>%</div>
                                <div>Wind: <span id="cwWind"></span> km/h</div>
                                <div>Precip: <span id="cwPrecip"></span> mm</div>
                                <div class="col-span-2 text-[10px] text-gray-400" id="cwSource"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 space-y-4 min-w-[260px]">
                        <div>
                            <h4 class="text-xs font-semibold text-gray-600 mb-1">Daily Summary</h4>
                            <div id="dailyForecast" class="flex gap-2 overflow-x-auto pb-1"></div>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-600 mb-1">Hourly (Next 24)</h4>
                            <div id="hourlyForecast" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2 text-[11px]"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endunless
        </div>
    </div>

    <script>
        const detectBtn = document.getElementById('detectBtn');
        const loadBtn = document.getElementById('loadWeatherBtn');
        const latInput = document.getElementById('latInput');
        const lonInput = document.getElementById('lonInput');
        const statusEl = document.getElementById('weatherStatus');
        const currentBox = document.getElementById('currentWeather');
        const cwIcon = document.getElementById('cwIcon');
        const cwTemp = document.getElementById('cwTemp');
        const cwCond = document.getElementById('cwCond');
        const cwHum = document.getElementById('cwHum');
        const cwWind = document.getElementById('cwWind');
        const cwPrecip = document.getElementById('cwPrecip');
        const cwSource = document.getElementById('cwSource');
        const dailyContainer = document.getElementById('dailyForecast');
        const hourlyContainer = document.getElementById('hourlyForecast');

        function setStatus(msg, warn=false){
            statusEl.textContent = msg;
            statusEl.className = 'text-xs ' + (warn? 'text-red-500':'text-gray-500');
        }

        detectBtn.addEventListener('click', ()=>{
            if(!navigator.geolocation){
                setStatus('Geolocation not supported', true);return;
            }
            setStatus('Detecting location...');
            navigator.geolocation.getCurrentPosition(pos=>{
                latInput.value = pos.coords.latitude.toFixed(3);
                lonInput.value = pos.coords.longitude.toFixed(3);
                setStatus('Location detected. Loading weather...');
                loadWeather();
            }, err=>{
                setStatus('Location error: '+err.message, true);
            }, {timeout:8000});
        });

        loadBtn.addEventListener('click', ()=>loadWeather());

        function loadWeather(){
            const lat = parseFloat(latInput.value); const lon = parseFloat(lonInput.value);
            if(isNaN(lat)||isNaN(lon)){ setStatus('Enter lat & lon', true); return; }
            setStatus('Loading weather...');
            Promise.all([
                fetch(`/weather/current?lat=${lat}&lon=${lon}`).then(r=>r.json()),
                fetch(`/weather/forecast?lat=${lat}&lon=${lon}`).then(r=>r.json())
            ]).then(([current, forecast])=>{
                if(current.error){ setStatus('Current: '+current.error, true); return; }
                if(forecast.error){ setStatus('Forecast: '+forecast.error, true); return; }
                renderCurrent(current);
                renderForecast(forecast);
                setStatus('Updated ' + new Date().toLocaleTimeString());
            }).catch(e=>{
                console.error(e); setStatus('Fetch error', true);
            });
        }

        function renderCurrent(c){
            cwIcon.src = c.icon ? `https://openweathermap.org/img/wn/${c.icon}@2x.png` : '';
            cwTemp.textContent = typeof c.temperature_c === 'number'? Math.round(c.temperature_c)+'°C' : '--';
            cwCond.textContent = c.conditions || '';
            cwHum.textContent = c.humidity_percent ?? '--';
            cwWind.textContent = c.wind_speed_kmh ?? '--';
            cwPrecip.textContent = c.precipitation_mm ?? 0;
            cwSource.textContent = 'Source: '+ (c.source || 'openweather');
            currentBox.classList.remove('hidden');
            applySafety(c);
        }

        function applySafety(c){
            const adviceEl = document.getElementById('weatherAdvice');
            if(!adviceEl){ return; }
            const cond = (c.conditions||'').toLowerCase();
            const wind = c.wind_speed_kmh || 0;
            const severeTerms = ['storm','gale','squall','hurricane','cyclone','thunder','tornado'];
            const severe = severeTerms.some(t=> cond.includes(t));
            let msg = '';
            let safe = true;
            if(!c.wind_speed_kmh && !c.conditions){ adviceEl.classList.add('hidden'); return; }
            if(wind >= 40 || severe){
                safe = false;
                let reasons = [];
                if(wind >= 40){ reasons.push(`wind ${wind} km/h`); }
                if(severe){ reasons.push('severe conditions'); }
                msg = `Caution: Conditions may be unsafe (${reasons.join(' & ')}). Consider postponing or using extra precautions.`;
            } else if(wind >= 25){
                msg = `Moderate wind (${wind} km/h). Operate with caution and monitor changes.`;
            } else {
                msg = 'Favorable conditions for small-scale fishing at the moment.';
            }
            adviceEl.textContent = msg;
            adviceEl.className = `text-[11px] leading-snug font-medium px-2 py-1 rounded border ${safe ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'}`;
            adviceEl.classList.remove('hidden');
        }

        function renderForecast(f){
            // Daily
            dailyContainer.innerHTML='';
            (f.daily||[]).slice(0,5).forEach(d=>{
                const el=document.createElement('div');
                el.className='flex flex-col items-center bg-gray-50 dark:bg-gray-700/40 rounded p-2 min-w-20';
                const date=new Date(d.date+'T00:00:00');
                el.innerHTML=`<div class='text-[10px] uppercase'>${date.toLocaleDateString(undefined,{weekday:'short'})}</div>
                    <img class='w-8 h-8' src='https://openweathermap.org/img/wn/${d.icon}@2x.png' alt=''>
                    <div class='text-[10px] text-center capitalize line-clamp-2'>${d.conditions||''}</div>
                    <div class='font-semibold text-xs mt-1'>${Math.round(d.max_temp_c)}° / ${Math.round(d.min_temp_c)}°</div>
                    <div class='text-[10px] text-gray-500'>P: ${d.total_precip_mm}mm</div>`;
                dailyContainer.appendChild(el);
            });
            // Hourly next 24h
            hourlyContainer.innerHTML='';
            (f.forecast||[]).slice(0,8).forEach(h=>{ // 8 *3h = 24h
                const el=document.createElement('div');
                el.className='border rounded p-1 flex flex-col items-center bg-gray-50 dark:bg-gray-700/40';
                const t=new Date(h.time.replace(' ','T'));
                el.innerHTML=`<div>${t.getHours().toString().padStart(2,'0')}:00</div>
                    <img class='w-8 h-8' src='https://openweathermap.org/img/wn/${h.icon}@2x.png' alt=''>
                    <div class='font-semibold'>${Math.round(h.temperature_c)}°</div>
                    <div class='text-[10px]'>${h.precipitation_mm||0}mm</div>`;
                hourlyContainer.appendChild(el);
            });
        }

        // Optionally load with cached coords if available
        // Could auto-detect on first load
    </script>
</x-app-layout>
