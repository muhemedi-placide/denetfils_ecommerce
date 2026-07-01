<?php

namespace App\Providers;

use App\Services\Shipping\MondialRelay\MondialRelayProvider;
use App\Services\Shipping\Chronopost\ChronopostProvider;
use App\Services\Shipping\ShippingManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        Permission::created(function (Permission $permission): void {
            Role::query()
                ->where('guard_name', $permission->guard_name)
                ->whereIn('name', ['admin', 'super_admin'])
                ->each(fn (Role $role) => $role->givePermissionTo($permission));
        });

        RateLimiter::for('pickup-search', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('shipment-tracking', fn (Request $request) => Limit::perMinute(12)->by($request->user()?->id ?: $request->ip()));
    }
}
