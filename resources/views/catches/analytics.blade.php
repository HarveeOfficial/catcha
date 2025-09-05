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
            <h2 class="font-semibold text-gray-700 text-sm mb-2">Gear Breakdown</h2>
            <ul class="space-y-1 text-sm">
                @forelse($gearBreakdown as $g)
                    <li class="flex justify-between"><span>{{ $g->gear_type }}</span> <span class="text-gray-500">{{ number_format($g->qty,2) }} kg / {{ $g->catches }} catches</span></li>
                @empty
                    <li class="text-gray-400 italic">No data</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="p-4 bg-white rounded border shadow">
            <h2 class="font-semibold text-gray-700 text-sm mb-2">Daily (last 14 days)</h2>
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
        </div>
        <div class="p-4 bg-white rounded border shadow">
            <h2 class="font-semibold text-gray-700 text-sm mb-2">Monthly (last 6)</h2>
            <table class="w-full text-xs">
                <thead><tr class="text-left text-gray-500"><th class="py-1">Month</th><th class="py-1">Qty (kg)</th><th class="py-1">Count</th></tr></thead>
                <tbody>
                @forelse($monthlySeries as $m)
                    <tr class="border-t"><td class="py-1">{{ $m->ym }}</td><td class="py-1">{{ number_format($m->qty,2) }}</td><td class="py-1">{{ $m->catch_count }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-gray-400 italic py-2">No data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-app-layout>
