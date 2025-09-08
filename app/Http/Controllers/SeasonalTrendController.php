<?php

namespace App\Http\Controllers;

use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SeasonalTrendController extends Controller
{
    /**
     * Return current seasonal status for all species plus recent catch trend (last 12 months).
     */
    public function __invoke(Request $request)
    {
        $now = Carbon::now();
        $species = Species::query()
            ->withCount(['catches as catches_last_30' => function ($q) {
                $q->where('caught_at', '>=', now()->subDays(30));
            }])
            ->get();

        // Build aggregated monthly catch quantity per species for last 12 months
        $from = $now->copy()->startOfMonth()->subMonths(11);
        // Driver-specific month formatting
        $driver = DB::getDriverName();
        $monthExpr = match ($driver) {
            'mysql','mariadb' => "DATE_FORMAT(caught_at, '%Y-%m')",
            'pgsql' => "TO_CHAR(caught_at, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', caught_at)",
            'sqlsrv' => "FORMAT(caught_at, 'yyyy-MM')",
            default => "DATE_FORMAT(caught_at, '%Y-%m')",
        };
        $raw = DB::table('fish_catches')
            ->selectRaw("species_id, {$monthExpr} as ym, SUM(quantity) as qty_sum, SUM(count) as catch_count")
            ->where('caught_at', '>=', $from)
            ->whereNotNull('species_id')
            ->groupBy('species_id', 'ym')
            ->get();

        $series = [];
        foreach ($raw as $row) {
            $series[$row->species_id][$row->ym] = [
                'qty' => (float) $row->qty_sum,
                'count' => (int) $row->catch_count,
            ];
        }
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $from->copy()->addMonths($i)->format('Y-m');
            $months[] = $m;
        }

        $payload = $species->map(function (Species $s) use ($months, $series, $now) {
            $status = $s->seasonalStatus($now);
            $trend = [];
            foreach ($months as $ym) {
                $trend[] = [
                    'month' => $ym,
                    'qty' => $series[$s->id][$ym]['qty'] ?? 0.0,
                    'count' => $series[$s->id][$ym]['count'] ?? 0,
                ];
            }

            return [
                'id' => $s->id,
                'common_name' => $s->common_name,
                'status' => $status,
                'recent_catches_30d' => $s->catches_last_30,
                'trend_12m' => $trend,
            ];
        });

        return response()->json([
            'generated_at' => $now->toIso8601String(),
            'months' => $months,
            'species' => $payload,
        ]);
    }
}
