<?php

namespace Tests\Feature;

use App\Livewire\Shop\CartManager;
use App\Livewire\Shop\CartPage;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
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
            ->assertSee('cart:add', false)
            ->assertSee('id="mobile-menu-state"', false)
            ->assertSee('data-mobile-menu-toggle', false)
            ->assertSee('data-testid="mobile-cart-open-button"', false)
            ->assertSee('pointer-events-none fixed', false)
            ->assertSee('Paiement sécurisé : Visa, Mastercard, Apple Pay, Google Pay, PayPal.')
            ->assertDontSee(__('home.cart.subtitle'))
            ->assertDontSee('TVA UE')
            ->assertDontSee('Securise')
            ->assertDontSee('Moyens de paiement acceptés')
            ->assertDontSee('Etape suivante')
            ->assertDontSee('x-trap.noscroll', false)
            ->assertDontSee('bg-black/45 backdrop-blur-sm', false)
            ->assertDontSee("classList.toggle('overflow-hidden'", false)
            ->assertSee('livewire', false);
    }

    public function test_english_catalog_displays_products_from_api(): void
    {
        $this->withoutVite();
        $this->fakeCatalog('Mountain honey', 'French origin', 'Fine groceries');

        $this->get('/en')
            ->assertOk()
            ->assertSee('Marché Peyi makes Caribbean, Haitian and African flavors easy to find, cook and share every day.')
            ->assertSee('Mountain honey')
            ->assertSee('French origin');
    }

    public function test_catalog_sends_filters_to_api(): void
    {
        $this->withoutVite();
        $this->fakeCatalog('Hibiscus infusion', 'Senegalese origin', 'Natural drinks');

        $this->get('/en/boutique?category=boissons-naturelles&q=hibiscus&sort=price_desc')
            ->assertOk()
            ->assertSee('Hibiscus infusion')
            ->assertSee('filterCategory', false)
            ->assertSee('Natural drinks')
            ->assertSee('wire:model="sort"', false)
            ->assertSee('value="price_desc"', false);

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
            ->assertSee('Premium selection')
            ->assertSee('Prepared within 24 to 48 business hours.')
            ->assertSee('application/ld+json', false)
            ->assertSee('wire:click="addToCart"', false);
    }

    public function test_livewire_cart_manager_creates_guest_cart_and_adds_product(): void
    {
        Http::fake([
            '*/carts/cart-token-123/items' => Http::response([
                'data' => $this->cart([
                    $this->cartItem(),
                ]),
            ]),
            '*/carts' => Http::response([
                'data' => $this->cart(),
            ], 201),
        ]);

        Livewire::test(CartManager::class, ['locale' => 'en'])
            ->call('addToCart', 10)
            ->assertSet('cartToken', 'cart-token-123')
            ->assertSet('isOpen', false);

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/carts')
            && $request->method() === 'POST');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/carts/cart-token-123/items')
            && $request->method() === 'POST'
            && $request['product_id'] === 10
            && $request['quantity'] === 1);
    }

    public function test_cart_page_creates_a_secure_recovery_url(): void
    {
        Http::fake([
            '*/carts/cart-token-123/recovery-links' => Http::response([
                'data' => [
                    'token' => str_repeat('r', 64),
                    'expires_at' => now()->addDays(30)->toIso8601String(),
                ],
            ], 201),
        ]);

        Livewire::test(CartPage::class, ['locale' => 'fr'])
            ->set('cartToken', 'cart-token-123')
            ->set('cart', $this->cart([$this->cartItem()]))
            ->call('createRecoveryLink')
            ->assertSet('recoveryUrl', route('cart.recover', [
                'locale' => 'fr',
                'recoveryToken' => str_repeat('r', 64),
            ]))
            ->assertSee('Copier');
    }

    public function test_recovery_route_restores_the_cart_from_api(): void
    {
        $recoveryToken = str_repeat('r', 64);

        Http::fake([
            '*/cart-recoveries/*' => Http::response([
                'data' => $this->cart([$this->cartItem()]),
            ]),
        ]);

        Livewire::test(CartPage::class, [
            'locale' => 'en',
            'recoveryToken' => $recoveryToken,
        ])
            ->assertSet('recoveredFromLink', true)
            ->assertSet('cartToken', 'cart-token-123')
            ->assertSee('Cart restored')
            ->assertSee('Mountain honey');
    }

    private function fakeCatalog(string $name, string $origin, string $categoryName): void
    {
        Http::fake([
            '*/seo/site*' => Http::response([
                'data' => $this->siteSeo(),
            ]),
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
                    $this->product($name, $origin, $categoryName),
                ],
            ]),
        ]);
    }

    private function product(string $name, string $origin, string $categoryName = 'Fine groceries'): array
    {
        return [
            'id' => 10,
            'category' => [
                'id' => 1,
                'slug' => 'epicerie-fine',
                'name' => $categoryName,
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
            'short_description' => 'A dense floral honey selected for breakfast and dessert.',
            'rich_content' => [
                'badges' => ['Best seller', 'EU delivery'],
                'highlights' => ['Premium selection', 'Careful preparation'],
                'tags' => ['honey', 'premium'],
                'ingredients' => 'Honey.',
                'allergens' => ['May contain traces of nuts.'],
                'nutrition_facts' => ['serving_basis' => 'per_100g'],
                'certifications' => ['Verified supplier'],
                'storage_instructions' => 'Store in a cool, dry place.',
                'usage_instructions' => 'Use with breakfast or dessert.',
            ],
            'commerce' => [
                'brand' => 'Denetfils',
                'availability' => 'in_stock',
                'is_available' => true,
                'max_order_quantity' => 12,
                'rating' => ['average' => 4.8, 'count' => 38],
                'sales_count' => 240,
                'shipping' => [
                    'dispatch_time' => 'Prepared within 24 to 48 business hours.',
                    'delivery_zone' => 'France and supported European countries.',
                ],
                'return_policy' => 'Food products cannot be returned after opening.',
                'guarantee' => 'Quality check before dispatch.',
            ],
            'seo' => [
                'meta' => [
                    'title' => $name . ' | Denetfils',
                    'description' => 'Shop ' . $name . ' with structured product data.',
                    'robots' => 'index,follow',
                ],
                'canonical' => 'http://127.0.0.1:8001/en/products/miel-de-montagne',
                'hreflang' => [
                    ['locale' => 'fr', 'hreflang' => 'fr-FR', 'url' => 'http://127.0.0.1:8001/fr/products/miel-de-montagne'],
                    ['locale' => 'en', 'hreflang' => 'en', 'url' => 'http://127.0.0.1:8001/en/products/miel-de-montagne'],
                ],
                'open_graph' => [
                    'type' => 'product',
                    'title' => $name,
                    'description' => 'Shop ' . $name,
                    'image' => 'https://example.test/honey.jpg',
                ],
                'twitter_card' => [
                    'card' => 'summary_large_image',
                    'title' => $name,
                    'description' => 'Shop ' . $name,
                    'image' => 'https://example.test/honey.jpg',
                ],
                'json_ld' => [
                    'product' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'Product',
                        'name' => $name,
                    ],
                ],
            ],
            'primary_image' => [
                'id' => 1,
                'url' => 'https://example.test/honey.jpg',
                'alt_text' => 'Jar of honey.',
                'width' => 1200,
                'height' => 900,
                'loading' => 'eager',
                'fetch_priority' => 'high',
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

    private function siteSeo(): array
    {
        return [
            'meta' => [
                'title' => 'Denetfils - Premium food shop',
                'description' => 'Discover curated grocery products, natural drinks and premium food boxes for Europe.',
                'robots' => 'index,follow',
            ],
            'canonical' => 'http://127.0.0.1:8001/en',
            'hreflang' => [
                ['locale' => 'fr', 'hreflang' => 'fr-FR', 'url' => 'http://127.0.0.1:8001/fr'],
                ['locale' => 'en', 'hreflang' => 'en', 'url' => 'http://127.0.0.1:8001/en'],
            ],
            'open_graph' => ['type' => 'website', 'title' => 'Denetfils'],
            'twitter_card' => ['card' => 'summary'],
            'json_ld' => [
                'organization' => ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'Denetfils'],
            ],
        ];
    }

    private function cart(array $items = []): array
    {
        return [
            'cart_token' => 'cart-token-123',
            'subtotal_cents' => 890,
            'tax_cents' => 0,
            'total_cents' => 890,
            'formatted_total' => 'EUR 8.90',
            'formatted_subtotal' => 'EUR 8.90',
            'items_count' => count($items),
            'reference' => 'CRT-TEST123',
            'items' => $items,
        ];
    }

    private function cartItem(): array
    {
        return [
            'id' => 55,
            'quantity' => 1,
            'line_total_cents' => 890,
            'unit_price_cents' => 890,
            'formatted_unit_price' => 'EUR 8.90',
            'formatted_line_total' => 'EUR 8.90',
            'product' => [
                'id' => 10,
                'name' => 'Mountain honey',
                'slug' => 'miel-de-montagne',
                'sku' => 'DEN-MIEL-250',
                'stock_quantity' => 35,
                'origin' => 'French origin',
                'image' => [
                    'url' => 'https://example.test/honey.jpg',
                    'alt_text' => 'Jar of honey.',
                ],
            ],
            'variant' => null,
        ];
    }
}
