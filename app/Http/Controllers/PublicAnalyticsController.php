<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use Illuminate\Support\Facades\DB;

class PublicAnalyticsController extends Controller
{
    public function __invoke()
    {
        $base = FishCatch::query();

        $driver = DB::getDriverName();
        $dateExprDay = match ($driver) {
            'mysql','mariadb','pgsql','sqlite' => 'DATE(caught_at)',
            'sqlsrv' => 'CAST(caught_at AS date)',
            default => 'DATE(caught_at)'
        };
        $dateExprMonth = match ($driver) {
            'mysql','mariadb' => "DATE_FORMAT(caught_at, '%Y-%m')",
            'pgsql' => "TO_CHAR(caught_at, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', caught_at)",
            'sqlsrv' => "FORMAT(caught_at, 'yyyy-MM')",
            default => "DATE_FORMAT(caught_at, '%Y-%m')"
        };

        $totalSummary = (clone $base)->selectRaw('COUNT(*) as catches, COALESCE(SUM(quantity),0) as total_qty, COALESCE(SUM(count),0) as total_count, AVG(avg_size_cm) as avg_size')->first();

        $topSpecies = (clone $base)
            ->selectRaw('species_id, COALESCE(SUM(quantity),0) as qty_sum')
            ->whereNotNull('species_id')
            ->groupBy('species_id')
            ->orderByDesc('qty_sum')
            ->limit(5)
            ->with('species:id,common_name')
            ->get();

        $dailySeries = (clone $base)
            ->selectRaw("{$dateExprDay} as d, SUM(quantity) as qty, SUM(count) as catch_count")
            ->groupBy('d')
            ->orderBy('d', 'desc')
            ->limit(14)
            ->get();

        $monthlySeries = (clone $base)
            ->selectRaw("{$dateExprMonth} as ym, SUM(quantity) as qty, SUM(count) as catch_count")
            ->groupBy('ym')
            ->orderBy('ym', 'desc')
            ->limit(6)
            ->get();

        $gearBreakdown = (clone $base)
            ->selectRaw('gear_type, COUNT(*) as catches, SUM(quantity) as qty')
            ->whereNotNull('gear_type')
            ->groupBy('gear_type')
            ->orderByDesc('qty')
            ->get();

        return view('analytics.public', [
            'totalSummary' => $totalSummary,
            'topSpecies' => $topSpecies,
            'dailySeries' => $dailySeries,
            'monthlySeries' => $monthlySeries,
            'gearBreakdown' => $gearBreakdown,
        ]);
    }
}
