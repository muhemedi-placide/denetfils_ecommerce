<?php

namespace Tests\Feature;

use App\Livewire\Shop\CheckoutReview;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutFrontendTest extends TestCase
{
    public function test_guest_checkout_prompts_account_connection(): void
    {
        $this->withoutVite();
        Http::fake([
            '*/supported-countries*' => Http::response(['data' => $this->countries()]),
        ]);

        $this->get('/fr/commande')
            ->assertOk()
            ->assertDontSee('Vérifier avant paiement.')
            ->assertDontSee('Le panier vient')
            ->assertSee('Connexion requise pour continuer vers la livraison.')
            ->assertSee('Connectez-vous pour sélectionner une adresse.')
            ->assertSee('wire:submit.prevent="confirm"', false)
            ->assertSee('noindex,nofollow', false);
    }

    public function test_authenticated_checkout_displays_profile_and_saved_addresses(): void
    {
        $this->withoutVite();
        $this->fakeAuthenticatedCheckout();

        $this->withSession(['customer_api_token' => 'checkout-token'])
            ->get('/fr/commande')
            ->assertOk()
            ->assertSee('Jean Martin')
            ->assertSee('12 Rue du Test')
            ->assertSee('France')
            ->assertSee('Créer la commande')
            ->assertSee('Checkout progress', false)
            ->assertSee('Transporteur')
            ->assertDontSee('Chronopost domicile')
            ->assertDontSee('Locker 24/7 Bricomarché Chalons')
            ->assertSee('TVA')
            ->assertSee('wire:model.live="selectedAddressId"', false);

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/me')
            && $request->hasHeader('Authorization', 'Bearer checkout-token'));

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/me/addresses')
            && $request->hasHeader('Authorization', 'Bearer checkout-token'));
    }

    public function test_expired_checkout_session_falls_back_to_guest_prompt(): void
    {
        $this->withoutVite();
        Http::fake([
            '*/me' => Http::response(['message' => 'Unauthenticated.'], 401),
            '*/supported-countries*' => Http::response(['data' => $this->countries()]),
        ]);

        $this->withSession(['customer_api_token' => 'expired-token'])
            ->get('/en/commande')
            ->assertOk()
            ->assertSessionMissing('customer_api_token')
            ->assertSee('Sign-in is required to continue to delivery.');
    }

    public function test_checkout_restore_fetches_real_quote(): void
    {
        Http::fake([
            '*/carts/cart-token-123*' => Http::response(['data' => $this->cart([$this->cartItem()])]),
            '*/shipping/methods*' => Http::response(['data' => $this->shippingMethods()]),
            '*/shipping/pickup-points/search' => Http::response(['data' => ['source' => 'mondial_relay', 'points' => [$this->pickupPoint()]]]),
            '*/shipping/selection' => Http::response(['data' => ['id' => 99]]),
            '*/checkout/quote' => Http::response(['data' => $this->quote()]),
        ]);

        $this->withSession(['customer_api_token' => 'checkout-token']);

        Livewire::test(CheckoutReview::class, [
            'locale' => 'fr',
            'user' => $this->user(),
            'addresses' => [$this->address()],
            'countries' => $this->countries(),
        ])
            ->call('restoreFromBrowser', 'cart-token-123')
            ->assertSet('pickupPointOptions.0.code', 'mr-paris-11')
            ->assertSet('selectedPickupPoint', 'mr-paris-11')
            ->assertSet('quote.total_cents', 1586)
            ->assertSet('quote.shipping_cents', 590)
            ->assertSet('quote.tax_cents', 106);

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/checkout/quote')
            && $request->hasHeader('Authorization', 'Bearer checkout-token')
            && $request['cart_token'] === 'cart-token-123'
            && (int) $request['shipping_address_id'] === 11
            && $request['delivery_method'] === 'relay'
            && $request['carrier'] === 'mondial_relay_point_relais'
            && (int) $request['shipping_method_id'] === 1);

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/shipping/pickup-points/search')
            && $request['cart_token'] === 'cart-token-123'
            && (int) $request['shipping_address_id'] === 11
            && ! isset($request['postal_code'])
            && ! isset($request['city']));
    }

    public function test_checkout_confirmation_creates_order_and_prepares_stripe_payment(): void
    {
        Http::fake([
            '*/orders' => Http::response(['data' => $this->order()], 201),
            '*/orders/22/payments/stripe/payment-intent' => Http::response(['data' => $this->stripePaymentIntent()]),
        ]);

        $this->withSession(['customer_api_token' => 'checkout-token']);

        Livewire::test(CheckoutReview::class, [
            'locale' => 'fr',
            'user' => $this->user(),
            'addresses' => [$this->address()],
            'countries' => $this->countries(),
        ])
            ->set('cartToken', 'cart-token-123')
            ->set('cart', $this->cart([$this->cartItem()]))
            ->set('shippingMethods', $this->shippingMethods())
            ->set('selectedShippingMethodId', 1)
            ->set('carrier', 'mondial_relay_point_relais')
            ->set('pickupPointOptions', [$this->pickupPoint()])
            ->set('selectedPickupPoint', 'mr-paris-11')
            ->call('confirm')
            ->assertSet('orderConfirmed', false)
            ->assertSet('confirmedOrder.order_number', 'DF-20260616-ABC123')
            ->assertSet('stripePaymentIntent.client_secret', 'pi_test_123_secret_test')
            ->assertSet('cartToken', 'cart-token-123')
            ->assertDispatched('stripe-payment-ready');

        Http::assertSent(fn ($request) => preg_match('#/orders$#', (string) $request->url())
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer checkout-token')
            && $request['cart_token'] === 'cart-token-123'
            && (int) $request['shipping_address_id'] === 11
            && $request['delivery_method'] === 'relay'
            && $request['carrier'] === 'mondial_relay_point_relais'
            && (int) $request['shipping_method_id'] === 1
            && (int) $request['pickup_point_id'] === 101
            && $request['metadata']['pickup_point']['code'] === 'mr-paris-11');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/orders/22/payments/stripe/payment-intent')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer checkout-token'));
    }

    public function test_successful_stripe_payment_clears_cart_and_confirms_checkout(): void
    {
        Http::fake([
            '*/orders/22/payments/stripe/payment-intent/confirm' => Http::response(['data' => [
                ...$this->stripePaymentIntent(),
                'status' => 'captured',
                'order' => [
                    'id' => 22,
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                    'fulfillment_status' => 'preparing',
                ],
            ]]),
        ]);

        $this->withSession(['customer_api_token' => 'checkout-token']);

        Livewire::test(CheckoutReview::class, [
            'locale' => 'fr',
            'user' => $this->user(),
            'addresses' => [$this->address()],
            'countries' => $this->countries(),
        ])
            ->set('cartToken', 'cart-token-123')
            ->set('cart', $this->cart([$this->cartItem()]))
            ->set('confirmedOrder', $this->order())
            ->set('stripePaymentIntent', $this->stripePaymentIntent())
            ->call('completeStripePayment', 'pi_test_123')
            ->assertSet('orderConfirmed', true)
            ->assertSet('confirmedOrder.payment_status', 'paid')
            ->assertSet('confirmedOrder.fulfillment_status', 'preparing')
            ->assertSet('cartToken', null)
            ->assertSet('cart.items', [])
            ->assertDispatched('cart-token-cleared')
            ->assertDispatched('cart:cleared')
            ->assertDispatched('checkout-confirmed');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/orders/22/payments/stripe/payment-intent/confirm')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer checkout-token')
            && $request['payment_intent_id'] === 'pi_test_123');
    }

    public function test_customer_can_prepare_paypal_order_from_checkout_payment_step(): void
    {
        Http::fake([
            '*/orders/22/payments/paypal/orders' => Http::response(['data' => $this->paypalOrder()]),
        ]);

        $this->withSession(['customer_api_token' => 'checkout-token']);

        Livewire::test(CheckoutReview::class, [
            'locale' => 'fr',
            'user' => $this->user(),
            'addresses' => [$this->address()],
            'countries' => $this->countries(),
        ])
            ->set('confirmedOrder', $this->order())
            ->call('selectPaymentProvider', 'paypal')
            ->assertSet('paymentProvider', 'paypal')
            ->assertSet('paypalOrder.external_id', 'PAYPAL-ORDER-1')
            ->assertDispatched('paypal-payment-ready');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/orders/22/payments/paypal/orders')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer checkout-token'));
    }

    public function test_successful_paypal_payment_clears_cart_and_confirms_checkout(): void
    {
        Http::fake([
            '*/orders/22/payments/paypal/orders/PAYPAL-ORDER-1/capture' => Http::response(['data' => [
                ...$this->paypalOrder(),
                'status' => 'COMPLETED',
            ]]),
        ]);

        $this->withSession(['customer_api_token' => 'checkout-token']);

        Livewire::test(CheckoutReview::class, [
            'locale' => 'fr',
            'user' => $this->user(),
            'addresses' => [$this->address()],
            'countries' => $this->countries(),
        ])
            ->set('cartToken', 'cart-token-123')
            ->set('cart', $this->cart([$this->cartItem()]))
            ->set('confirmedOrder', $this->order())
            ->set('paypalOrder', $this->paypalOrder())
            ->call('capturePaypalPayment', 'PAYPAL-ORDER-1')
            ->assertSet('orderConfirmed', true)
            ->assertSet('cartToken', null)
            ->assertSet('cart.items', [])
            ->assertDispatched('cart-token-cleared')
            ->assertDispatched('cart:cleared')
            ->assertDispatched('checkout-confirmed');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/orders/22/payments/paypal/orders/PAYPAL-ORDER-1/capture')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer checkout-token'));
    }

    private function fakeAuthenticatedCheckout(): void
    {
        Http::fake([
            '*/me/addresses' => Http::response(['data' => [$this->address()]]),
            '*/me' => Http::response(['data' => $this->user()]),
            '*/supported-countries*' => Http::response(['data' => $this->countries()]),
        ]);
    }

    private function user(): array
    {
        return [
            'id' => 7,
            'name' => 'Jean Martin',
            'first_name' => 'Jean',
            'last_name' => 'Martin',
            'email' => 'jean@example.test',
            'phone' => '+33600000000',
            'preferred_locale' => 'fr',
            'country_code' => 'FR',
            'timezone' => 'Europe/Paris',
            'status' => 'active',
            'roles' => ['customer'],
        ];
    }

    private function address(): array
    {
        return [
            'id' => 11,
            'type' => 'shipping',
            'label' => 'Maison',
            'recipient_name' => 'Jean Martin',
            'company' => null,
            'street_line_1' => '12 Rue du Test',
            'street_line_2' => null,
            'postal_code' => '75001',
            'city' => 'Paris',
            'region' => 'Ile-de-France',
            'country_code' => 'FR',
            'phone' => '+33600000000',
            'is_default' => true,
        ];
    }

    private function countries(): array
    {
        return [
            ['code' => 'FR', 'name' => 'France', 'currency' => 'EUR', 'default_locale' => 'fr', 'timezone' => 'Europe/Paris', 'is_eu' => true, 'is_active' => true],
            ['code' => 'BE', 'name' => 'Belgique', 'currency' => 'EUR', 'default_locale' => 'fr', 'timezone' => 'Europe/Brussels', 'is_eu' => true, 'is_active' => true],
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
            'items' => $items,
        ];
    }

    private function quote(): array
    {
        return [
            'cart_token' => 'cart-token-123',
            'currency' => 'EUR',
            'subtotal_cents' => 890,
            'formatted_subtotal' => '8,90 EUR',
            'shipping_cents' => 590,
            'formatted_shipping' => '5,90 EUR',
            'tax_cents' => 106,
            'formatted_tax' => '1,06 EUR',
            'discount_cents' => 0,
            'formatted_discount' => '0,00 EUR',
            'total_cents' => 1586,
            'formatted_total' => '15,86 EUR',
        ];
    }

    private function order(array $overrides = []): array
    {
        return array_merge([
            'id' => 22,
            'order_number' => 'DF-20260616-ABC123',
            'status' => 'pending_payment',
            'payment_status' => 'unpaid',
            'fulfillment_status' => 'unfulfilled',
            'currency' => 'EUR',
            'subtotal_cents' => 890,
            'shipping_cents' => 590,
            'tax_cents' => 106,
            'total_cents' => 1586,
            'formatted_total' => '15,86 EUR',
            'items' => [$this->cartItem()],
        ], $overrides);
    }

    private function stripePaymentIntent(): array
    {
        return [
            'provider' => 'stripe',
            'payment_id' => 44,
            'external_id' => 'pi_test_123',
            'status' => 'requires_payment_method',
            'amount_cents' => 1586,
            'currency' => 'EUR',
            'client_secret' => 'pi_test_123_secret_test',
            'publishable_key' => 'pk_test_123',
        ];
    }

    private function paypalOrder(): array
    {
        return [
            'provider' => 'paypal',
            'payment_id' => 45,
            'external_id' => 'PAYPAL-ORDER-1',
            'status' => 'CREATED',
            'amount_cents' => 1586,
            'currency' => 'EUR',
            'approval_url' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-1',
            'client_id' => 'paypal-client-id',
        ];
    }

    private function cartItem(): array
    {
        return [
            'id' => 55,
            'quantity' => 1,
            'line_total_cents' => 890,
            'formatted_line_total' => 'EUR 8.90',
            'product' => [
                'id' => 10,
                'name' => 'Miel de montagne',
                'origin' => 'Origine France',
                'image' => null,
            ],
            'variant' => null,
        ];
    }

    private function shippingMethods(): array
    {
        return [[
            'method_id' => 1, 'method_code' => 'mondial_relay_point_relais', 'carrier_code' => 'mondial_relay',
            'name' => 'Point Relais®', 'delivery_type' => 'pickup_point', 'price_cents' => 490, 'currency' => 'EUR',
            'requires_pickup_point' => true, 'requires_phone' => true, 'min_delivery_days' => 3, 'max_delivery_days' => 5,
        ]];
    }

    private function pickupPoint(): array
    {
        return [
            'id' => 101, 'code' => 'mr-paris-11', 'external_id' => 'mr-paris-11', 'carrier_code' => 'mondial_relay',
            'type' => 'pickup_point', 'name' => 'Relais Paris', 'address' => '10 rue de Rivoli, 75001 Paris',
            'address_line1' => '10 rue de Rivoli', 'postal_code' => '75001', 'city' => 'Paris', 'country_code' => 'FR',
            'distance_meters' => 450, 'latitude' => 48.8566, 'longitude' => 2.3522,
        ];
    }
}
