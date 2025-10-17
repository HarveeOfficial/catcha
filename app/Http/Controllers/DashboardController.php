<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use App\Models\Guidance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $userId = Auth::id();
        $recentCatches = FishCatch::with(['species'])
            ->where('user_id', $userId)
            ->latest('caught_at')
            ->limit(5)
            ->get();

        // Database agnostic monthly aggregation
        $driver = DB::getDriverName();
        $dateExpr = match($driver) {
            'mysql', 'mariadb' => "DATE_FORMAT(caught_at, '%Y-%m')",
            'pgsql' => "TO_CHAR(caught_at, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', caught_at)",
            'sqlsrv' => "FORMAT(caught_at, 'yyyy-MM')",
            default => "DATE_FORMAT(caught_at, '%Y-%m')"
        };

        $monthlyTotals = FishCatch::selectRaw("{$dateExpr} as ym, SUM(quantity) as total_qty")
            ->where('user_id', $userId)
            ->groupBy('ym')
            ->orderBy('ym','desc')
            ->limit(6)
            ->get();

        $activeGuidances = Guidance::where('active', true)
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Admin view toggle: admins always see it; experts can opt-in via ?view=admin
        $siteTotals = null;
        $userCount = null;
        $speciesCount = null;
        $pendingGuidances = null;
        $user = Auth::user();
        $showAdmin = false;
        if ($user) {
            $showAdmin = $user->isAdmin() || ($user->isExpert() && request()->input('view') === 'admin');
        }

        if ($showAdmin) {
            $siteTotals = FishCatch::selectRaw('COUNT(*) as catches, COALESCE(SUM(quantity),0) as total_qty, COALESCE(SUM(`count`),0) as total_count')->first();
            $userCount = \App\Models\User::count();
            $speciesCount = \App\Models\Species::count();
            $pendingGuidances = Guidance::where('active', false)->orderBy('id')->limit(10)->get();
        }

        return view('dashboard', compact('recentCatches','monthlyTotals','activeGuidances','siteTotals','userCount','speciesCount','pendingGuidances','showAdmin'));
    }
}
