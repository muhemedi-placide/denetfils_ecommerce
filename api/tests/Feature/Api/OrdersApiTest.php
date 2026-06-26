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

    public function test_order_creation_rejects_empty_cart_and_invalid_address(): void
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
    }

    public function test_order_creation_is_idempotent_for_same_customer_cart(): void
    {
        $user = $this->customer();
        $address = $this->address($user);
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product);

        Sanctum::actingAs($user);

        $firstOrderId = $this->postJson('/api/v1/orders', [
            'cart_token' => $cartToken,
            'shipping_address_id' => $address->id,
        ])
            ->assertCreated()
            ->json('data.id');

        $this->postJson('/api/v1/orders', [
            'cart_token' => $cartToken,
            'shipping_address_id' => $address->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.id', $firstOrderId);

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_converted_cart_cannot_be_reused_by_another_customer(): void
    {
        $owner = $this->customer(['email' => 'owner-cart@example.test']);
        $other = $this->customer(['email' => 'other-cart@example.test']);
        $ownerAddress = $this->address($owner);
        $otherAddress = $this->address($other);
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->cartWithProduct($product);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/orders', [
            'cart_token' => $cartToken,
            'shipping_address_id' => $ownerAddress->id,
        ])->assertCreated();

        Sanctum::actingAs($other);

        $this->postJson('/api/v1/orders', [
            'cart_token' => $cartToken,
            'shipping_address_id' => $otherAddress->id,
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

    public function test_operations_manager_can_list_and_update_orders_with_audit(): void
    {
        $customer = $this->customer();
        $address = $this->address($customer);
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();

        Sanctum::actingAs($customer);

        $orderId = $this->postJson('/api/v1/orders', [
            'cart_token' => $this->cartWithProduct($product),
            'shipping_address_id' => $address->id,
            'delivery_method' => 'relay',
            'carrier' => 'mondial_relay_pickup',
        ])->assertCreated()->json('data.id');

        $manager = User::factory()->create();
        $manager->assignRole('operations_manager');

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/admin/orders?status=pending_payment&locale=fr')
            ->assertOk()
            ->assertJsonPath('data.0.id', $orderId)
            ->assertJsonPath('data.0.status_label', 'Paiement en attente')
            ->assertJsonPath('data.0.is_new_customer', true)
            ->assertJsonPath('summary.total_orders', 1)
            ->assertJsonPath('summary.to_prepare_orders', 1)
            ->assertJsonPath('summary.abandoned_carts', 0)
            ->assertJsonPath('summary.conversion_rate_percent', 100);

        $this->patchJson("/api/v1/admin/orders/{$orderId}", [
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'fulfillment_status' => 'preparing',
            'carrier' => 'chrono_relais_pickup',
            'tracking_number' => 'CR123456789FR',
            'tracking_url' => 'https://tracking.example.test/CR123456789FR',
            'admin_note' => 'Preparation prioritaire.',
            'order_state' => 'processing',
            'notify_customer' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.payment_status', 'paid')
            ->assertJsonPath('data.fulfillment_status', 'preparing')
            ->assertJsonPath('data.metadata.order_state', 'processing')
            ->assertJsonPath('data.tracking.number', 'CR123456789FR')
            ->assertJsonPath('data.admin_notes.0.body', 'Preparation prioritaire.');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'fulfillment_status' => 'preparing',
            'carrier' => 'chrono_relais_pickup',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'orders.status_updated',
            'auditable_id' => $orderId,
        ]);

        $adminCreateResponse = $this->postJson('/api/v1/admin/orders', [
            'user_id' => $customer->id,
            'cart_token' => $this->cartWithProduct($product),
            'shipping_address_id' => $address->id,
            'delivery_method' => 'standard',
            'carrier' => 'chronopost_home',
            'metadata' => [
                'admin_note' => 'Commande creee par le back-office.',
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.carrier', 'chronopost_home')
            ->assertJsonPath('data.metadata.created_from', 'admin')
            ->assertJsonPath('data.admin_notes.0.body', 'Commande creee par le back-office.');

        $adminCreatedOrderId = $adminCreateResponse->json('data.id');
        $adminCreatedTotal = number_format(((int) $adminCreateResponse->json('data.total_cents')) / 100, 2, '.', '');

        $this->getJson("/api/v1/admin/orders?new_customer=0&customer={$customer->email}&total={$adminCreatedTotal}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $adminCreatedOrderId)
            ->assertJsonPath('data.0.is_new_customer', false);
    }

    public function test_customer_cannot_read_admin_orders(): void
    {
        $customer = $this->customer();

        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/admin/orders')->assertForbidden();
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
