<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CatchController;
use App\Http\Controllers\LiveTrackController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (stateless)
|--------------------------------------------------------------------------
*/

Route::prefix('live-tracks')->group(function () {
    // Create a track (requires user login via Sanctum)
    Route::post('/', [LiveTrackController::class, 'apiCreate'])
        ->middleware('auth:sanctum')
        ->name('api.live-tracks.create');

    // Poll points (public)
    Route::get('{publicId}/points', [LiveTrackController::class, 'pointsIndex'])
        ->name('api.live-tracks.points.index');

    // Ingest a point (requires user login and write key header)
    Route::post('{publicId}/points', [LiveTrackController::class, 'pointsStore'])
        ->middleware('auth:sanctum')
        ->name('api.live-tracks.points.store');

    // End a track (requires user login and write key header)
    Route::post('{publicId}/end', [LiveTrackController::class, 'end'])
        ->middleware('auth:sanctum')
        ->name('api.live-tracks.end');
});

// Matches your Expo createCatch() expectation: POST /api/catches
Route::post('catches', [CatchController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.catches.store');

// Auth (Sanctum) for mobile app
Route::post('login', [AuthController::class, 'login'])->name('api.login');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('user', [AuthController::class, 'me'])->name('api.me');
});

// Location API (PSGC data for cascading dropdowns)
Route::prefix('locations')->group(function () {
    Route::get('regions', [LocationController::class, 'getRegions'])->name('api.locations.regions');
    Route::get('municipalities', [LocationController::class, 'getMunicipalitiesByRegion'])->name('api.locations.municipalities');
    Route::get('barangays', [LocationController::class, 'getBarangaysByMunicipality'])->name('api.locations.barangays');
});
