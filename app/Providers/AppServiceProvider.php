<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\LiveTrack;
use App\Policies\LiveTrackPolicy;
use App\Models\User;

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
        Gate::define('viewLiveTracksAdmin', function (User $user): bool {
            return $user->isAdmin();
        });
    }
}
