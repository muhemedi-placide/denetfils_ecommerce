<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\EcommerceSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrdersApiTest extends TestCase
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

    public function test_authenticated_customer_can_create_order_from_cart(): void
    {
        $user = $this->customer();
        $address = $this->address($user);
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product, 2);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/orders', [
            'cart_token' => $cartToken,
            'shipping_address_id' => $address->id,
            'locale' => 'fr',
            'delivery_method' => 'standard',
            'carrier' => 'chronopost_home',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending_payment')
            ->assertJsonPath('data.payment_status', 'unpaid')
            ->assertJsonPath('data.fulfillment_status', 'unfulfilled')
            ->assertJsonPath('data.customer.email', $user->email)
            ->assertJsonPath('data.shipping_cents', 590)
            ->assertJsonPath('data.tax_cents', 216)
            ->assertJsonPath('data.total_cents', 2586)
            ->assertJsonPath('data.items.0.product.name', 'Miel de montagne')
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.addresses.0.type', 'shipping')
            ->assertJsonPath('data.addresses.1.type', 'billing')
            ->assertJsonPath('data.carrier', 'chronopost_home');

        $this->assertMatchesRegularExpression('/^DF-\d{8}-[A-Z0-9]{6}$/', $response->json('data.order_number'));
        $this->assertDatabaseHas('orders', [
            'id' => $response->json('data.id'),
            'user_id' => $user->id,
            'cart_id' => $this->cartId($cartToken),
            'shipping_cents' => 590,
            'tax_cents' => 216,
            'total_cents' => 2586,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $response->json('data.id'),
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price_cents' => $product->price_cents,
            'line_total_cents' => $product->price_cents * 2,
        ]);
        $this->assertDatabaseCount('order_addresses', 2);
        $this->assertSame($product->stock_quantity, $product->fresh()->stock_quantity);
    }

    public function test_customer_can_list_and_read_only_own_orders(): void
    {
        $owner = $this->customer(['email' => 'owner@example.test']);
        $other = $this->customer(['email' => 'other@example.test']);
        $ownerAddress = $this->address($owner);
        $otherAddress = $this->address($other);
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();

        Sanctum::actingAs($owner);
        $ownerOrderId = $this->postJson('/api/v1/orders', [
            'cart_token' => $this->cartWithProduct($product),
            'shipping_address_id' => $ownerAddress->id,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($other);
        $otherOrderId = $this->postJson('/api/v1/orders', [
            'cart_token' => $this->cartWithProduct($product),
            'shipping_address_id' => $otherAddress->id,
        ])->assertCreated()->json('data.id');

        $this->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $otherOrderId);

        $this->getJson("/api/v1/orders/{$otherOrderId}")
            ->assertOk()
            ->assertJsonPath('data.id', $otherOrderId);

        $this->getJson("/api/v1/orders/{$ownerOrderId}")
            ->assertNotFound();
    }

    public function test_order_creation_rejects_empty_cart_invalid_address_and_duplicate_cart(): void
    {
        $user = $this->customer();
        $address = $this->address($user);

        Sanctum::actingAs($user);

        $emptyCartToken = $this->postJson('/api/v1/carts')
            ->assertCreated()
            ->json('data.cart_token');

        $this->postJson('/api/v1/orders', [
            'cart_token' => $emptyCartToken,
            'shipping_address_id' => $address->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart_token');

        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product);

        $this->postJson('/api/v1/orders', [
            'cart_token' => $cartToken,
            'shipping_address_id' => 999999,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('shipping_address_id');

        $this->postJson('/api/v1/orders', [
            'cart_token' => $cartToken,
            'shipping_address_id' => $address->id,
        ])->assertCreated();

        $this->postJson('/api/v1/orders', [
            'cart_token' => $cartToken,
            'shipping_address_id' => $address->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart_token');
    }

    public function test_order_creation_revalidates_current_stock(): void
    {
        $user = $this->customer();
        $address = $this->address($user);
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product, 2);
        $product->update(['stock_quantity' => 1]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/orders', [
            'cart_token' => $cartToken,
            'shipping_address_id' => $address->id,
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

    private function cartId(string $cartToken): int
    {
        return (int) \App\Models\Cart::query()
            ->where('cart_token', $cartToken)
            ->value('id');
    }
}
