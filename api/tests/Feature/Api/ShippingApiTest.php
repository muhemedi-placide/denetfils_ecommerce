<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\Shipping\MondialRelay\MondialRelayProvider;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShippingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        ShippingCarrier::query()->where('code', 'mondial_relay')->update(['is_enabled' => true, 'status' => 'active']);
    }

    public function test_customer_receives_weight_and_country_eligible_shipping_methods(): void
    {
        [$user, $address, $cart] = $this->checkoutContext();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/shipping/methods?'.http_build_query(['cart_token' => $cart->cart_token, 'shipping_address_id' => $address->id, 'locale' => 'fr']))
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.carrier_code', 'mondial_relay')
            ->assertJsonPath('data.0.requires_pickup_point', true);
    }

    public function test_pickup_selection_is_required_and_persisted_on_cart(): void
    {
        [$user, $address, $cart] = $this->checkoutContext();
        Sanctum::actingAs($user);
        $method = ShippingMethod::query()->where('code', 'mondial_relay_point_relais')->firstOrFail();

        $this->postJson('/api/v1/shipping/selection', ['cart_token' => $cart->cart_token, 'shipping_method_id' => $method->id, 'shipping_address_id' => $address->id])
            ->assertUnprocessable()->assertJsonValidationErrors('pickup_point_id');

        $point = PickupPoint::query()->create(['carrier_code' => 'mondial_relay', 'external_id' => '000001', 'type' => 'pickup_point', 'country' => 'FR', 'name' => 'Relais Test', 'address_line1' => '1 rue Test', 'postal_code' => '75001', 'city' => 'Paris']);
        $this->postJson('/api/v1/shipping/selection', ['cart_token' => $cart->cart_token, 'shipping_method_id' => $method->id, 'shipping_address_id' => $address->id, 'pickup_point_id' => $point->id])
            ->assertOk()->assertJsonPath('data.pickup_point.external_id', '000001');

        $this->assertDatabaseHas('cart_shipping_selections', ['cart_id' => $cart->id, 'shipping_method_id' => $method->id, 'pickup_point_id' => $point->id, 'shipping_price_cents' => 490]);
    }

    public function test_mondial_relay_provider_maps_pickup_points_and_never_sends_private_key(): void
    {
        Http::fake(['https://api.mondialrelay.test' => Http::response('<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><WSI4_PointRelais_RechercheResponse xmlns="http://www.mondialrelay.fr/webservice/"><WSI4_PointRelais_RechercheResult><STAT>0</STAT><PointsRelais><PointRelais_Details><Num>012345</Num><LgAdr1>Relais Paris</LgAdr1><LgAdr3>10 rue de Rivoli</LgAdr3><CP>75001</CP><Ville>Paris</Ville><Pays>FR</Pays><Latitude>48,8566</Latitude><Longitude>2,3522</Longitude><Distance>450</Distance></PointRelais_Details></PointsRelais></WSI4_PointRelais_RechercheResult></WSI4_PointRelais_RechercheResponse></soap:Body></soap:Envelope>', 200)]);
        $carrier = ShippingCarrier::query()->where('code', 'mondial_relay')->firstOrFail();
        $carrier->credentials = ['enseigne' => 'BDTEST', 'private_key' => 'TOP-SECRET', 'api_endpoint' => 'https://api.mondialrelay.test'];
        $carrier->save();

        $points = app(MondialRelayProvider::class)->searchPickupPoints($carrier, ['country' => 'FR', 'postal_code' => '75001', 'weight_grams' => 1000]);
        $this->assertCount(1, $points);
        $this->assertSame('012345', $points[0]->externalId);
        $this->assertSame(48.8566, $points[0]->latitude);
        Http::assertSent(fn ($request) => str_contains($request->body(), '<Security>')
            && $request->hasHeader('SOAPAction', 'http://www.mondialrelay.fr/webservice/WSI4_PointRelais_Recherche')
            && ! str_contains($request->body(), 'TOP-SECRET'));
    }

    public function test_pickup_search_uses_delivery_address_automatically_and_returns_nearest_first(): void
    {
        Http::fake(['https://api.mondialrelay.test' => Http::response('<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><WSI4_PointRelais_RechercheResponse xmlns="http://www.mondialrelay.fr/webservice/"><WSI4_PointRelais_RechercheResult><STAT>0</STAT><PointsRelais><PointRelais_Details><Num>FAR001</Num><LgAdr1>Relais éloigné</LgAdr1><LgAdr3>20 rue Test</LgAdr3><CP>75001</CP><Ville>Paris</Ville><Pays>FR</Pays><Latitude>48,8600</Latitude><Longitude>2,3600</Longitude><Distance>1200</Distance></PointRelais_Details><PointRelais_Details><Num>NEAR01</Num><LgAdr1>Relais proche</LgAdr1><LgAdr3>2 rue Test</LgAdr3><CP>75001</CP><Ville>Paris</Ville><Pays>FR</Pays><Latitude>48,8566</Latitude><Longitude>2,3522</Longitude><Distance>300</Distance></PointRelais_Details></PointsRelais></WSI4_PointRelais_RechercheResult></WSI4_PointRelais_RechercheResponse></soap:Body></soap:Envelope>', 200)]);
        $carrier = ShippingCarrier::query()->where('code', 'mondial_relay')->firstOrFail();
        $carrier->credentials = ['enseigne' => 'BDTEST', 'private_key' => 'TOP-SECRET', 'api_endpoint' => 'https://api.mondialrelay.test'];
        $carrier->save();
        [$user, $address, $cart] = $this->checkoutContext();
        Sanctum::actingAs($user);
        $method = ShippingMethod::query()->where('code', 'mondial_relay_point_relais')->firstOrFail();

        $this->postJson('/api/v1/shipping/pickup-points/search', [
            'cart_token' => $cart->cart_token,
            'shipping_method_id' => $method->id,
            'shipping_address_id' => $address->id,
        ])->assertOk()
            ->assertJsonPath('data.search.mode', 'automatic_address')
            ->assertJsonPath('data.search.postal_code', '75001')
            ->assertJsonPath('data.search.city', 'Paris')
            ->assertJsonPath('data.points.0.external_id', 'NEAR01')
            ->assertJsonPath('data.points.0.distance_meters', 300)
            ->assertJsonPath('data.points.1.external_id', 'FAR001');

        Http::assertSent(fn ($request) => str_contains($request->body(), '<CP>75001</CP>')
            && str_contains($request->body(), '<Ville>Paris</Ville>'));
    }

    public function test_public_tracking_uses_mondial_relay_webservice(): void
    {
        Http::fake(['https://api.mondialrelay.test' => Http::response('<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><WSI2_TracingColisDetailleResponse xmlns="http://www.mondialrelay.fr/webservice/"><WSI2_TracingColisDetailleResult><STAT>0</STAT><Libelle01>Colis pris en charge</Libelle01><Date01>23/06/2026</Date01><Heure01>10:15</Heure01><Emplacement01>Paris</Emplacement01></WSI2_TracingColisDetailleResult></WSI2_TracingColisDetailleResponse></soap:Body></soap:Envelope>', 200)]);
        $carrier = ShippingCarrier::query()->where('code', 'mondial_relay')->firstOrFail();
        $carrier->credentials = ['enseigne' => 'BDTEST', 'private_key' => 'TOP-SECRET', 'api_endpoint' => 'https://api.mondialrelay.test'];
        $carrier->save();

        $this->postJson('/api/v1/shipping/tracking', [
            'tracking_number' => '123456789012',
            'locale' => 'fr',
        ])->assertOk()
            ->assertJsonPath('data.source', 'mondial_relay')
            ->assertJsonPath('data.tracking_number', '123456789012')
            ->assertJsonPath('data.events.0.label', 'Colis pris en charge')
            ->assertJsonPath('data.events.0.location', 'Paris');

        Http::assertSent(fn ($request) => $request->hasHeader('SOAPAction', 'http://www.mondialrelay.fr/webservice/WSI2_TracingColisDetaille')
            && str_contains($request->body(), '<Expedition>123456789012</Expedition>')
            && ! str_contains($request->body(), 'TOP-SECRET'));
    }

    public function test_admin_can_add_chronopost_home_method_that_is_returned_to_checkout(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('finance_manager');
        Sanctum::actingAs($manager);

        $this->postJson('/api/v1/admin/shipping-carriers', [
            'code' => 'chronopost_fr', 'provider' => 'chronopost',
            'display_name' => ['fr' => 'Chronopost', 'en' => 'Chronopost'],
            'environment' => 'sandbox', 'status' => 'active', 'is_enabled' => true,
            'delivery_modes' => ['HOME'], 'countries' => ['FR'], 'max_weight_grams' => 30000,
            'supports_relay_points' => false, 'supports_home_delivery' => true, 'credentials' => [],
            'method' => [
                'code' => 'chronopost_home', 'name' => ['fr' => 'Chronopost domicile', 'en' => 'Chronopost home'],
                'delivery_type' => 'home', 'service_code' => 'HOME', 'price_cents' => 1290,
                'currency' => 'EUR', 'min_delivery_days' => 1, 'max_delivery_days' => 2,
            ],
        ])->assertCreated()->assertJsonPath('data.provider', 'chronopost');

        [$customer, $address, $cart] = $this->checkoutContext();
        Sanctum::actingAs($customer);
        $this->getJson('/api/v1/shipping/methods?'.http_build_query(['cart_token' => $cart->cart_token, 'shipping_address_id' => $address->id, 'locale' => 'fr']))
            ->assertOk()->assertJsonFragment(['method_code' => 'chronopost_home', 'name' => 'Chronopost domicile', 'delivery_type' => 'home']);
    }

    private function checkoutContext(): array
    {
        $user = Customer::factory()->create(['country_code' => 'FR']);
        $address = $user->addresses()->create(['type' => 'shipping', 'label' => 'Maison', 'recipient_name' => $user->name, 'street_line_1' => '12 rue Test', 'postal_code' => '75001', 'city' => 'Paris', 'country_code' => 'FR', 'phone' => '+33600000000', 'is_default' => true]);
        $product = Product::query()->whereNotNull('weight_grams')->firstOrFail();
        $cart = Cart::query()->create(['cart_token' => str_repeat('a', 64), 'currency' => 'EUR', 'subtotal_cents' => $product->price_cents, 'total_cents' => $product->price_cents]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price_cents' => $product->price_cents, 'line_total_cents' => $product->price_cents]);
        return [$user, $address, $cart];
    }
}
