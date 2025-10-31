<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CatchAnalyticsController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // Scope: fishers see only their own catches; experts/admins see all (optionally filter by user)
        $base = FishCatch::query();
        if (! $user->isExpert() && ! $user->isAdmin()) {
            $base->where('user_id', $user->id);
        } elseif ($request->filled('user')) {
            $base->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->input('user').'%');
            });
        }

        // Date range filters
        if ($request->filled('from')) {
            $base->where('caught_at', '>=', $request->date('from')->startOfDay());
        }
        if ($request->filled('to')) {
            $base->where('caught_at', '<=', $request->date('to')->endOfDay());
        }

        $driver = DB::getDriverName();
        $dateExprDay = match ($driver) {
            'mysql','mariadb' => 'DATE(caught_at)',
            'pgsql' => 'DATE(caught_at)',
            'sqlite' => 'DATE(caught_at)',
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

        // Clone base for separate aggregations
        $totalSummary = (clone $base)->selectRaw('COUNT(*) as catches, COALESCE(SUM(quantity),0) as total_qty, COALESCE(SUM(count),0) as total_count, AVG(avg_size_cm) as avg_size')->first();

        $topSpecies = (clone $base)
            ->selectRaw('species_id, COALESCE(SUM(quantity),0) as qty_sum, COUNT(*) as catches_count')
            ->whereNotNull('species_id')
            ->groupBy('species_id')
            ->orderByDesc('qty_sum')
            ->limit(5)
            ->with('species')
            ->get();

        $dailySeries = (clone $base)
            ->selectRaw("{$dateExprDay} as d, SUM(quantity) as qty, SUM(count) as catch_count")
            ->groupBy('d')
            ->orderBy('d', 'desc')
            ->limit(14)
            ->get();

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

        $annualBySpecies = (clone $base)
            ->whereNotNull('species_id')
            ->selectRaw("{$dateExprYear} as y, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count")
            ->groupBy('y', 'species_id')
            ->orderBy('y', 'desc')
            ->get()
            ->groupBy('y');

        $gearBreakdown = (clone $base)
            ->join('gear_types', 'fish_catches.gear_type_id', '=', 'gear_types.id')
            ->selectRaw('gear_types.name as gear_type, COUNT(*) as catches, SUM(fish_catches.quantity) as qty')
            ->whereNotNull('fish_catches.gear_type_id')
            ->groupBy('gear_types.id', 'gear_types.name')
            ->orderByDesc('qty')
            ->get();

        // Per-gear species breakdown: for each gear_type, list species with qty/count
        $gearSpecies = (clone $base)
            ->join('gear_types', 'fish_catches.gear_type_id', '=', 'gear_types.id')
            ->whereNotNull('fish_catches.gear_type_id')
            ->whereNotNull('fish_catches.species_id')
            ->selectRaw('gear_types.name as gear_type, fish_catches.species_id, COALESCE(SUM(fish_catches.quantity),0) as qty, COALESCE(SUM(fish_catches.count),0) as catch_count')
            ->groupBy('gear_types.id', 'gear_types.name', 'fish_catches.species_id')
            ->orderByDesc('qty')
            ->get()
            ->groupBy('gear_type');

        // Provide species list for filter UI if needed
        $speciesList = Species::orderBy('common_name')->get(['id', 'common_name']);
        
        // If CSV requested, stream the selected series as per-column CSV
        // also support monthly separated by species via ?format=csv&series=monthly&separated=species
        if ($request->input('format') === 'csv') {
            $series = $request->input('series', 'monthly'); // daily, monthly, annual, gear
            $separated = $request->input('separated') === 'species';

            // Handle year/month filtering for CSV export
            $yearFilter = $request->input('year');
            $monthFilter = $request->input('month');
            
            // If year/month specified, filter the data accordingly
            $csvBase = clone $base;
            if ($yearFilter) {
                $csvBase->whereRaw("YEAR(caught_at) = ?", [$yearFilter]);
                if ($monthFilter) {
                    $csvBase->whereRaw("MONTH(caught_at) = ?", [$monthFilter]);
                }
            }

            // support gear-by-species CSV via ?format=csv&series=gear
            if ($series === 'gear') {
                $header = ['Gear', 'Species', 'Qty(Kg)', 'Count'];
                $rows = [];
                $speciesMap = \App\Models\Species::pluck('common_name', 'id')->all();
                
                // Use filtered base for gear export
                $gearSpeciesFiltered = (clone $csvBase)
                    ->join('gear_types', 'fish_catches.gear_type_id', '=', 'gear_types.id')
                    ->whereNotNull('fish_catches.gear_type_id')
                    ->whereNotNull('fish_catches.species_id')
                    ->selectRaw('gear_types.name as gear_type, fish_catches.species_id, COALESCE(SUM(fish_catches.quantity),0) as qty, COALESCE(SUM(fish_catches.count),0) as catch_count')
                    ->groupBy('gear_types.id', 'gear_types.name', 'fish_catches.species_id')
                    ->orderByDesc('qty')
                    ->get()
                    ->groupBy('gear_type');
                    
                foreach ($gearSpeciesFiltered as $gear => $rowsForGear) {
                    foreach ($rowsForGear as $r) {
                        $rows[] = [$gear, $speciesMap[$r->species_id] ?? $r->species_id, (string) $r->qty, (string) $r->catch_count];
                    }
                }

                $filename = sprintf('catch-analytics-gear-by-species-%s.csv', now()->format('YmdHis'));
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

            if ($series === 'monthly' && $separated) {
                // Long format: one row per (month, species) with a Species column
                $species = \App\Models\Species::orderBy('common_name')->get(['id', 'common_name']);
                
                $aggregated = (clone $csvBase)
                    ->whereNotNull('species_id')
                    ->selectRaw("{$dateExprMonth} as ym, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count")
                    ->groupBy('ym', 'species_id')
                    ->get();
                
                // If year/month filter applied, only get that specific month
                $months = $aggregated->pluck('ym')->unique()->sortDesc()->values()->all();
                if (empty($months)) {
                    $months = [];
                }
                
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

                $filename = sprintf('catch-analytics-monthly-by-species-%s.csv', now()->format('YmdHis'));
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

            if ($series === 'daily') {
                if ($separated) {
                    $species = \App\Models\Species::orderBy('common_name')->get(['id', 'common_name']);
                    
                    $aggregated = (clone $csvBase)
                        ->whereNotNull('species_id')
                        ->selectRaw("{$dateExprDay} as d, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count")
                        ->groupBy('d', 'species_id')
                        ->get();
                    
                    $days = $aggregated->pluck('d')->unique()->sortDesc()->values()->all();

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

                    $filename = sprintf('catch-analytics-daily-by-species-%s.csv', now()->format('YmdHis'));
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

                $rows = (clone $dailySeries)->sortByDesc('d')->values();
                $header = ['Period', 'Qty(Kg)', 'Count'];
                $iter = $rows->map(fn ($r) => [$r->d, (string) $r->qty, (string) $r->catch_count]);
            } elseif ($series === 'annual') {
                // support annual separated by species if requested
                if ($separated) {
                    $species = \App\Models\Species::orderBy('common_name')->get(['id', 'common_name']);
                    $aggregated = (clone $csvBase)
                        ->whereNotNull('species_id')
                        ->selectRaw("{$dateExprYear} as y, species_id, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as catch_count")
                        ->groupBy('y', 'species_id')
                        ->get();
                    $map = [];
                    foreach ($aggregated as $a) {
                        $map[$a->y][$a->species_id] = ['qty' => $a->qty, 'count' => $a->catch_count];
                    }
                    $years = $aggregated->pluck('y')->unique()->sortDesc()->values()->all();
                    $header = ['Period', 'Species', 'Qty(Kg)', 'Count'];
                    $rows = [];
                    foreach ($years as $y) {
                        foreach ($species as $sp) {
                            $qty = $map[$y][$sp->id]['qty'] ?? 0;
                            $cnt = $map[$y][$sp->id]['count'] ?? 0;
                            $rows[] = [$y, $sp->common_name, (string) $qty, (string) $cnt];
                        }
                    }
                    $filename = sprintf('catch-analytics-annual-by-species-%s.csv', now()->format('YmdHis'));
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
                $rows = $annualSeries->sortByDesc('y')->values();
                $header = ['Period', 'Qty(Kg)', 'Count'];
                $iter = $rows->map(fn ($r) => [$r->y, (string) $r->qty, (string) $r->catch_count]);
            } else {
                $rows = $monthlySeries->sortByDesc('ym')->values();
                $header = ['Period', 'Qty(Kg)', 'Count'];
                $iter = $rows->map(fn ($r) => [$r->ym, (string) $r->qty, (string) $r->catch_count]);
            }

            $filename = sprintf('catch-analytics-%s-%s.csv', $series, now()->format('YmdHis'));
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

        return view('catches.analytics', [
            'totalSummary' => $totalSummary,
            'topSpecies' => $topSpecies,
            'dailySeries' => $dailySeries,
            'dailyBySpecies' => $dailyBySpecies,
            'monthlySeries' => $monthlySeries,
            'monthlyBySpecies' => $monthlyBySpecies,
            'annualBySpecies' => $annualBySpecies,
            'annualSeries' => $annualSeries,
            'gearBreakdown' => $gearBreakdown,
            'gearSpecies' => $gearSpecies,
            'speciesList' => $speciesList,
        ]);
    }
}
