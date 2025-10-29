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
        $dateExprYear = match ($driver) {
            'mysql','mariadb' => "DATE_FORMAT(caught_at, '%Y')",
            'pgsql' => "TO_CHAR(caught_at, 'YYYY')",
            'sqlite' => "strftime('%Y', caught_at)",
            'sqlsrv' => "FORMAT(caught_at, 'yyyy')",
            default => "DATE_FORMAT(caught_at, '%Y')"
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

        // daily by species (long format): d, species_id, qty, catch_count
        $dailyBySpecies = (clone $base)
            ->whereNotNull('species_id')
            ->selectRaw("{$dateExprDay} as d, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count")
            ->groupBy('d', 'species_id')
            ->orderBy('d', 'desc')
            ->get()
            ->groupBy('d');

        $monthlySeries = (clone $base)
            ->selectRaw("{$dateExprMonth} as ym, SUM(quantity) as qty, SUM(count) as catch_count")
            ->groupBy('ym')
            ->orderBy('ym', 'desc')
            ->limit(6)
            ->get();

        // monthly by species (long format): ym, species_id, qty, catch_count
        $monthlyBySpecies = (clone $base)
            ->whereNotNull('species_id')
            ->selectRaw("{$dateExprMonth} as ym, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count")
            ->groupBy('ym', 'species_id')
            ->orderBy('ym', 'desc')
            ->get()
            ->groupBy('ym');

        $annualSeries = (clone $base)
            ->selectRaw("{$dateExprYear} as y, SUM(quantity) as qty, SUM(count) as catch_count")
            ->groupBy('y')
            ->orderBy('y', 'desc')
            ->get();

        // annual by species (long format): y, species_id, qty, catch_count
        $annualBySpecies = (clone $base)
            ->whereNotNull('species_id')
            ->selectRaw("{$dateExprYear} as y, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count")
            ->groupBy('y', 'species_id')
            ->orderBy('y', 'desc')
            ->get()
            ->groupBy('y');

        $gearBreakdown = (clone $base)
            ->selectRaw('gear_type, COUNT(*) as catches, SUM(quantity) as qty')
            ->whereNotNull('gear_type')
            ->groupBy('gear_type')
            ->orderByDesc('qty')
            ->get();

        $zoneBreakdown = (clone $base)
            ->selectRaw('zone_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count, COUNT(*) as catches')
            ->whereNotNull('zone_id')
            ->groupBy('zone_id')
            ->orderByDesc('qty')
            ->with('zone')
            ->get();

        // zone by species (long format): zone_id, species_id, qty, catch_count
        $zoneBySpecies = (clone $base)
            ->whereNotNull('zone_id')
            ->whereNotNull('species_id')
            ->selectRaw('zone_id, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count')
            ->groupBy('zone_id', 'species_id')
            ->orderByDesc('qty')
            ->get()
            ->groupBy('zone_id');

        // Support CSV export: ?format=csv&series=annual|monthly|daily
        // also support monthly separated by species: ?format=csv&series=monthly&separated=species
        if (request()->input('format') === 'csv') {
            $series = request()->input('series', 'monthly');
            $separated = request()->input('separated') === 'species';

            // If user requests daily separated by species, export long-format daily-by-species CSV
            if ($series === 'daily' && $separated) {
                $species = \App\Models\Species::orderBy('common_name')->get(['id', 'common_name']);
                $days = $dailySeries->sortByDesc('d')->values()->pluck('d')->all();

                $aggregated = (clone $base)
                    ->whereNotNull('species_id')
                    ->selectRaw("{$dateExprDay} as d, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count")
                    ->groupBy('d', 'species_id')
                    ->get();

                $map = [];
                foreach ($aggregated as $a) {
                    $map[$a->d][$a->species_id] = ['qty' => $a->qty, 'count' => $a->catch_count];
                }

                $header = ['Period', 'Species', 'Qty(Kg)', 'Count'];
                $rows = [];
                foreach ($days as $d) {
                    foreach ($species as $sp) {
                        $qty = $map[$d][$sp->id]['qty'] ?? 0;
                        $cnt = $map[$d][$sp->id]['count'] ?? 0;
                        $rows[] = [$d, $sp->common_name, (string) $qty, (string) $cnt];
                    }
                }

                $filename = sprintf('public-catch-analytics-daily-by-species-%s.csv', now()->format('YmdHis'));
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

                $callback = function () use ($header, $rows) {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, $header);
                    foreach ($rows as $r) {
                        fputcsv($out, $r);
                    }
                    fclose($out);
                };

                return response()->stream($callback, 200, $headers);
            }

            // If user requests monthly separated by species, build a wide CSV with one column per species
            if ($series === 'monthly' && $separated) {
                // Long format: one row per (month, species) with a Species column
                $species = \App\Models\Species::orderBy('common_name')->get(['id', 'common_name']);
                $months = $monthlySeries->sortByDesc('ym')->values()->pluck('ym')->all();

                // Aggregate once: month, species_id -> qty, count
                $aggregated = (clone $base)
                    ->whereNotNull('species_id')
                    ->selectRaw("{$dateExprMonth} as ym, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count")
                    ->groupBy('ym', 'species_id')
                    ->get();

                $map = [];
                foreach ($aggregated as $a) {
                    $map[$a->ym][$a->species_id] = ['qty' => $a->qty, 'count' => $a->catch_count];
                }

                $header = ['Period', 'Species', 'Qty(Kg)', 'Count'];
                $rows = [];
                foreach ($months as $ym) {
                    foreach ($species as $sp) {
                        $qty = $map[$ym][$sp->id]['qty'] ?? 0;
                        $cnt = $map[$ym][$sp->id]['count'] ?? 0;
                        $rows[] = [$ym, $sp->common_name, (string) $qty, (string) $cnt];
                    }
                }

                $filename = sprintf('public-catch-analytics-monthly-by-species-%s.csv', now()->format('YmdHis'));
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

                $callback = function () use ($header, $rows) {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, $header);
                    foreach ($rows as $r) {
                        fputcsv($out, $r);
                    }
                    fclose($out);
                };

                return response()->stream($callback, 200, $headers);
            }

            // Fallback: existing per-period CSVs (daily/monthly/annual)
            // Also support annual separated by species: long format Year, Species, Qty, Count
            if ($series === 'annual' && $separated) {
                $species = \App\Models\Species::orderBy('common_name')->get(['id', 'common_name']);

                $aggregated = (clone $base)
                    ->whereNotNull('species_id')
                    ->selectRaw("{$dateExprYear} as y, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count")
                    ->groupBy('y', 'species_id')
                    ->get();

                $map = [];
                foreach ($aggregated as $a) {
                    $map[$a->y][$a->species_id] = ['qty' => $a->qty, 'count' => $a->catch_count];
                }

                $years = $annualSeries->sortByDesc('y')->values()->pluck('y')->all();
                $header = ['Period', 'Species', 'Qty(Kg)', 'Count'];
                $rows = [];
                foreach ($years as $y) {
                    foreach ($species as $sp) {
                        $qty = $map[$y][$sp->id]['qty'] ?? 0;
                        $cnt = $map[$y][$sp->id]['count'] ?? 0;
                        $rows[] = [$y, $sp->common_name, (string) $qty, (string) $cnt];
                    }
                }

                $filename = sprintf('public-catch-analytics-annual-by-species-%s.csv', now()->format('YmdHis'));
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

                $callback = function () use ($header, $rows) {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, $header);
                    foreach ($rows as $r) {
                        fputcsv($out, $r);
                    }
                    fclose($out);
                };

                return response()->stream($callback, 200, $headers);
            }
            if ($series === 'annual') {
                $rows = $annualSeries->sortByDesc('y')->values();
                $iter = $rows->map(fn ($r) => [$r->y, (string) $r->qty, (string) $r->catch_count]);
            } elseif ($series === 'daily') {
                $rows = $dailySeries->sortByDesc('d')->values();
                $iter = $rows->map(fn ($r) => [$r->d, (string) $r->qty, (string) $r->catch_count]);
            } else {
                $rows = $monthlySeries->sortByDesc('ym')->values();
                $iter = $rows->map(fn ($r) => [$r->ym, (string) $r->qty, (string) $r->catch_count]);
            }
            $header = ['Period', 'Qty(Kg)', 'Count'];
            $filename = sprintf('public-catch-analytics-%s-%s.csv', $series, now()->format('YmdHis'));
            // For testing environments, return non-streamed content so tests can assert on it
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ];
            if (app()->runningUnitTests()) {
                $fp = fopen('php://temp', 'r+');
                fputcsv($fp, $header);
                foreach ($iter as $row) {
                    fputcsv($fp, $row);
                }
                rewind($fp);
                $content = stream_get_contents($fp);
                fclose($fp);

                return response($content, 200, $headers);
            }

            $callback = function () use ($header, $iter) {
                $out = fopen('php://output', 'w');
                fputcsv($out, $header);
                foreach ($iter as $row) {
                    fputcsv($out, $row);
                }
                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        return view('analytics.public', [
            'totalSummary' => $totalSummary,
            'topSpecies' => $topSpecies,
            'dailySeries' => $dailySeries,
            'monthlySeries' => $monthlySeries,
            'dailyBySpecies' => $dailyBySpecies,
            'monthlyBySpecies' => $monthlyBySpecies,
            'annualBySpecies' => $annualBySpecies,
            'gearBreakdown' => $gearBreakdown,
            'annualSeries' => $annualSeries,
            'zoneBreakdown' => $zoneBreakdown,
            'zoneBySpecies' => $zoneBySpecies,
        ]);
    }
}
