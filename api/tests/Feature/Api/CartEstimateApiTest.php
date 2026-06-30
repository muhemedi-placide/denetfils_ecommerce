<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\ShippingCarrier;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\EcommerceSeeder;
use Database\Seeders\ShippingDomainSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartEstimateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            SupportedCountrySeeder::class,
            AccessControlSeeder::class,
            EcommerceSeeder::class,
            ShippingDomainSeeder::class,
        ]);
        ShippingCarrier::query()->where('code', 'mondial_relay')->update([
            'is_enabled' => true,
            'status' => 'active',
        ]);
    }

    public function test_guest_receives_tax_inclusive_cart_estimate(): void
    {
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product);

        $this->postJson("/api/v1/carts/{$cartToken}/estimate", [
            'country_code' => 'FR',
            'locale' => 'fr',
        ])
            ->assertOk()
            ->assertJsonPath('data.is_estimate', true)
            ->assertJsonPath('data.is_supported', true)
            ->assertJsonPath('data.prices_include_tax', true)
            ->assertJsonPath('data.destination_country.code', 'FR')
            ->assertJsonPath('data.subtotal_cents', 890)
            ->assertJsonPath('data.shipping_from_cents', 390)
            ->assertJsonPath('data.tax_cents', 111)
            ->assertJsonPath('data.total_cents', 1280)
            ->assertJsonCount(2, 'data.shipping_options');
    }

    public function test_guest_estimate_reports_unsupported_country_without_false_amounts(): void
    {
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product);

        $this->postJson("/api/v1/carts/{$cartToken}/estimate", [
            'country_code' => 'US',
            'locale' => 'en',
        ])
            ->assertOk()
            ->assertJsonPath('data.is_supported', false)
            ->assertJsonPath('data.destination_country.code', 'US')
            ->assertJsonPath('data.shipping_cents', null)
            ->assertJsonPath('data.tax_cents', null)
            ->assertJsonPath('data.total_cents', null);
    }

    public function test_guest_estimate_rejects_empty_and_invalid_carts(): void
    {
        $empty = $this->postJson('/api/v1/carts')->assertCreated()->json('data.cart_token');

        $this->postJson("/api/v1/carts/{$empty}/estimate", ['country_code' => 'FR'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart_token');

        $this->postJson('/api/v1/carts/missing/estimate', ['country_code' => 'FR'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart_token');
    }

    private function cartWithProduct(Product $product): string
    {
        $token = $this->postJson('/api/v1/carts')->assertCreated()->json('data.cart_token');
        $this->postJson("/api/v1/carts/{$token}/items", [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        return $token;
    }
}
