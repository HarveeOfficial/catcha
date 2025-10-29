<?php

namespace App\Providers;

use App\Models\LiveTrack;
use App\Models\User;
use App\Models\Zone;
use App\Policies\LiveTrackPolicy;
use App\Policies\ZonePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(LiveTrack::class, LiveTrackPolicy::class);
        Gate::policy(Zone::class, ZonePolicy::class);
        Gate::define('viewLiveTracksAdmin', function (User $user): bool {
            return $user->isAdmin();
        });
    }
}
