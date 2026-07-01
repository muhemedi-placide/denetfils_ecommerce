<?php

namespace Tests\Feature\Api\Core;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\EcommerceSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminBackOfficeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SupportedCountrySeeder::class, AccessControlSeeder::class, EcommerceSeeder::class]);
    }

    public function test_catalog_manager_can_read_dashboard_kpis(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('catalog_manager');

        Product::query()->where('slug', 'miel-de-montagne')->update(['stock_quantity' => 3]);
        Cart::create([
            'cart_token' => 'dashboard-token',
            'currency' => 'EUR',
            'subtotal_cents' => 1590,
            'tax_cents' => 0,
            'total_cents' => 1590,
            'expires_at' => now()->addDays(2),
        ]);

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/admin/dashboard?locale=fr&threshold=5')
            ->assertOk()
            ->assertJsonPath('data.currency', 'EUR')
            ->assertJsonPath('data.low_stock_threshold', 5)
            ->assertJsonPath('data.kpis.carts.active_count', 1)
            ->assertJsonPath('data.kpis.inventory.low_stock_products', 1)
            ->assertJsonPath('data.stock_alerts.0.status', 'low_stock');
    }

    public function test_customer_cannot_read_admin_dashboard(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/admin/dashboard')->assertForbidden();
    }

    public function test_inventory_endpoint_filters_low_and_out_of_stock_products(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('catalog_manager');

        Product::query()->where('slug', 'miel-de-montagne')->update(['stock_quantity' => 3, 'is_active' => true]);
        Product::query()->where('slug', 'jus-pomme-gingembre')->update(['stock_quantity' => 0, 'is_active' => true]);

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/admin/inventory?status=low_stock&threshold=5&sort=stock_asc')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'miel-de-montagne')
            ->assertJsonPath('data.0.stock_status', 'low_stock')
            ->assertJsonPath('summary.low_stock_threshold', 5);

        $this->getJson('/api/v1/admin/inventory?status=out_of_stock')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'jus-pomme-gingembre')
            ->assertJsonPath('data.0.stock_status', 'out_of_stock');
    }

    public function test_catalog_manager_can_publish_and_unpublish_product_with_audit(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('catalog_manager');
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();

        Sanctum::actingAs($manager);

        $this->postJson("/api/v1/admin/products/{$product->id}/unpublish")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'catalog.products.unpublished',
            'auditable_id' => $product->id,
        ]);

        $this->postJson("/api/v1/admin/products/{$product->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'catalog.products.published',
            'auditable_id' => $product->id,
        ]);

        $this->assertNotNull($product->refresh()->published_at);
    }

    public function test_catalog_manager_can_activate_and_deactivate_category_with_audit(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('catalog_manager');
        $category = Category::query()->where('slug', 'epicerie-fine')->firstOrFail();

        Sanctum::actingAs($manager);

        $this->postJson("/api/v1/admin/categories/{$category->id}/deactivate")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->postJson("/api/v1/admin/categories/{$category->id}/activate")
            ->assertOk()
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'catalog.categories.deactivated',
            'auditable_id' => $category->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'catalog.categories.activated',
            'auditable_id' => $category->id,
        ]);
    }

    public function test_admin_users_index_supports_back_office_filters(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $support = User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Support',
            'email' => 'alice.support@example.test',
            'status' => 'active',
            'country_code' => 'FR',
        ]);
        $support->assignRole('support_agent');

        User::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Suspended',
            'email' => 'bob@example.test',
            'status' => 'suspended',
            'country_code' => 'BE',
        ])->assignRole('support_agent');

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/users?q=alice&role=support_agent&status=active&country_code=fr')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'alice.support@example.test');
    }
}
