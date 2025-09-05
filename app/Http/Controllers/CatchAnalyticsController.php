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
        if (!$user->isExpert() && !$user->isAdmin()) {
            $base->where('user_id', $user->id);
        } else if ($request->filled('user')) {
            $base->whereHas('user', function($q) use ($request) {
                $q->where('name','like','%'.$request->input('user').'%');
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
        $dateExprDay = match($driver) {
            'mysql','mariadb' => "DATE(caught_at)",
            'pgsql' => "DATE(caught_at)",
            'sqlite' => "DATE(caught_at)",
            'sqlsrv' => "CAST(caught_at AS date)",
            default => "DATE(caught_at)"
        };
        $dateExprMonth = match($driver) {
            'mysql','mariadb' => "DATE_FORMAT(caught_at, '%Y-%m')",
            'pgsql' => "TO_CHAR(caught_at, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', caught_at)",
            'sqlsrv' => "FORMAT(caught_at, 'yyyy-MM')",
            default => "DATE_FORMAT(caught_at, '%Y-%m')"
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
            ->orderBy('d','desc')
            ->limit(14)
            ->get();

        $monthlySeries = (clone $base)
            ->selectRaw("{$dateExprMonth} as ym, SUM(quantity) as qty, SUM(count) as catch_count")
            ->groupBy('ym')
            ->orderBy('ym','desc')
            ->limit(6)
            ->get();

        $gearBreakdown = (clone $base)
            ->selectRaw("gear_type, COUNT(*) as catches, SUM(quantity) as qty")
            ->whereNotNull('gear_type')
            ->groupBy('gear_type')
            ->orderByDesc('qty')
            ->get();

        // Provide species list for filter UI if needed
        $speciesList = Species::orderBy('common_name')->get(['id','common_name']);

        return view('catches.analytics', [
            'totalSummary' => $totalSummary,
            'topSpecies' => $topSpecies,
            'dailySeries' => $dailySeries,
            'monthlySeries' => $monthlySeries,
            'gearBreakdown' => $gearBreakdown,
            'speciesList' => $speciesList,
        ]);
    }
}

