<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShopFrontendTest extends TestCase
{
    public function test_french_catalog_displays_products_from_api(): void
    {
        $this->withoutVite();
        $this->fakeCatalog('Miel de montagne', 'Origine France', 'Epicerie fine');

        $this->get('/fr')
            ->assertOk()
            ->assertSee('Miel de montagne')
            ->assertSee('Origine France')
            ->assertSee('Epicerie fine')
            ->assertSee('addToCart(10)', false);

        $this->assertStringContainsString('denetfils_cart_token', file_get_contents(resource_path('js/app.js')));
    }

    public function test_english_catalog_displays_products_from_api(): void
    {
        $this->withoutVite();
        $this->fakeCatalog('Mountain honey', 'French origin', 'Fine groceries');

        $this->get('/en')
            ->assertOk()
            ->assertSee('Reliable flavors, delivered with precision.')
            ->assertSee('Mountain honey')
            ->assertSee('French origin');
    }

    public function test_catalog_sends_filters_to_api(): void
    {
        $this->withoutVite();
        $this->fakeCatalog('Hibiscus infusion', 'Senegalese origin', 'Natural drinks');

        $this->get('/en?category=boissons-naturelles&q=hibiscus&sort=price_desc')
            ->assertOk()
            ->assertSee('Hibiscus infusion')
            ->assertSee('value="hibiscus"', false)
            ->assertSee('value="boissons-naturelles" selected', false)
            ->assertSee('value="price_desc" selected', false);

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/products')
            && $request['category'] === 'boissons-naturelles'
            && $request['q'] === 'hibiscus'
            && $request['sort'] === 'price_desc');
    }

    public function test_product_detail_displays_price_image_and_description(): void
    {
        $this->withoutVite();
        Http::fake([
            '*' => Http::response(['data' => $this->product('Mountain honey', 'French origin')]),
        ]);

        $this->get('/en/products/miel-de-montagne')
            ->assertOk()
            ->assertSee('Mountain honey')
            ->assertSee('A dense floral honey')
            ->assertSee('EUR 8.90')
            ->assertSee('https://example.test/honey.jpg')
            ->assertSee('addToCart(10, variantId)', false);
    }

    private function fakeCatalog(string $name, string $origin, string $categoryName): void
    {
        Http::fake([
            '*/categories*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'slug' => 'epicerie-fine',
                        'name' => $categoryName,
                        'products_count' => 1,
                    ],
                    [
                        'id' => 2,
                        'slug' => 'boissons-naturelles',
                        'name' => $categoryName,
                        'products_count' => 1,
                    ],
                ],
            ]),
            '*/products*' => Http::response([
                'data' => [
                    $this->product($name, $origin),
                ],
            ]),
        ]);
    }

    private function product(string $name, string $origin): array
    {
        return [
            'id' => 10,
            'category' => [
                'id' => 1,
                'slug' => 'epicerie-fine',
                'name' => 'Fine groceries',
            ],
            'name' => $name,
            'slug' => 'miel-de-montagne',
            'description' => 'A dense floral honey for breakfasts and desserts.',
            'origin' => $origin,
            'sku' => 'DEN-MIEL-250',
            'price_cents' => 890,
            'formatted_price' => 'EUR 8.90',
            'currency' => 'EUR',
            'weight_grams' => 250,
            'stock_quantity' => 35,
            'is_active' => true,
            'primary_image' => [
                'id' => 1,
                'url' => 'https://example.test/honey.jpg',
                'alt_text' => 'Jar of honey.',
            ],
            'images' => [],
            'variants' => [
                [
                    'id' => 3,
                    'name' => '250 g jar',
                    'sku' => 'DEN-MIEL-250-A',
                    'price_adjustment_cents' => 0,
                    'price_cents' => 890,
                    'formatted_price' => 'EUR 8.90',
                    'currency' => 'EUR',
                    'stock_quantity' => 35,
                    'is_active' => true,
                ],
            ],
        ];
    }
}
