<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catch Analytics</h2>
    </x-slot>
<div class="py-6 max-w-7xl mx-auto space-y-6">

    <div class="grid md:grid-cols-4 gap-4 text-sm">
        <div class="p-4 bg-white rounded border shadow">
            <div class="text-gray-500 text-xs uppercase">Total Catches</div>
            <div class="text-xl font-bold">{{ $totalSummary->catches }}</div>
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <div class="text-gray-500 text-xs uppercase">Total Quantity (kg)</div>
            <div class="text-xl font-bold">{{ number_format($totalSummary->total_qty,2) }}</div>
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <div class="text-gray-500 text-xs uppercase">Total Count (pcs)</div>
            <div class="text-xl font-bold">{{ $totalSummary->total_count }}</div>
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <div class="text-gray-500 text-xs uppercase">Avg Size (cm)</div>
            <div class="text-xl font-bold">{{ $totalSummary->avg_size ? number_format($totalSummary->avg_size,1) : '—' }}</div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="p-4 bg-white rounded border shadow">
            <h2 class="font-semibold text-gray-700 text-sm mb-2">Top Species (by qty)</h2>
            <ul class="space-y-1 text-sm">
                @forelse($topSpecies as $row)
                    <li class="flex justify-between"><span>{{ $row->species?->common_name ?? 'Unknown' }}</span> <span class="text-gray-500">{{ number_format($row->qty_sum,2) }} kg</span></li>
                @empty
                    <li class="text-gray-400 italic">No data</li>
                @endforelse
            </ul>
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-700 text-sm mb-2">Gear Breakdown</h2>
                <div class="text-xs"><a href="?format=csv&series=gear" class="text-sky-600">Download CSV (Gear by species)</a></div>
            </div>
            <ul class="space-y-1 text-sm">
                @forelse($gearBreakdown as $g)
                    <li class="space-y-1">
                        <div class="flex justify-between"><span>{{ $g->gear_type }}</span> <span class="text-gray-500">{{ number_format($g->qty,2) }} kg / {{ $g->catches }} catches</span></div>
                        @php
                            $rows = $gearSpecies[$g->gear_type] ?? collect();
                            $top = $rows->sortByDesc('qty')->take(5);
                        @endphp
                        @if($top->isNotEmpty())
                            <div class="text-xs text-gray-600">
                                @foreach($top as $r)
                                    <div>{{ $r->species?->common_name ?? $r->species_id }}: {{ number_format($r->qty,2) }} kg</div>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @empty
                    <li class="text-gray-400 italic">No data</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="p-4 bg-white rounded border shadow">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-700 text-sm mb-2">Daily (last 14 days)</h2>
                <div class="text-xs"><a href="?format=csv&series=daily&separated=species" class="text-sky-600">Download CSV (Daily by species)</a></div>
            </div>
            <table class="w-full text-xs">
                <thead><tr class="text-left text-gray-500"><th class="py-1">Date</th><th class="py-1">Qty (kg)</th><th class="py-1">Count</th></tr></thead>
                <tbody>
                @forelse($dailySeries as $d)
                    <tr class="border-t"><td class="py-1">{{ $d->d }}</td><td class="py-1">{{ number_format($d->qty,2) }}</td><td class="py-1">{{ $d->catch_count }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-gray-400 italic py-2">No data</td></tr>
                @endforelse
                </tbody>
            </table>
            @if(!empty($dailyBySpecies) && $dailyBySpecies->isNotEmpty())
                <div class="mt-3 text-xs">
                    <div class="text-gray-600 font-medium mb-1">Daily by species</div>
                    <table class="w-full text-xs">
                        <thead><tr class="text-left text-gray-500"><th class="py-1">Date</th><th class="py-1">Species</th><th class="py-1">Qty</th><th class="py-1">Count</th></tr></thead>
                        <tbody>
                        @foreach($dailyBySpecies as $date => $rows)
                            @foreach($rows as $r)
                                <tr class="border-t"><td class="py-1">{{ $date }}</td><td class="py-1">{{ $r->species?->common_name ?? $r->species_id }}</td><td class="py-1">{{ number_format($r->qty,2) }}</td><td class="py-1">{{ $r->catch_count }}</td></tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-700 text-sm mb-2">Monthly (last 6)</h2>
                <div class="text-xs"><a href="?format=csv&series=monthly&separated=species" class="text-sky-600">Download CSV (Monthly by species)</a></div>
            </div>
            <table class="w-full text-xs">
                <thead><tr class="text-left text-gray-500"><th class="py-1">Month</th><th class="py-1">Qty (kg)</th><th class="py-1">Count</th><th class="py-1">Species breakdown</th></tr></thead>
                <tbody>
                @forelse($monthlySeries as $m)
                    <tr class="border-t">
                        <td class="py-1">{{ $m->ym }}</td>
                        <td class="py-1">{{ number_format($m->qty,2) }}</td>
                        <td class="py-1">{{ $m->catch_count }}</td>
                        <td class="py-1 text-xs text-gray-600">
                            @php
                                $rows = $monthlyBySpecies[$m->ym] ?? collect();
                                $top = $rows->sortByDesc('qty')->take(3);
                            @endphp
                            @if($top->isEmpty())
                                <span class="text-gray-400">—</span>
                            @else
                                @foreach($top as $r)
                                    <div>{{ $r->species?->common_name ?? $r->species_id }}: {{ number_format($r->qty,2) }} kg</div>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-gray-400 italic py-2">No data</td></tr>
                @endforelse
                </tbody>
            </table>
            @if(!empty($monthlyBySpecies) && $monthlyBySpecies->isNotEmpty())
                <div class="mt-3 text-xs">
                    <div class="text-gray-600 font-medium mb-1">Monthly by species</div>
                    <table class="w-full text-xs">
                        <thead><tr class="text-left text-gray-500"><th class="py-1">Period</th><th class="py-1">Species</th><th class="py-1">Qty</th><th class="py-1">Count</th></tr></thead>
                        <tbody>
                        @foreach($monthlyBySpecies as $period => $rows)
                            @foreach($rows as $r)
                                <tr class="border-t"><td class="py-1">{{ $period }}</td><td class="py-1">{{ $r->species?->common_name ?? $r->species_id }}</td><td class="py-1">{{ number_format($r->qty,2) }}</td><td class="py-1">{{ $r->catch_count }}</td></tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="p-4 bg-white rounded border shadow">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-700 text-sm mb-2">Annual</h2>
            <div class="text-xs space-x-3">
                <a href="?format=csv&series=annual" class="text-sky-600">Download CSV</a>
                <a href="?format=csv&series=annual&separated=species" class="text-sky-600">Download CSV (Annual by species)</a>
            </div>
        </div>
        <table class="w-full text-xs">
            <thead><tr class="text-left text-gray-500"><th class="py-1">Year</th><th class="py-1">Qty (kg)</th><th class="py-1">Count</th></tr></thead>
            <tbody>
            @forelse($annualSeries as $y)
                <tr class="border-t">
                    <td class="py-1">{{ $y->y }}</td>
                    <td class="py-1">{{ number_format($y->qty,2) }}</td>
                    <td class="py-1">{{ $y->catch_count }}</td>
                    <td class="py-1 text-xs text-gray-600">
                        @php
                            $rows = $annualBySpecies[$y->y] ?? collect();
                            $top = $rows->sortByDesc('qty')->take(3);
                        @endphp
                        @if($top->isEmpty())
                            <span class="text-gray-400">—</span>
                        @else
                            @foreach($top as $r)
                                <div>{{ $r->species?->common_name ?? $r->species_id }}: {{ number_format($r->qty,2) }} kg</div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-gray-400 italic py-2">No data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-app-layout>
