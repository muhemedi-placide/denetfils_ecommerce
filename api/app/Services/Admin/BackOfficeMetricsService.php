<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
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
                    'users_total' => Customer::query()->count() + User::query()->count(),
                    'customers_total' => Customer::query()->count(),
                    'staff_total' => User::query()->count(),
                    'suspended_users' => Customer::query()->where('status', 'suspended')->count() + User::query()->where('status', 'suspended')->count(),
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
            'visitor_acquisition' => $this->visitorAcquisition($locale),
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

    private function visitorAcquisition(string $locale): array
    {
        $total = max(1, Customer::query()->count() + User::query()->count());
        $customers = Customer::query()->count();
        $staff = User::query()->count();
        $suspended = Customer::query()->where('status', 'suspended')->count() + User::query()->where('status', 'suspended')->count();
        $active = Customer::query()->where('status', 'active')->count() + User::query()->where('status', 'active')->count();

        $segments = [
            ['key' => 'customers', 'label' => 'Clients', 'value' => $customers, 'percentage' => $this->percentage($customers, $total), 'color' => '#1f8a5b'],
            ['key' => 'staff', 'label' => 'Equipe interne', 'value' => $staff, 'percentage' => $this->percentage($staff, $total), 'color' => '#c46a2a'],
            ['key' => 'suspended', 'label' => 'Suspendus', 'value' => $suspended, 'percentage' => $this->percentage($suspended, $total), 'color' => '#b91c1c'],
            ['key' => 'active', 'label' => 'Actifs', 'value' => $active, 'percentage' => $this->percentage($active, $total), 'color' => '#6554c0'],
        ];

        $countries = Customer::query()
            ->selectRaw("COALESCE(NULLIF(country_code, ''), 'N/A') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row) => [
                'label' => strtoupper((string) $row->label),
                'value' => (int) $row->total,
                'percentage' => $this->percentage((int) $row->total, $total),
            ])
            ->values()
            ->all();

        $platforms = collect([
            ['label' => 'Site web', 'value' => $customers, 'percentage' => $this->percentage($customers, $total), 'color' => '#1f8a5b'],
            ['label' => 'Back-office', 'value' => $staff, 'percentage' => $this->percentage($staff, $total), 'color' => '#c46a2a'],
            ['label' => 'Import manuel', 'value' => max(0, $total - $customers - $staff), 'percentage' => $this->percentage(max(0, $total - $customers - $staff), $total), 'color' => '#6554c0'],
        ])->filter(fn ($item) => $item['value'] > 0)->values()->all();

        $recent = Customer::query()
            ->latest('id')
            ->limit(4)
            ->get()
            ->map(fn (Customer $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'country_code' => $user->country_code,
                'preferred_locale' => $user->preferred_locale,
                'roles' => ['customer'],
                'source' => 'Inscription client',
                'platform' => 'Site web',
                'channel' => 'Compte client',
                'campaign' => 'Non rattachee',
                'first_touch_at' => optional($user->created_at)->toIso8601String(),
                'last_touch_at' => optional($user->updated_at)->toIso8601String(),
            ])
            ->values()
            ->all();

        return [
            'total' => $total,
            'source' => 'computed_from_customers_and_users',
            'segments' => $segments,
            'platforms' => $platforms,
            'countries' => $countries,
            'recent_visitors' => $recent,
            'next_api_contract' => [
                'source',
                'platform',
                'channel',
                'campaign',
                'first_touch_at',
                'last_touch_at',
            ],
        ];
    }

    private function stockAlerts(int $lowStockThreshold, string $locale): array
    {
        return Product::query()
            ->with(['category', 'images'])
            ->where('stock_quantity', '<=', $this->threshold($lowStockThreshold))
            ->orderBy('stock_quantity')
            ->orderBy('id')
            ->limit(10)
            ->get()
            ->map(function (Product $product) use ($lowStockThreshold, $locale) {
                $primaryImage = $product->images->first();

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'slug' => $product->slug,
                    'name' => $product->localized('name', $locale),
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->localized('name', $locale),
                        'slug' => $product->category->slug,
                    ] : null,
                    'primary_image' => $primaryImage ? [
                        'id' => $primaryImage->id,
                        'url' => $primaryImage->url,
                        'width' => $primaryImage->width,
                        'height' => $primaryImage->height,
                        'dominant_color' => $primaryImage->dominant_color,
                        'alt_text' => $primaryImage->localized('alt_text', $locale),
                        'sort_order' => $primaryImage->sort_order,
                    ] : null,
                    'stock_quantity' => $product->stock_quantity,
                    'status' => $this->stockStatus($product, $lowStockThreshold),
                    'is_active' => $product->is_active,
                ];
            })
            ->values()
            ->all();
    }

    private function percentage(int $value, int $total): float
    {
        return $total > 0 ? round(($value / $total) * 100, 1) : 0.0;
    }

    private function threshold(int $value): int
    {
        return max(0, min(100, $value));
    }
}
