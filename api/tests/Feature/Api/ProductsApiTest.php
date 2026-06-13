<?php

namespace Tests\Feature\Api;

use Database\Seeders\EcommerceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_list_returns_localized_french_products(): void
    {
        $this->seed(EcommerceSeeder::class);

        $response = $this->getJson('/api/v1/products?locale=fr');

        $response->assertOk();

        $this->assertGreaterThanOrEqual(6, count($response->json('data')));
        $this->assertSame('Miel de montagne', $response->json('data.0.name'));
        $this->assertSame('Origine France', $response->json('data.0.origin'));
        $this->assertIsString($response->json('data.0.formatted_price'));
    }

    public function test_products_list_returns_localized_english_products(): void
    {
        $this->seed(EcommerceSeeder::class);

        $response = $this->getJson('/api/v1/products?locale=en');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Mountain honey')
            ->assertJsonPath('data.0.origin', 'French origin');
    }

    public function test_product_detail_returns_product_by_slug(): void
    {
        $this->seed(EcommerceSeeder::class);

        $response = $this->getJson('/api/v1/products/miel-de-montagne?locale=en');

        $response
            ->assertOk()
            ->assertJsonPath('data.slug', 'miel-de-montagne')
            ->assertJsonPath('data.name', 'Mountain honey')
            ->assertJsonPath('data.currency', 'EUR')
            ->assertJsonPath('data.primary_image.alt_text', 'Jar of artisanal honey.')
            ->assertJsonPath('data.primary_image.loading', 'eager')
            ->assertJsonPath('data.primary_image.fetch_priority', 'high')
            ->assertJsonPath('data.rich_content.highlights.0', 'Premium selection')
            ->assertJsonPath('data.commerce.availability', 'in_stock')
            ->assertJsonPath('data.seo.open_graph.type', 'product')
            ->assertJsonPath('data.seo.json_ld.product.@type', 'Product')
            ->assertJsonPath('data.seo.json_ld.breadcrumb.@type', 'BreadcrumbList');

        $this->assertStringContainsString(
            '/en/products/miel-de-montagne',
            $response->json('data.seo.canonical'),
        );

        $this->assertGreaterThanOrEqual(4, count($response->json('data.primary_image.sources')));
    }

    public function test_products_can_be_filtered_by_category_and_search(): void
    {
        $this->seed(EcommerceSeeder::class);

        $categoryResponse = $this->getJson('/api/v1/products?locale=fr&category=boissons-naturelles');

        $categoryResponse
            ->assertOk()
            ->assertJsonPath('data.0.category.slug', 'boissons-naturelles');

        $this->assertContains(
            'Jus pomme gingembre',
            collect($categoryResponse->json('data'))->pluck('name')->all(),
        );

        $searchResponse = $this->getJson('/api/v1/products?locale=fr&q=hibiscus');

        $searchResponse
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'infusion-hibiscus');
    }

    public function test_products_can_be_sorted_by_price(): void
    {
        $this->seed(EcommerceSeeder::class);

        $response = $this->getJson('/api/v1/products?locale=fr&sort=price_desc');

        $response->assertOk();

        $prices = collect($response->json('data'))->pluck('price_cents')->all();
        $sortedPrices = $prices;
        rsort($sortedPrices);

        $this->assertSame($sortedPrices, $prices);
    }
}
