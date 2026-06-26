<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\EcommerceSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentsApiTest extends TestCase
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

    public function test_customer_can_create_and_capture_paypal_order(): void
    {
        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'paypal-access-token',
            ]),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAYPAL-ORDER-1',
                'status' => 'CREATED',
                'links' => [[
                    'rel' => 'approve',
                    'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-1',
                ]],
            ]),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/PAYPAL-ORDER-1/capture' => Http::response([
                'id' => 'PAYPAL-ORDER-1',
                'status' => 'COMPLETED',
            ]),
        ]);

        $this->activePayPalMethod();
        $user = $this->customer();
        $order = $this->orderFor($user);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/orders/{$order->id}/payments/paypal/orders", [
            'return_url' => 'https://shop.example.test/paypal/return',
            'cancel_url' => 'https://shop.example.test/paypal/cancel',
        ])
            ->assertOk()
            ->assertJsonPath('data.provider', 'paypal')
            ->assertJsonPath('data.external_id', 'PAYPAL-ORDER-1')
            ->assertJsonPath('data.approval_url', 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-1');

        $this->assertDatabaseHas('order_payments', [
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_reference' => 'PAYPAL-ORDER-1',
            'status' => 'CREATED',
            'amount_cents' => $order->total_cents,
        ]);

        $this->postJson("/api/v1/orders/{$order->id}/payments/paypal/orders/PAYPAL-ORDER-1/capture")
            ->assertOk()
            ->assertJsonPath('data.status', 'captured');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    public function test_paypal_order_payload_omits_relative_return_urls(): void
    {
        config([
            'services.paypal.return_url' => '/checkout/paypal/return',
            'services.paypal.cancel_url' => '/checkout/paypal/cancel',
        ]);

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'paypal-access-token',
            ]),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAYPAL-ORDER-RELATIVE-URL',
                'status' => 'CREATED',
                'links' => [[
                    'rel' => 'approve',
                    'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-RELATIVE-URL',
                ]],
            ]),
        ]);

        $this->activePayPalMethod();
        $user = $this->customer();
        $order = $this->orderFor($user);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/orders/{$order->id}/payments/paypal/orders")
            ->assertOk()
            ->assertJsonPath('data.external_id', 'PAYPAL-ORDER-RELATIVE-URL');

        Http::assertSent(function ($request) {
            if ((string) $request->url() !== 'https://api-m.sandbox.paypal.com/v2/checkout/orders') {
                return false;
            }

            $payload = $request->data();

            return ! isset($payload['application_context']['return_url'])
                && ! isset($payload['application_context']['cancel_url'])
                && ($payload['application_context']['user_action'] ?? null) === 'PAY_NOW';
        });
    }

    public function test_paypal_order_creation_reuses_existing_pending_payment(): void
    {
        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'paypal-access-token',
            ]),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAYPAL-ORDER-REUSED',
                'status' => 'CREATED',
                'links' => [[
                    'rel' => 'approve',
                    'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-REUSED',
                ]],
            ]),
        ]);

        $this->activePayPalMethod();
        $user = $this->customer();
        $order = $this->orderFor($user);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/orders/{$order->id}/payments/paypal/orders")
            ->assertOk()
            ->assertJsonPath('data.external_id', 'PAYPAL-ORDER-REUSED');

        $this->postJson("/api/v1/orders/{$order->id}/payments/paypal/orders")
            ->assertOk()
            ->assertJsonPath('data.external_id', 'PAYPAL-ORDER-REUSED');

        Http::assertSentCount(2);
        $this->assertDatabaseCount('order_payments', 1);
    }

    public function test_stripe_webhook_marks_order_as_paid(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $user = $this->customer();
        $order = $this->orderFor($user);

        OrderPayment::create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'provider_reference' => 'pi_test_123',
            'status' => 'processing',
            'amount_cents' => $order->total_cents,
            'currency' => $order->currency,
            'client_secret' => 'pi_test_123_secret_test',
        ]);

        $this->postJson('/api/v1/payments/stripe/webhook', [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'object' => 'payment_intent',
                    'id' => 'pi_test_123',
                    'status' => 'succeeded',
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
        $this->assertDatabaseHas('order_payments', [
            'provider_reference' => 'pi_test_123',
            'status' => 'captured',
        ]);
    }

    public function test_paypal_webhook_marks_order_as_paid(): void
    {
        $this->activePayPalMethod();
        $user = $this->customer();
        $order = $this->orderFor($user);

        OrderPayment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_reference' => 'PAYPAL-ORDER-2',
            'status' => 'APPROVED',
            'amount_cents' => $order->total_cents,
            'currency' => $order->currency,
        ]);

        $this->postJson('/api/v1/payments/paypal/webhook', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE-1',
                'status' => 'COMPLETED',
                'supplementary_data' => [
                    'related_ids' => [
                        'order_id' => 'PAYPAL-ORDER-2',
                    ],
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
        $this->assertDatabaseHas('order_payments', [
            'provider_reference' => 'PAYPAL-ORDER-2',
            'status' => 'captured',
        ]);
    }

    public function test_customer_cannot_prepare_payment_for_another_customer_order(): void
    {
        $this->activePayPalMethod();
        $owner = $this->customer(['email' => 'owner-payments@example.test']);
        $other = $this->customer(['email' => 'other-payments@example.test']);
        $order = $this->orderFor($owner);

        Sanctum::actingAs($other);

        $this->postJson("/api/v1/orders/{$order->id}/payments/paypal/orders")
            ->assertNotFound();
    }

    private function activePayPalMethod(): PaymentMethod
    {
        return PaymentMethod::create([
            'code' => 'paypal_sandbox',
            'provider' => 'paypal',
            'display_name' => ['fr' => 'PayPal'],
            'environment' => 'sandbox',
            'status' => 'active',
            'is_enabled' => true,
            'currencies' => ['EUR'],
            'countries' => ['FR'],
            'credentials' => [
                'client_id' => 'paypal-client-id',
                'client_secret' => 'paypal-client-secret',
            ],
        ]);
    }

    private function orderFor(User $user): Order
    {
        $address = $this->address($user);
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();

        Sanctum::actingAs($user);

        $cartToken = $this->postJson('/api/v1/carts')
            ->assertCreated()
            ->json('data.cart_token');

        $this->postJson("/api/v1/carts/{$cartToken}/items", [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $orderId = $this->postJson('/api/v1/orders', [
            'cart_token' => $cartToken,
            'shipping_address_id' => $address->id,
            'locale' => 'fr',
        ])->assertCreated()->json('data.id');

        return Order::findOrFail($orderId);
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
}
