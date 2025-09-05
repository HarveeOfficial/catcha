<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">City Weather</h2>
    </x-slot>
    <div class="py-8 max-w-md mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6" x-data="cityWeather()">
            <form @submit.prevent="search" class="flex gap-2 mb-4">
                <input type="text" x-model="city" placeholder="Enter city name" class="flex-1 border-gray-300 rounded-md px-3 py-2 text-sm" required />
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded">Get Weather</button>
            </form>
            <template x-if="error">
                <p class="text-sm text-red-600" x-text="error"></p>
            </template>
            <div x-show="loading" class="text-xs text-gray-500 flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-indigo-500" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                Fetching...
            </div>
            <div x-show="result" x-cloak class="mt-4 space-y-2">
                <h3 class="text-lg font-semibold" x-text="result.city + (result.country ? ', ' + result.country : '')"></h3>
                <div class="flex items-center gap-3">
                    <template x-if="result.icon">
                        <img :src="`https://openweathermap.org/img/wn/${result.icon}@2x.png`" :alt="result.conditions" class="h-12 w-12" />
                    </template>
                    <p class="text-2xl" x-text="result.temperature_c !== null ? result.temperature_c + '°C' : '—' "></p>
                </div>
                <p class="text-sm capitalize" x-text="result.conditions"></p>
                <p class="text-xs text-gray-500" x-text="result.time ? 'As of ' + result.time : ''"></p>
                <!-- Safety advisory -->
                <template x-if="advice">
                    <div :class="advice.safe ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'" class="text-xs border rounded p-2">
                        <span class="font-semibold" x-text="advice.safe ? 'Generally Safe:' : 'Caution:'"></span>
                        <span x-text="advice.message"></span>
                    </div>
                </template>
                <div class="grid grid-cols-2 gap-2 text-xs mt-2">
                    <div><span class="text-gray-500">Humidity:</span> <span x-text="result.humidity_percent != null ? result.humidity_percent + '%' : '—'"></span></div>
                    <div><span class="text-gray-500">Wind:</span> <span x-text="result.wind_speed_kmh != null ? result.wind_speed_kmh + ' km/h' : '—'"></span> <span x-text="result.wind_dir_deg != null ? result.wind_dir_deg + '°' : ''"></span></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
function cityWeather(){
  return {
    city:'',
    loading:false,
    error:null,
    result:null,
    advice:null,
    search(){
      this.error=null; this.result=null; this.loading=true;
      fetch(`{{ route('weather.city') }}?q=${encodeURIComponent(this.city)}`, {headers:{'Accept':'application/json'}})
        .then(r=>r.json())
                .then(j=>{
                    if(j.error){ this.error=j.error; return; }
                    this.result=j;
                    this.advice = this.evaluateSafety(j);
        })
        .catch(()=> this.error='Request failed')
        .finally(()=> this.loading=false);
        },
        evaluateSafety(w){
            // Simple heuristic thresholds (can be refined):
            // Unsafe if wind >= 40 km/h, or severe conditions keywords detected.
            const severeTerms = ['storm','gale','squall','hurricane','cyclone','thunder','tornado'];
            const cond = (w.conditions||'').toLowerCase();
            const highWind = (w.wind_speed_kmh||0) >= 40; // moderate caution threshold
            const severe = severeTerms.some(t=> cond.includes(t));
            if(!w.wind_speed_kmh && !w.conditions){
                return null; // insufficient data
            }
            if(highWind || severe){
                let reasons = [];
                if(highWind){ reasons.push(`wind ${w.wind_speed_kmh} km/h`); }
                if(severe){ reasons.push('severe weather conditions'); }
                return {safe:false, message:`Conditions may be unsafe for small vessels (${reasons.join(' & ')}). Consider postponing or using extra caution.`};
            }
            // Additional mild advisory if wind 25-39
            if((w.wind_speed_kmh||0) >= 25){
                return {safe:true, message:`Moderate wind (${w.wind_speed_kmh} km/h). Check gear and monitor updates.`};
            }
            return {safe:true, message:'Favorable conditions for small-scale fishing based on current wind & reported weather.'};
        }
  }
}
</script>
@endpush