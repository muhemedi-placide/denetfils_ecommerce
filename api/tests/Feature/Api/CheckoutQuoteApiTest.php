<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\EcommerceSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutQuoteApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            SupportedCountrySeeder::class,
            AccessControlSeeder::class,
            EcommerceSeeder::class,
        ]);
    }

    public function test_checkout_quote_calculates_france_food_vat_shipping_and_total(): void
    {
        $user = $this->customer();
        $address = $this->address($user, ['country_code' => 'FR']);
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product, 2);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/checkout/quote', [
            'cart_token' => $cartToken,
            'shipping_address_id' => $address->id,
            'locale' => 'fr',
            'delivery_method' => 'standard',
            'carrier' => 'chronopost_home',
        ])
            ->assertOk()
            ->assertJsonPath('data.destination_country.code', 'FR')
            ->assertJsonPath('data.delivery_method', 'standard')
            ->assertJsonPath('data.subtotal_cents', 1780)
            ->assertJsonPath('data.shipping_cents', 590)
            ->assertJsonPath('data.tax_cents', 191)
            ->assertJsonPath('data.total_cents', 2370)
            ->assertJsonPath('data.prices_include_tax', true)
            ->assertJsonPath('data.tax_breakdown.0.type', 'product')
            ->assertJsonPath('data.tax_breakdown.0.rate_percent', 5.5)
            ->assertJsonPath('data.tax_breakdown.0.tax_cents', 93)
            ->assertJsonPath('data.tax_breakdown.1.type', 'shipping')
            ->assertJsonPath('data.tax_breakdown.1.rate_percent', 20)
            ->assertJsonPath('data.tax_breakdown.1.tax_cents', 98);
    }

    public function test_checkout_quote_supports_country_code_without_saved_address(): void
    {
        $user = $this->customer();
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product, 1);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/checkout/quote', [
            'cart_token' => $cartToken,
            'country_code' => 'BE',
            'locale' => 'fr',
            'delivery_method' => 'relay',
        ])
            ->assertOk()
            ->assertJsonPath('data.destination_country.code', 'BE')
            ->assertJsonPath('data.shipping_cents', 790)
            ->assertJsonPath('data.tax_breakdown.0.rate_percent', 6)
            ->assertJsonPath('data.tax_breakdown.1.rate_percent', 21);
    }

    public function test_checkout_quote_zero_rates_non_eu_destination(): void
    {
        $user = $this->customer();
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product, 1);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/checkout/quote', [
            'cart_token' => $cartToken,
            'country_code' => 'CH',
            'delivery_method' => 'standard',
        ])
            ->assertOk()
            ->assertJsonPath('data.destination_country.code', 'CH')
            ->assertJsonPath('data.shipping_cents', 1490)
            ->assertJsonPath('data.tax_cents', 0)
            ->assertJsonPath('data.total_cents', 2380);
    }

    public function test_checkout_quote_uses_free_shipping_threshold(): void
    {
        $user = $this->customer();
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product, 8);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/checkout/quote', [
            'cart_token' => $cartToken,
            'country_code' => 'FR',
        ])
            ->assertOk()
            ->assertJsonPath('data.subtotal_cents', 7120)
            ->assertJsonPath('data.shipping_cents', 0)
            ->assertJsonPath('data.tax_cents', 371)
            ->assertJsonPath('data.total_cents', 7120);
    }

    public function test_checkout_quote_uses_each_products_tax_class(): void
    {
        $user = $this->customer();
        $food = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $standard = Product::query()->whereKeyNot($food->id)->firstOrFail();
        $standard->update(['tax_class' => 'standard']);
        $cartToken = $this->cartWithProduct($food);
        $this->postJson("/api/v1/carts/{$cartToken}/items", [
            'product_id' => $standard->id,
            'quantity' => 1,
        ])->assertCreated();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/checkout/quote', [
            'cart_token' => $cartToken,
            'country_code' => 'FR',
        ])->assertOk();

        $productLines = collect($response->json('data.tax_breakdown'))->where('type', 'product')->values();
        $this->assertSame('food', $productLines[0]['tax_class']);
        $this->assertSame(5.5, $productLines[0]['rate_percent']);
        $this->assertSame('standard', $productLines[1]['tax_class']);
        $this->assertSame(20, $productLines[1]['rate_percent']);
    }

    public function test_checkout_quote_rejects_empty_or_invalid_cart(): void
    {
        $user = $this->customer();
        Sanctum::actingAs($user);

        $emptyCartToken = $this->postJson('/api/v1/carts')
            ->assertCreated()
            ->json('data.cart_token');

        $this->postJson('/api/v1/checkout/quote', [
            'cart_token' => $emptyCartToken,
            'country_code' => 'FR',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart_token');

        $this->postJson('/api/v1/checkout/quote', [
            'cart_token' => 'missing-cart',
            'country_code' => 'FR',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart_token');
    }

    private function customer(array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        $user->assignRole('customer');

        return $user;
    }

    private function address(User $user, array $overrides = []): UserAddress
    {
        return $user->addresses()->create(array_merge([
            'type' => 'shipping',
            'label' => 'Maison',
            'recipient_name' => 'Jean Martin',
            'street_line_1' => '12 Rue du Test',
            'postal_code' => '75001',
            'city' => 'Paris',
            'country_code' => 'FR',
            'phone' => '+33600000000',
            'is_default' => true,
        ], $overrides));
    }

    private function cartWithProduct(Product $product, int $quantity = 1): string
    {
        $cartToken = $this->postJson('/api/v1/carts')
            ->assertCreated()
            ->json('data.cart_token');

        $this->postJson("/api/v1/carts/{$cartToken}/items", [
            'product_id' => $product->id,
            'quantity' => $quantity,
        ])->assertCreated();

        return $cartToken;
    }
}
