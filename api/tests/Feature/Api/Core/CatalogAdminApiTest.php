<?php

namespace Tests\Feature\Api\Core;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\EcommerceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([AccessControlSeeder::class, EcommerceSeeder::class]);
    }

    public function test_customer_cannot_access_admin_catalog(): void
    {
        $customer = Customer::factory()->create();
        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/admin/products')->assertForbidden();
        $this->postJson('/api/v1/admin/categories', [])->assertForbidden();
    }

    public function test_catalog_manager_can_create_category_and_product(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('catalog_manager');
        Sanctum::actingAs($manager);

        $categoryId = $this->postJson('/api/v1/admin/categories', [
            'name' => ['fr' => 'Coffrets Europe', 'en' => 'Europe boxes'],
            'slug' => 'coffrets-europe',
            'sort_order' => 10,
            'is_active' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'coffrets-europe')
            ->json('data.id');

        $this->postJson('/api/v1/admin/products', [
            'category_id' => $categoryId,
            'name' => ['fr' => 'Coffret decouverte', 'en' => 'Discovery box'],
            'slug' => 'coffret-decouverte',
            'description' => [
                'fr' => 'Une selection de produits alimentaires adaptee au marche europeen.',
                'en' => 'A food product selection tailored for the European market.',
            ],
            'short_description' => [
                'fr' => 'Coffret alimentaire premium pour l Europe.',
                'en' => 'Premium food box for Europe.',
            ],
            'origin' => ['fr' => 'Origine Europe', 'en' => 'European origin'],
            'highlights' => [
                'fr' => ['Prix en EUR', 'Livraison UE'],
                'en' => ['EUR pricing', 'EU delivery'],
            ],
            'badges' => [
                'fr' => ['Nouveau'],
                'en' => ['New'],
            ],
            'nutrition_facts' => [
                'serving_basis' => 'per_box',
                'energy_kcal' => 1200,
            ],
            'shipping_profile' => [
                'dispatch_time' => ['fr' => '24 h', 'en' => '24 h'],
                'cold_chain' => false,
            ],
            'return_policy' => [
                'fr' => 'Retour selon conditions alimentaires.',
                'en' => 'Return under food product conditions.',
            ],
            'seo_title' => [
                'fr' => 'Coffret decouverte Denetfils',
                'en' => 'Denetfils discovery box',
            ],
            'seo_description' => [
                'fr' => 'Coffret alimentaire premium avec donnees SEO structurees.',
                'en' => 'Premium food box with structured SEO data.',
            ],
            'seo_keywords' => [
                'fr' => ['coffret', 'alimentaire'],
                'en' => ['box', 'food'],
            ],
            'sku' => 'DEN-BOX-EU-001',
            'price_cents' => 2590,
            'currency' => 'EUR',
            'tax_class' => 'standard',
            'weight_grams' => 1200,
            'stock_quantity' => 25,
            'max_order_quantity' => 6,
            'rating_average' => 4.8,
            'rating_count' => 14,
            'sales_count' => 80,
            'images' => [[
                'url' => 'https://example.com/products/coffret.jpg',
                'width' => 1200,
                'height' => 900,
                'dominant_color' => '#f4efe7',
                'alt_text' => ['fr' => 'Coffret alimentaire.', 'en' => 'Food box.'],
            ]],
            'variants' => [[
                'name' => ['fr' => 'Standard', 'en' => 'Standard'],
                'sku' => 'DEN-BOX-EU-001-STD',
                'price_adjustment_cents' => 0,
                'stock_quantity' => 25,
            ]],
        ])
            ->assertCreated()
            ->assertJsonPath('data.sku', 'DEN-BOX-EU-001')
            ->assertJsonPath('data.currency', 'EUR')
            ->assertJsonPath('data.tax_class', 'standard')
            ->assertJsonPath('data.short_description.en', 'Premium food box for Europe.')
            ->assertJsonPath('data.highlights.en.0', 'EUR pricing')
            ->assertJsonPath('data.max_order_quantity', 6)
            ->assertJsonPath('data.images.0.width', 1200)
            ->assertJsonCount(1, 'data.images')
            ->assertJsonCount(1, 'data.variants');

        $this->assertDatabaseHas('audit_logs', ['action' => 'catalog.categories.created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'catalog.products.created']);
    }

    public function test_catalog_manager_can_update_product_and_deactivate_omitted_variants(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('catalog_manager');
        Sanctum::actingAs($manager);

        $product = Product::query()
            ->where('slug', 'miel-de-montagne')
            ->with('variants')
            ->firstOrFail();

        $keptVariant = $product->variants->first();
        $omittedVariant = $product->variants->last();

        $this->patchJson("/api/v1/admin/products/{$product->id}", [
            'stock_quantity' => 12,
            'is_active' => false,
            'variants' => [
                [
                    'id' => $keptVariant->id,
                    'name' => ['fr' => 'Pot 250 g', 'en' => '250 g jar'],
                    'sku' => $keptVariant->sku,
                    'price_adjustment_cents' => 0,
                    'stock_quantity' => 12,
                    'is_active' => true,
                ],
                [
                    'name' => ['fr' => 'Coffret 3 pots', 'en' => '3 jar box'],
                    'sku' => 'DEN-MIEL-3POTS',
                    'price_adjustment_cents' => 1200,
                    'stock_quantity' => 6,
                    'is_active' => true,
                ],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.stock_quantity', 12)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('product_variants', [
            'id' => $omittedVariant->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'sku' => 'DEN-MIEL-3POTS',
            'product_id' => $product->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'catalog.products.updated']);
    }

    public function test_catalog_admin_validation_rejects_duplicate_slugs_and_skus(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('catalog_manager');
        Sanctum::actingAs($manager);

        $this->postJson('/api/v1/admin/categories', [
            'name' => ['fr' => 'Epicerie fine', 'en' => 'Fine groceries'],
            'slug' => 'epicerie-fine',
        ])->assertUnprocessable();

        $category = Category::query()->firstOrFail();

        $this->postJson('/api/v1/admin/products', [
            'category_id' => $category->id,
            'name' => ['fr' => 'Produit test', 'en' => 'Test product'],
            'slug' => 'produit-test',
            'description' => ['fr' => 'Description test.', 'en' => 'Test description.'],
            'sku' => 'DEN-MIEL-250',
            'price_cents' => 1000,
            'stock_quantity' => 5,
        ])->assertUnprocessable();
    }
}
