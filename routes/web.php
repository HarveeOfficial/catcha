<?php

use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\AiConsultController;
use App\Http\Controllers\AiConversationController;
use App\Http\Controllers\AiGuidanceController;
use App\Http\Controllers\CatchAnalyticsController;
use App\Http\Controllers\CatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuidanceController;
use App\Http\Controllers\HeatmapController;
use App\Http\Controllers\LiveTrackController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAnalyticsController;
use App\Http\Controllers\WeatherCityController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\WeatherForecastController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $base = \App\Models\FishCatch::query();
    $driver = \Illuminate\Support\Facades\DB::getDriverName();
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
        ->limit(7) // show last 7 days on landing
        ->get();

    return view('welcome', [
        'landingTotalSummary' => $totalSummary,
        'landingTopSpecies' => $topSpecies,
        'landingDailySeries' => $dailySeries,
    ]);
});

// Public analytics (aggregated, anonymized)
Route::get('/analytics', PublicAnalyticsController::class)->name('analytics.public');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

// Public heatmap endpoints (aggregated, anonymized)
Route::get('/catches/heatmap', [HeatmapController::class, 'view'])->name('catches.heatmap');
Route::get('/catches/heatmap/data', [HeatmapController::class, 'data'])->name('catches.heatmap.data');
Route::get('/catches/heatmap/point-info', [HeatmapController::class, 'pointInfo'])->name('catches.heatmap.point-info');

// Public zones view
Route::view('/zones', 'zones')->name('zones');

Route::middleware(['auth'])->group(function () {
    // Catches
    Route::get('/catches', [CatchController::class, 'index'])->name('catches.index');
    Route::get('/catches/create', [CatchController::class, 'create'])->name('catches.create');
    Route::post('/catches', [CatchController::class, 'store'])->name('catches.store');
    Route::get('/catches/{fishCatch}/edit', [CatchController::class, 'edit'])
        ->whereNumber('fishCatch')
        ->name('catches.edit');
    Route::patch('/catches/{fishCatch}', [CatchController::class, 'update'])
        ->whereNumber('fishCatch')
        ->name('catches.update');
    // Place analytics BEFORE the parameter route to avoid being captured by {fishCatch}
    Route::get('/catches/analytics', CatchAnalyticsController::class)->name('catches.analytics');
    Route::get('/catches/{fishCatch}', [CatchController::class, 'show'])
        ->whereNumber('fishCatch')
        ->name('catches.show');
    // Weather endpoint (AJAX)
    Route::get('/weather/current', WeatherController::class)->name('weather.current');
    Route::get('/weather/forecast', WeatherForecastController::class)->name('weather.forecast');
    Route::get('/weather/city', WeatherCityController::class)->name('weather.city');
    // Interactive weather map (click-to-fetch current + forecast)
    Route::view('/weather/map', 'weather.map')->name('weather.map');
    // Removed standalone weather page; weather now integrated into dashboard
    // AI consult endpoint
    Route::post('/ai/consult', AiConsultController::class)->name('ai.consult');
    // Redirect the old consult page to the unified chat page with the consult tab
    Route::get('/ai/consult', function () {
        return redirect()->route('ai.chat', ['tab' => 'consult']);
    });
    Route::view('/ai/chat', 'ai.chat')->name('ai.chat');
    // Seasonal trend endpoint (AI-assisted trend data)
    Route::get('/ai/seasonal-trends', \App\Http\Controllers\SeasonalTrendController::class)->name('ai.seasonal-trends');
    Route::view('/ai/seasonal-trends/view', 'ai.seasonal-trends')->name('ai.seasonal-trends.view');
    Route::get('/ai/conversations', [AiConversationController::class, 'index'])->name('ai.conversations.index');
    Route::get('/ai/conversations/{conversation}', [AiConversationController::class, 'show'])->name('ai.conversations.show');
    Route::delete('/ai/conversations/{conversation}', [AiConversationController::class, 'destroy'])->name('ai.conversations.destroy');
    Route::post('/ai/messages/{message}/to-guidance', [AiGuidanceController::class, 'store'])->name('ai.messages.to-guidance');
    // Feedback (experts/admins can submit; all authenticated can view list)
    Route::get('/catches/{fishCatch}/feedback', [\App\Http\Controllers\CatchFeedbackController::class, 'index'])->name('catches.feedback.index');
    Route::post('/catches/{fishCatch}/feedback', [\App\Http\Controllers\CatchFeedbackController::class, 'store'])->name('catches.feedback.store');
    Route::delete('/feedback/{feedback}', [\App\Http\Controllers\CatchFeedbackController::class, 'destroy'])->name('catches.feedback.destroy');
    Route::patch('/feedback/{feedback}', [\App\Http\Controllers\CatchFeedbackController::class, 'update'])->name('catches.feedback.update');
    Route::post('/feedback/{feedback}/like', [\App\Http\Controllers\CatchFeedbackController::class, 'like'])->name('catches.feedback.like');
    Route::delete('/feedback/{feedback}/like', [\App\Http\Controllers\CatchFeedbackController::class, 'unlike'])->name('catches.feedback.unlike');

    // Guidance (restrict creation to experts/admin later)
    Route::get('/guidances', [GuidanceController::class, 'index'])->name('guidances.index');
    Route::get('/guidances/create', [GuidanceController::class, 'create'])->name('guidances.create');
    Route::post('/guidances', [GuidanceController::class, 'store'])->name('guidances.store');
    Route::get('/guidances/{guidance}', [GuidanceController::class, 'show'])->name('guidances.show');
    Route::get('/guidances/{guidance}/edit', [GuidanceController::class, 'edit'])->name('guidances.edit');
    Route::patch('/guidances/{guidance}', [GuidanceController::class, 'update'])->name('guidances.update');
    Route::delete('/guidances/{guidance}', [GuidanceController::class, 'destroy'])->name('guidances.destroy');
    Route::post('/guidances/{guidance}/approve', [GuidanceController::class, 'approve'])->name('guidances.approve');
    Route::post('/guidances/{guidance}/reject', [GuidanceController::class, 'reject'])->name('guidances.reject');

    // AI Suggestions (cached per subject)
    Route::get('/ai/suggestions/catches/{fishCatch}', [\App\Http\Controllers\AiSuggestionController::class, 'showCatch'])
        ->whereNumber('fishCatch')
        ->name('ai.suggestions.catches.show');
    Route::post('/ai/suggestions/catches/{fishCatch}', [\App\Http\Controllers\AiSuggestionController::class, 'generateCatch'])
        ->whereNumber('fishCatch')
        ->name('ai.suggestions.catches.generate');

    // Live tracking: create a new track (returns publicId + writeKey)
    Route::get('/live-tracks', [LiveTrackController::class, 'index'])
        ->middleware('can:viewLiveTracksAdmin')
        ->name('live-tracks.index');
    // Admin action: allow admins to forcibly end/close a track
    Route::post('/live-tracks/{track}/admin-end', [LiveTrackController::class, 'adminEnd'])
        ->whereNumber('track')
        ->middleware('can:viewLiveTracksAdmin')
        ->name('live-tracks.admin-end');
    Route::post('/live-tracks', [LiveTrackController::class, 'create'])->name('live-tracks.create');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes (only for admins)
Route::middleware(['auth', IsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/zones', [ZoneController::class, 'index'])->name('zones.index');
    Route::get('/zones/create', [ZoneController::class, 'create'])->name('zones.create');
    Route::post('/zones', [ZoneController::class, 'store'])->name('zones.store');
    Route::get('/zones/{zone}', [ZoneController::class, 'show'])->name('zones.show');
    Route::get('/zones/{zone}/edit', [ZoneController::class, 'edit'])->name('zones.edit');
    Route::patch('/zones/{zone}', [ZoneController::class, 'update'])->name('zones.update');
    Route::delete('/zones/{zone}', [ZoneController::class, 'destroy'])->name('zones.destroy');
});

// Public API for zone data
Route::get('/api/zones/data', [ZoneController::class, 'data'])->name('api.zones.data');

require __DIR__.'/auth.php';

// Public live track endpoints
Route::get('/live-tracks/active', [LiveTrackController::class, 'activeMap'])->name('live-tracks.active');
Route::get('/live-tracks/active/points', [LiveTrackController::class, 'activePoints'])->name('live-tracks.active.points');
Route::get('/live-tracks/{publicId}', [LiveTrackController::class, 'show'])->name('live-tracks.show');
Route::get('/live-tracks/{publicId}/points', [LiveTrackController::class, 'pointsIndex'])->name('live-tracks.points.index');
Route::post('/live-tracks/{publicId}/points', [LiveTrackController::class, 'pointsStore'])->name('live-tracks.points.store');
Route::post('/live-tracks/{publicId}/end', [LiveTrackController::class, 'end'])->name('live-tracks.end');
