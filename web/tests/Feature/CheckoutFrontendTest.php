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
            ->assertSee('Chronopost domicile')
            ->assertSee('Mondial Relay')
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
            ->assertSet('quote.total_cents', 1586)
            ->assertSet('quote.shipping_cents', 590)
            ->assertSet('quote.tax_cents', 106);

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/checkout/quote')
            && $request->hasHeader('Authorization', 'Bearer checkout-token')
            && $request['cart_token'] === 'cart-token-123'
            && (int) $request['shipping_address_id'] === 11
            && $request['delivery_method'] === 'relay'
            && $request['carrier'] === 'mondial_relay_pickup');
    }

    public function test_checkout_confirmation_creates_order_and_clears_guest_cart_token(): void
    {
        Http::fake([
            '*/orders' => Http::response(['data' => $this->order()], 201),
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
            ->call('confirm')
            ->assertSet('orderConfirmed', true)
            ->assertSet('confirmedOrder.order_number', 'DF-20260616-ABC123')
            ->assertSet('cartToken', null)
            ->assertSet('cart.items', [])
            ->assertDispatched('cart-token-cleared')
            ->assertDispatched('cart:cleared')
            ->assertDispatched('checkout-confirmed');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/orders')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer checkout-token')
            && $request['cart_token'] === 'cart-token-123'
            && (int) $request['shipping_address_id'] === 11
            && $request['delivery_method'] === 'relay'
            && $request['carrier'] === 'mondial_relay_pickup'
            && $request['metadata']['pickup_point']['code'] === 'mr-paris-11');
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

    private function order(): array
    {
        return [
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
}
