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
                        <a :href="'{{ route('ai.seasonal-trends') }}?format=csv'" class="px-3 py-1 rounded border text-sky-600 hover:bg-sky-50">Download CSV</a>
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
                                                <div class="flex flex-col justify-end w-2" :title="point.month + ' ' + point.qty.toFixed(2) + ' kg '">
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
                <div class="flex items-center justify-between">
                    <div class="text-[11px] text-gray-500 leading-relaxed max-w-prose">
                    Interpretation: "In Season" is determined by configured open months/windows. Trend bars show relative monthly total quantity (not scaled across species). For cross-species comparison, consider exporting raw data later.
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <button x-on:click="prevPage()" class="px-2 py-1 rounded border" :disabled="!hasPrev">Prev</button>
                        <div class="flex items-center gap-1">
                            <template x-for="p in visiblePages" :key="p.key">
                                <button
                                    x-show="p.type === 'page'"
                                    x-on:click="goTo(p.page)"
                                    class="px-2 py-1 rounded border text-xs"
                                    :class="{'bg-indigo-600 text-white': p.page === currentPage, 'text-gray-700': p.page !== currentPage}"
                                    x-text="p.page"></button>
                            </template>
                            <template x-for="p in visiblePages" :key="p.key + '-ellipsis'">
                                <span x-show="p.type === 'ellipsis'" class="px-2 text-xs text-gray-500">&hellip;</span>
                            </template>
                        </div>
                        <div class="text-xs">Page <span x-text="currentPage"></span> of <span x-text="lastPage"></span></div>
                        <button x-on:click="nextPage()" class="px-2 py-1 rounded border" :disabled="!hasNext">Next</button>
                    </div>
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
                // pagination state
                currentPage: 1,
                lastPage: 1,
                hasPrev: false,
                hasNext: false,
                // computed visible pages for numbered pagination
                get visiblePages(){
                    const pages = [];
                    const total = this.lastPage || 1;
                    const cur = this.currentPage || 1;
                    // Always show first and last, plus a window around current
                    const window = 2; // pages either side

                    const addPage = (n) => pages.push({type:'page', page:n, key:'p'+n});
                    const addEllipsis = (k) => pages.push({type:'ellipsis', key:'e'+k});

                    if (total <= 7) {
                        for (let i=1;i<=total;i++) addPage(i);
                        return pages;
                    }

                    // always show first
                    addPage(1);

                    let left = Math.max(2, cur - window);
                    let right = Math.min(total - 1, cur + window);

                    if (left > 2) addEllipsis('l');

                    for (let i = left; i <= right; i++) addPage(i);

                    if (right < total - 1) addEllipsis('r');

                    // always show last
                    addPage(total);

                    return pages;
                },
                async load(){
                    this.loading = true;
                    try {
                        const resp = await fetch("{{ route('ai.seasonal-trends') }}", {headers:{'Accept':'application/json'}});
                        if(!resp.ok){ throw new Error('Failed'); }
                        const data = await resp.json();
                        // API now returns a paginator for 'species'
                        if (data.species && data.species.data) {
                            this.species = data.species.data;
                            this.currentPage = data.species.current_page || 1;
                            this.lastPage = data.species.last_page || 1;
                            this.hasPrev = data.species.prev_page_url !== null;
                            this.hasNext = data.species.next_page_url !== null;
                        } else {
                            this.species = data.species || [];
                        }
                        this.months = data.months;
                        this.generatedAt = data.generated_at;
                    } catch(e){ console.error(e); }
                    finally { this.loading = false; }
                },
                async goTo(page){
                    this.loading = true;
                    try {
                        const url = new URL("{{ route('ai.seasonal-trends') }}", window.location.origin);
                        url.searchParams.set('page', page);
                        const resp = await fetch(url.toString(), {headers:{'Accept':'application/json'}});
                        if(!resp.ok){ throw new Error('Failed'); }
                        const data = await resp.json();
                        if (data.species && data.species.data) {
                            this.species = data.species.data;
                            this.currentPage = data.species.current_page || 1;
                            this.lastPage = data.species.last_page || 1;
                            this.hasPrev = data.species.prev_page_url !== null;
                            this.hasNext = data.species.next_page_url !== null;
                        }
                        this.months = data.months;
                        this.generatedAt = data.generated_at;
                    } catch(e){ console.error(e); }
                    finally { this.loading = false; }
                },
                prevPage(){ if(this.hasPrev) this.goTo(this.currentPage - 1); },
                nextPage(){ if(this.hasNext) this.goTo(this.currentPage + 1); },
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
