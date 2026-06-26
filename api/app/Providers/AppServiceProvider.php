<?php

namespace App\Providers;

use App\Services\Shipping\MondialRelay\MondialRelayProvider;
use App\Services\Shipping\Chronopost\ChronopostProvider;
use App\Services\Shipping\ShippingManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ShippingManager::class, fn ($app) => new ShippingManager([
            $app->make(MondialRelayProvider::class),
            $app->make(ChronopostProvider::class),
        ]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('pickup-search', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('shipment-tracking', fn (Request $request) => Limit::perMinute(12)->by($request->user()?->id ?: $request->ip()));
    }
}
