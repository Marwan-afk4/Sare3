<?php

namespace App\Providers;

use App\Models\Ride;
use App\Models\User;
use App\Observers\RideObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        // Register Ride Observer for Reverb WebSocket updates
        Ride::observe(RideObserver::class);

        // Share pending drivers count with all views (for sidebar badge)
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $pendingDriversCount = User::where('role', 'driver')
                    ->where('status', 'pending')
                    ->count();
                $view->with('pendingDriversCount', $pendingDriversCount);
            }
        });
    }
}
