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
        // Paginate species list so the UI can show a limited number per page
        $paginated = Species::query()
            ->withCount(['catches as catches_last_30' => function ($q) {
                $q->where('caught_at', '>=', now()->subDays(30));
            }])
            ->paginate(5);

        // Work with the underlying collection for transformation, then re-attach to paginator
        $speciesCollection = $paginated->getCollection();

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

        // If CSV export requested, build a wide-format CSV with Period + one column per species
        if ($request->input('format') === 'csv') {
            // Load all species (include those without catches as well)
            $allSpecies = Species::orderBy('common_name')->get(['id','common_name']);

            // Build map of species id => common_name
            $speciesNames = $allSpecies->pluck('common_name','id')->toArray();

            // Rebuild series map from raw
            $seriesMap = [];
            foreach ($raw as $row) {
                $seriesMap[$row->species_id][$row->ym] = (float) $row->qty_sum;
            }

            // Prepare CSV header: Period, species1, species2, ...
            $header = array_merge(['Period'], array_values($speciesNames));

            $rows = [];
            // months is already built below; but build here since not yet defined
            $monthsList = [];
            for ($i = 0; $i < 12; $i++) {
                $m = $from->copy()->addMonths($i)->format('Y-m');
                $monthsList[] = $m;
            }

            foreach ($monthsList as $ym) {
                $row = [$ym];
                foreach ($allSpecies as $sp) {
                    $row[] = $seriesMap[$sp->id][$ym] ?? 0.0;
                }
                $rows[] = $row;
            }

            $filename = sprintf('seasonal-monthly-by-species-%s.csv', now()->format('YmdHis'));
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ];

            if (app()->runningUnitTests()) {
                $fp = fopen('php://temp', 'r+');
                fputcsv($fp, $header);
                foreach ($rows as $r) {
                    fputcsv($fp, $r);
                }
                rewind($fp);
                $content = stream_get_contents($fp);
                fclose($fp);
                return response($content, 200, $headers);
            }

            $callback = function() use ($header, $rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, $header);
                foreach ($rows as $r) {
                    fputcsv($out, $r);
                }
                fclose($out);
            };
            return response()->stream($callback, 200, $headers);
        }

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

        $payload = $speciesCollection->map(function (Species $s) use ($months, $series, $now) {
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

        // Put transformed payload back into the paginator so JSON includes pagination meta
        $paginated->setCollection($payload);

        return response()->json([
            'generated_at' => $now->toIso8601String(),
            'months' => $months,
            // Return paginator as array to ensure JSON-decoded payload is a PHP array
            'species' => $paginated->toArray(),
        ]);
    }
}
