<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Seasonal Species Trends</h2>
    </x-slot>

    <div class="py-6" x-data="seasonalTrends()" x-init="load()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow rounded p-4 flex flex-col gap-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <h3 class="text-sm font-semibold text-gray-700">Current Month: <span x-text="currentMonth"></span></h3>
                        <span class="text-xs text-gray-400" x-text="generatedAt? 'Updated '+relativeTime(generatedAt): ''"></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <button @click="load()" class="px-3 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50" :disabled="loading">Refresh</button>
                        <div x-show="loading" class="flex items-center gap-1 text-gray-500">
                            <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" class="opacity-25"/><path d="M12 2a10 10 0 0 1 10 10" class="opacity-75"/></svg>
                            Loading...
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b text-xs text-gray-600">
                                <th class="text-left py-2 pr-4">Species</th>
                                <th class="text-left py-2 pr-4">Status</th>
                                <th class="text-left py-2 pr-4">30d Catches</th>
                                <th class="text-left py-2 pr-4">12-Month Qty Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="!loading && !species.length">
                                <tr><td colspan="4" class="py-6 text-center text-gray-400">No species data.</td></tr>
                            </template>
                            <template x-for="sp in species" :key="sp.id">
                                <tr class="border-b last:border-0 hover:bg-indigo-50/40">
                                    <td class="py-2 pr-4 font-medium text-gray-800" x-text="sp.common_name"></td>
                                    <td class="py-2 pr-4">
                                        <span :class="sp.status.in_season ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-0.5 rounded text-xs font-semibold" x-text="sp.status.in_season ? 'In Season' : 'Off Season'"></span>
                                    </td>
                                    <td class="py-2 pr-4 text-gray-700" x-text="sp.recent_catches_30d"></td>
                                    <td class="py-2 pr-4">
                                        <div class="flex items-end gap-0.5 h-12">
                                            <template x-for="point in sp.trend_12m" :key="point.month">
                                                <div class="flex flex-col justify-end w-2" :title="point.month+' qty '+point.qty.toFixed(2)">
                                                    <div class="bg-indigo-500/70 hover:bg-indigo-600 transition rounded-t" :style="'height:'+barHeight(sp.trend_12m, point.qty)+'px'"></div>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                                            <template x-for="(point, idx) in sp.trend_12m" :key="point.month">
                                                <span x-text="monthLabel(point.month)" class="w-2 text-center"></span>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="text-[11px] text-gray-500 leading-relaxed">
                    Interpretation: "In Season" is determined by configured open months/windows. Trend bars show relative monthly total quantity (not scaled across species). For cross-species comparison, consider exporting raw data later.
                </div>
            </div>
        </div>
    </div>

    <script>
        function seasonalTrends(){
            return {
                species: [],
                months: [],
                generatedAt: null,
                loading: false,
                currentMonth: new Date().toLocaleDateString(undefined,{month:'long', year:'numeric'}),
                async load(){
                    this.loading = true;
                    try {
                        const resp = await fetch("{{ route('ai.seasonal-trends') }}", {headers:{'Accept':'application/json'}});
                        if(!resp.ok){ throw new Error('Failed'); }
                        const data = await resp.json();
                        this.species = data.species;
                        this.months = data.months;
                        this.generatedAt = data.generated_at;
                    } catch(e){ console.error(e); }
                    finally { this.loading = false; }
                },
                barHeight(series, value){
                    const max = Math.max(...series.map(p=>p.qty));
                    if(max <= 0){ return 0; }
                    // max bar 48px
                    return Math.round( (value / max) * 48 );
                },
                monthLabel(str){
                    // str is YYYY-MM
                    return str.slice(5,7);
                },
                relativeTime(iso){
                    const d = new Date(iso);
                    const diff = (Date.now() - d.getTime())/1000;
                    if(diff < 60) return Math.floor(diff)+'s ago';
                    if(diff < 3600) return Math.floor(diff/60)+'m ago';
                    if(diff < 86400) return Math.floor(diff/3600)+'h ago';
                    return Math.floor(diff/86400)+'d ago';
                }
            }
        }
    </script>
</x-app-layout>
