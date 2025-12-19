<x-app-layout>

    <div class="py-6 bg-gradient-to-br from-blue-50 via-white to-indigo-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if($showAdmin)
                <!-- Admin Dashboard Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600 mt-1">Welcome back! Here's your catch overview.</p>
                </div>

                <!-- Key Metrics Grid -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Site Totals Card -->
                    <a href="{{ route('catches.analytics') }}" class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-l-4 border-blue-500">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-blue-50 rounded-bl-full -mr-8 -mt-8 group-hover:bg-blue-100 transition-colors"></div>
                        <div class="relative z-10">
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Catch Weight</h3>
                            @if($siteTotals)
                                <div class="mt-4">
                                    <div class="text-3xl font-bold text-blue-600">{{ number_format($siteTotals->total_qty, 2) }}</div>
                                    <div class="text-sm text-gray-500 mt-1">kg</div>
                                    <div class="text-xs text-gray-400 mt-3 pt-3 border-t border-gray-200">{{ $siteTotals->catches }} catches • {{ $siteTotals->total_count }} fish</div>
                                </div>
                            @else
                                <div class="text-gray-400 italic mt-4">No data yet</div>
                            @endif
                        </div>
                    </a>

                    <!-- Species Card -->
                    <a href="{{ route('catches.index') }}" class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-l-4 border-emerald-500">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 group-hover:bg-emerald-100 transition-colors"></div>
                        <div class="relative z-10">
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Species</h3>
                            <div class="mt-4">
                                <div class="text-3xl font-bold text-emerald-600">{{ $speciesCount ?? '—' }}</div>
                                <div class="text-sm text-gray-500 mt-1">in catalogue</div>
                                <div class="text-xs text-gray-400 mt-3 pt-3 border-t border-gray-200">Catalogued species</div>
                            </div>
                        </div>
                    </a>

                    <!-- Average Catch Weight -->
                    <div class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-l-4 border-amber-500">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-amber-50 rounded-bl-full -mr-8 -mt-8"></div>
                        <div class="relative z-10">
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Avg per Catch</h3>
                            @if($siteTotals && $siteTotals->catches > 0)
                                <div class="mt-4">
                                    <div class="text-3xl font-bold text-amber-600">{{ number_format($siteTotals->total_qty / $siteTotals->catches, 2) }}</div>
                                    <div class="text-sm text-gray-500 mt-1">kg</div>
                                    <div class="text-xs text-gray-400 mt-3 pt-3 border-t border-gray-200">Average weight</div>
                                </div>
                            @else
                                <div class="text-gray-400 italic mt-4">No data</div>
                            @endif
                        </div>
                    </div>

                    <!-- Fish Count Card -->
                    <div class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-l-4 border-rose-500">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-rose-50 rounded-bl-full -mr-8 -mt-8"></div>
                        <div class="relative z-10">
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Fish</h3>
                            @if($siteTotals)
                                <div class="mt-4">
                                    <div class="text-3xl font-bold text-rose-600">{{ number_format($siteTotals->total_count) }}</div>
                                    <div class="text-sm text-gray-500 mt-1">pieces</div>
                                    <div class="text-xs text-gray-400 mt-3 pt-3 border-t border-gray-200">Individual fish / excluding uncountable</div>
                                </div>
                            @else
                                <div class="text-gray-400 italic mt-4">No data</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Row -->
                <div class="grid gap-4 md:grid-cols-3">
                    <!-- Recent Activity -->
                    <div class="rounded-lg bg-white p-6 shadow-md border-t-4 border-indigo-500">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Recent Catches</h3>
                        @if($recentCatches->isNotEmpty())
                            <ul class="space-y-2">
                                @foreach($recentCatches->take(5) as $catch)
                                    <li class="flex items-center justify-between text-sm pb-2 border-b border-gray-100 last:border-0">
                                        <span class="text-gray-700 font-medium">{{ $catch->species?->common_name ?? 'Unknown' }}</span>
                                        <span class="font-semibold text-indigo-500">{{ number_format($catch->quantity, 1) }} kg</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-400 italic text-sm">No catches recorded yet</p>
                        @endif
                    </div>

                    <!-- Monthly Trend -->
                    <div class="rounded-lg bg-white p-6 shadow-md border-t-4 border-emerald-500">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Last 6 Months</h3>
                        @if($monthlyTotals->isNotEmpty())
                            <ul class="space-y-2">
                                @foreach($monthlyTotals->take(6) as $month)
                                    <li class="flex items-center justify-between text-sm pb-2 border-b border-gray-100 last:border-0">
                                        <span class="text-gray-600">{{ $month->ym }}</span>
                                        <span class="font-semibold text-emerald-600">{{ number_format($month->total_qty, 1) }} kg</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-400 italic text-sm">No data available</p>
                        @endif
                    </div>

                    <!-- Quick Actions -->
                    <div class="rounded-lg bg-white p-6 shadow-md border-t-4 border-blue-500">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Quick Actions</h3>
                        <div class="space-y-2">
                            <a href="{{ route('catches.index') }}" class="block px-4 py-2 bg-blue-400 hover:bg-blue-300 rounded transition text-sm font-medium text-center text-white">
                                View All Catches
                            </a>
                            <a href="{{ route('catches.analytics') }}" class="block px-4 py-2 bg-blue-400 hover:bg-blue-300 rounded transition text-sm font-medium text-center text-white">
                                Detailed Analytics
                            </a>
                            <a href="{{ route('admin.zones.index') }}" class="block px-4 py-2 bg-blue-400 hover:bg-blue-300 rounded transition text-sm font-medium text-center text-white">
                                Manage Zones
                            </a>
                            <a href="{{ route('catches.heatmap') }}" class="block px-4 py-2 bg-blue-400 hover:bg-blue-300 rounded transition text-sm font-medium text-center text-white">
                                View Heatmap
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- User Dashboard Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600 mt-1">Track your fishing catches and view forecasts.</p>
                </div>

                <!-- User Stats Grid -->
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Recent Catches Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md border-t-4 border-blue-500">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Recent Catches
                        </h3>
                        @if($recentCatches->isNotEmpty())
                            <ul class="space-y-3">
                                @foreach($recentCatches->take(5) as $c)
                                    <li class="flex items-center justify-between pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                                        <span class="text-gray-700 font-medium">{{ $c->species?->common_name ?? 'Unknown Species' }}</span>
                                        <span class="inline-block px-3 py-1 text-xs bg-blue-100 text-blue-700 font-semibold rounded-full">{{ $c->quantity }} kg.</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-400 italic text-sm">No catches recorded yet</p>
                        @endif
                    </div>

                    <!-- Monthly Totals Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md border-t-4 border-emerald-500">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            Last 6 Months
                        </h3>
                        @if($monthlyTotals->isNotEmpty())
                            <ul class="space-y-2">
                                @foreach($monthlyTotals->take(6) as $m)
                                    <li class="flex items-center justify-between pb-2 border-b border-gray-100 last:border-0">
                                        <span class="text-gray-600 font-medium">{{ $m->ym }}</span>
                                        <div class="flex items-center gap-2">
                                            <div class="w-24 bg-emerald-100 h-2 rounded-full overflow-hidden">
                                                <div class="bg-emerald-500 h-full" style="width: {{ min(100, ($m->total_qty / 200) * 100) }}%"></div>
                                            </div>
                                            <span class="font-semibold text-emerald-600 w-12 text-right">{{ number_format($m->total_qty, 1) }} kg</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-400 italic text-sm">No data available</p>
                        @endif
                    </div>
                </div>
            @endif

            @unless($showAdmin)
            <div id="weather" class="mt-6 p-6 bg-gradient-to-r from-cyan-50 to-blue-50 rounded-lg shadow-md border border-cyan-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                ☁️ Weather & Forecast
                </h3>
                <div class="flex flex-wrap gap-6 items-start">
                    <div class="w-full md:w-64 space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Auto Detect Location</label>
                            <button id="detectBtn" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium shadow-md transition">
                                📍 Detect My Location
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Latitude</label>
                                <input id="latInput" type="number" step="0.001" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="13.412" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Longitude</label>
                                <input id="lonInput" type="number" step="0.001" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="122.562" />
                            </div>
                        </div>
                        <button id="loadWeatherBtn" class="w-full px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg font-medium shadow-md transition">
                            Load Weather
                        </button>
                        <div id="weatherStatus" class="text-xs text-gray-600"></div>
                        <div id="currentWeather" class="hidden border-2 border-blue-200 rounded-lg p-3 text-sm space-y-3 bg-white">
                            <div class="flex items-center gap-3">
                                <img id="cwIcon" class="w-12 h-12" alt="" />
                                <div>
                                    <div id="cwTemp" class="font-bold text-2xl text-gray-800"></div>
                                    <div id="cwCond" class="capitalize text-gray-600"></div>
                                </div>
                            </div>
                            <div id="weatherAdvice" class="hidden text-xs leading-relaxed font-medium px-3 py-2 rounded border-l-4 bg-blue-50"></div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="bg-gray-50 p-2 rounded"><span class="text-gray-500">Humidity</span><br><span class="font-semibold text-gray-700"><span id="cwHum"></span>%</span></div>
                                <div class="bg-gray-50 p-2 rounded"><span class="text-gray-500">Wind</span><br><span class="font-semibold text-gray-700"><span id="cwWind"></span> km/h</span></div>
                                <div class="bg-gray-50 p-2 rounded col-span-2"><span class="text-gray-500">Precipitation</span><br><span class="font-semibold text-gray-700"><span id="cwPrecip"></span> mm</span></div>
                                <div class="text-gray-400 col-span-2 text-[10px]" id="cwSource"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 space-y-4 min-w-[300px]">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Daily Summary (5 Days)</h4>
                            <div id="dailyForecast" class="flex gap-3 overflow-x-auto pb-2 snap-x"></div>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Hourly Forecast (Next 24 Hours)</h4>
                            <div id="hourlyForecast" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2 text-[11px]"></div>
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

        // Load with Aparri location (northernmost city in Cagayan, Philippines)
        window.addEventListener('load', () => {
            latInput.value = '18.3545';
            lonInput.value = '121.6392';
            loadWeather();
        });
    </script>
</x-app-layout>
