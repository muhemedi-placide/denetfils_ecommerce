<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\MoneyFormatter;
use Illuminate\Support\Carbon;

class BackOfficeMetricsService
{
    public function dashboard(int $lowStockThreshold = 5, string $locale = 'fr'): array
    {
        $lowStockThreshold = $this->threshold($lowStockThreshold);
        $now = now();
        $today = Carbon::now('Europe/Paris')->startOfDay()->timezone(config('app.timezone'));

        $activeCartQuery = Cart::query()
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            });

        $activeCartValue = (int) (clone $activeCartQuery)->sum('total_cents');

        return [
            'generated_at' => $now->toIso8601String(),
            'timezone' => 'Europe/Paris',
            'currency' => 'EUR',
            'low_stock_threshold' => $lowStockThreshold,
            'kpis' => [
                'catalog' => [
                    'products_total' => Product::query()->count(),
                    'products_active' => Product::query()->where('is_active', true)->count(),
                    'products_inactive' => Product::query()->where('is_active', false)->count(),
                    'categories_total' => Category::query()->count(),
                    'categories_active' => Category::query()->where('is_active', true)->count(),
                ],
                'inventory' => [
                    'out_of_stock_products' => Product::query()->where('stock_quantity', 0)->count(),
                    'low_stock_products' => Product::query()
                        ->where('stock_quantity', '>', 0)
                        ->where('stock_quantity', '<=', $lowStockThreshold)
                        ->count(),
                    'total_units_available' => (int) Product::query()->sum('stock_quantity'),
                ],
                'carts' => [
                    'active_count' => (clone $activeCartQuery)->count(),
                    'created_today' => Cart::query()->where('created_at', '>=', $today)->count(),
                    'active_value_cents' => $activeCartValue,
                    'formatted_active_value' => MoneyFormatter::format($activeCartValue, 'EUR', $locale),
                ],
                'identity' => [
                    'users_total' => User::query()->count(),
                    'customers_total' => User::role('customer')->count(),
                    'staff_total' => User::query()->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))->count(),
                    'suspended_users' => User::query()->where('status', 'suspended')->count(),
                ],
            ],
            'catalog_health' => [
                'products_missing_images' => Product::query()->doesntHave('images')->count(),
                'products_missing_variants' => Product::query()->doesntHave('variants')->count(),
                'products_missing_seo' => Product::query()
                    ->where(function ($query) {
                        $query->whereNull('seo_title')->orWhereNull('seo_description');
                    })
                    ->count(),
                'inactive_categories_with_active_products' => Category::query()
                    ->where('is_active', false)
                    ->whereHas('products', fn ($query) => $query->where('is_active', true))
                    ->count(),
            ],
            'stock_alerts' => $this->stockAlerts($lowStockThreshold, $locale),
            'recent_activity' => AuditLog::query()
                ->with('actor')
                ->latest('id')
                ->limit(8)
                ->get()
                ->map(fn (AuditLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'actor' => $log->actor ? [
                        'id' => $log->actor->id,
                        'name' => $log->actor->name,
                        'email' => $log->actor->email,
                    ] : null,
                    'auditable_type' => $log->auditable_type,
                    'auditable_id' => $log->auditable_id,
                    'created_at' => $log->created_at,
                ])
                ->values()
                ->all(),
        ];
    }

    public function stockStatus(Product $product, int $lowStockThreshold = 5): string
    {
        if (! $product->is_active) {
            return 'inactive';
        }

        if ($product->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ($product->stock_quantity <= $this->threshold($lowStockThreshold)) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    private function stockAlerts(int $lowStockThreshold, string $locale): array
    {
        return Product::query()
            ->with('category')
            ->where('stock_quantity', '<=', $this->threshold($lowStockThreshold))
            ->orderBy('stock_quantity')
            ->orderBy('id')
            ->limit(10)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'slug' => $product->slug,
                'name' => $product->localized('name', $locale),
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->localized('name', $locale),
                    'slug' => $product->category->slug,
                ] : null,
                'stock_quantity' => $product->stock_quantity,
                'status' => $this->stockStatus($product, $lowStockThreshold),
                'is_active' => $product->is_active,
            ])
            ->values()
            ->all();
    }

    private function threshold(int $value): int
    {
        return max(0, min(100, $value));
    }
}
