<?php

namespace App\Providers;

use App\Models\Delivery;
use App\Models\Ride;
use App\Models\User;
use App\Observers\DeliveryObserver;
use App\Observers\RideObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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

        // Register Delivery Observer for Reverb WebSocket updates
        Delivery::observe(DeliveryObserver::class);

        // Share pending drivers count with all views (for sidebar badge)
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $pendingDriversCount = User::where('role', 'driver')
                    ->where('status', 'pending')
                    ->count();
                $pendingDeliveryAgentsCount = 0;
                if (Schema::hasTable('rider_vehicles')) {
                    $pendingDeliveryAgentsCount = User::where('role', 'delivery')
                        ->where('status', 'pending')
                        ->count();
                }
                $pendingSignupGiftsCount = 0;
                if (Schema::hasColumn('users', 'signup_gift_received_at')) {
                    $pendingSignupGiftsCount = User::where('role', 'user')
                        ->whereNull('signup_gift_received_at')
                        ->where(function ($q) {
                            $q->where(function ($nameQuery) {
                                $nameQuery->whereNotNull('name')->where('name', '!=', '');
                            })->orWhereNotNull('email');
                        })
                        ->count();
                }
                $view->with('pendingDriversCount', $pendingDriversCount);
                $view->with('pendingDeliveryAgentsCount', $pendingDeliveryAgentsCount);
                $view->with('pendingSignupGiftsCount', $pendingSignupGiftsCount);
            }
        });
    }
}
