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
        $speciesQuery = Species::query();

        // Optional name search (client-side dropdown or search box may pass 'q')
        $q = $request->input('q');
        // Suggestions endpoint: return lightweight species list for typeahead
        if ($request->boolean('suggest')) {
            $suggestQ = Species::query();
            if (! empty($q)) {
                $suggestQ->where('common_name', 'like', '%'.$q.'%');
            }
            $list = $suggestQ->orderBy('common_name')->limit(10)->get(['id','common_name']);
            return response()->json(['data' => $list]);
        }

        // Full species list endpoint (for dropdown listing all species)
        if ($request->boolean('list')) {
            $listQ = Species::query()->orderBy('common_name');
            if (! empty($q)) {
                $listQ->where('common_name', 'like', '%'.$q.'%');
            }
            $all = $listQ->get(['id','common_name']);
            return response()->json(['data' => $all]);
        }
            $speciesId = $request->input('species_id');
            if (! empty($speciesId)) {
                $speciesQuery->where('id', $speciesId);
            } elseif (! empty($q)) {
                $speciesQuery->where('common_name', 'like', '%'.$q.'%');
            }

        $paginated = $speciesQuery
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

            // If species has no explicit restrictions, infer season from recent catches
            // (simple heuristic): if there were catches within the last 30 days, mark
            // as inferred in-season so users see relevant recent activity instead of
            // a blanket 'Unknown'. This keeps an explicit distinction via
            // status.has_restrictions and status.inferred.
            if (empty($status['has_restrictions'])) {
                $recent = (int) ($s->catches_last_30 ?? 0);
                $inferred = $recent > 0;
                $status['inferred'] = $inferred;
                $status['in_season'] = $inferred;
                $status['label'] = $inferred ? 'inferred_in_season' : 'unknown';
            }
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
