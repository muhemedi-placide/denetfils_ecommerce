<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminBackOfficeFrontendTest extends TestCase
{
    public function test_admin_pages_render_with_shell_and_action_modals(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $session = [
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ];

        $this->withSession($session)
            ->get('/fr/admin')
            ->assertOk()
            ->assertSee('Back-office Denetfils')
            ->assertSee('adminShell', false)
            ->assertSee('Cockpit ERP / CRM');

        $this->withSession($session)
            ->get('/fr/admin/catalogue/produits')
            ->assertOk()
            ->assertSee('Nouveau produit')
            ->assertSee('product-create-modal', false)
            ->assertSee('product-publication-10', false);

        $this->withSession($session)
            ->get('/fr/admin/catalogue/categories')
            ->assertOk()
            ->assertSee('Nouvelle categorie')
            ->assertSee('category-create-modal', false);

        $this->withSession($session)
            ->get('/fr/admin/stock')
            ->assertOk()
            ->assertSee('inventory-stock-10', false);

        $this->withSession($session)
            ->get('/fr/admin/utilisateurs')
            ->assertOk()
            ->assertSee('Inviter un membre')
            ->assertSee('user-create-modal', false)
            ->assertSee('user-roles-7', false);

        $this->withSession($session)
            ->get('/fr/admin/acces')
            ->assertOk()
            ->assertSee('Voir droits');

        $this->withSession($session)
            ->get('/fr/admin/audit')
            ->assertOk()
            ->assertSee('audit-show-0', false);

        $this->withSession($session)
            ->get('/fr/admin/modules/commandes')
            ->assertOk()
            ->assertSee('Commandes')
            ->assertSee('Module separe');
    }

    private function adminApiFakes(): array
    {
        return [
            '*/admin/dashboard*' => Http::response([
                'data' => [
                    'kpis' => [
                        'catalog' => ['products_active' => 1, 'products_total' => 1],
                        'inventory' => ['total_units_available' => 12, 'low_stock_products' => 0],
                        'carts' => ['active_count' => 2, 'formatted_active_value' => '18,00 EUR'],
                        'identity' => ['users_total' => 1, 'customers_total' => 1],
                    ],
                    'catalog_health' => [
                        'products_missing_images' => 0,
                        'products_missing_variants' => 0,
                        'products_missing_seo' => 0,
                        'inactive_categories_with_active_products' => 0,
                    ],
                    'stock_alerts' => [],
                    'recent_activity' => [$this->auditLog()],
                ],
            ]),
            '*/admin/products*' => Http::response([
                'data' => [$this->product()],
                'meta' => ['total' => 1],
            ]),
            '*/admin/categories*' => Http::response([
                'data' => [$this->category()],
                'meta' => ['total' => 1],
            ]),
            '*/admin/inventory*' => Http::response([
                'data' => [$this->inventoryProduct()],
                'meta' => ['total' => 1],
            ]),
            '*/admin/users*' => Http::response([
                'data' => [$this->user()],
                'meta' => ['total' => 1],
            ]),
            '*/admin/roles*' => Http::response([
                'data' => [['name' => 'admin', 'permissions' => ['catalog.view', 'users.view']]],
            ]),
            '*/admin/permissions*' => Http::response([
                'data' => ['catalog.view', 'users.view', 'audit.view'],
            ]),
            '*/admin/audit-logs*' => Http::response([
                'data' => [$this->auditLog()],
                'meta' => ['total' => 1],
            ]),
        ];
    }

    private function product(): array
    {
        return [
            'id' => 10,
            'category_id' => 3,
            'category' => $this->category(),
            'name' => ['fr' => 'Miel doux', 'en' => 'Sweet honey'],
            'slug' => 'miel-doux',
            'description' => ['fr' => 'Description produit', 'en' => 'Product description'],
            'sku' => 'MIEL-001',
            'price_cents' => 890,
            'currency' => 'EUR',
            'stock_quantity' => 12,
            'is_active' => true,
            'variants' => [],
        ];
    }

    private function inventoryProduct(): array
    {
        return [
            ...$this->product(),
            'preview_name' => ['fr' => 'Miel doux', 'en' => 'Sweet honey'],
            'stock_status' => 'in_stock',
            'low_stock_threshold' => 5,
            'updated_at' => '2026-06-16T10:00:00Z',
        ];
    }

    private function category(): array
    {
        return [
            'id' => 3,
            'name' => ['fr' => 'Epicerie', 'en' => 'Grocery'],
            'slug' => 'epicerie',
            'sort_order' => 1,
            'is_active' => true,
            'products_count' => 1,
        ];
    }

    private function user(): array
    {
        return [
            'id' => 7,
            'name' => 'Admin Test',
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin@example.test',
            'phone' => '+33600000000',
            'preferred_locale' => 'fr',
            'country_code' => 'FR',
            'timezone' => 'Europe/Paris',
            'status' => 'active',
            'roles' => ['admin'],
        ];
    }

    private function auditLog(): array
    {
        return [
            'action' => 'product.updated',
            'actor' => ['name' => 'Admin Test', 'email' => 'admin@example.test'],
            'auditable_type' => 'App\\Models\\Product',
            'auditable_id' => 10,
            'metadata' => ['field' => 'stock_quantity'],
            'ip_address' => '127.0.0.1',
            'created_at' => '2026-06-16T10:00:00Z',
        ];
    }
}
