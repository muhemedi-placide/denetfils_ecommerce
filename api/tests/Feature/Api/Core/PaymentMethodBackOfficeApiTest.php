<?php

namespace Tests\Feature\Api\Core;

use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentMethodBackOfficeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AccessControlSeeder::class);
    }

    public function test_finance_manager_can_read_payment_provider_schemas(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('finance_manager');

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/admin/payment-methods/schemas')
            ->assertOk()
            ->assertJsonPath('data.stripe.name', 'Stripe')
            ->assertJsonPath('data.paypal.name', 'PayPal')
            ->assertJsonPath('data.prestashop.capabilities.0', 'external_channel')
            ->assertJsonPath('data.tiktok_shop.credential_fields.0.key', 'app_key');
    }

    public function test_finance_manager_can_create_stripe_method_with_masked_encrypted_credentials(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('finance_manager');

        Sanctum::actingAs($manager);

        $response = $this->postJson('/api/v1/admin/payment-methods', [
            'code' => 'stripe_cards_fr',
            'provider' => 'stripe',
            'display_name' => [
                'fr' => 'Carte bancaire',
                'en' => 'Card',
            ],
            'environment' => 'sandbox',
            'countries' => ['fr', 'be'],
            'currencies' => ['eur'],
            'credentials' => [
                'publishable_key' => 'pk_test_public',
                'restricted_key' => 'rk_test_private_secret',
                'webhook_signing_secret' => 'whsec_test_secret',
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.code', 'stripe_cards_fr')
            ->assertJsonPath('data.countries.0', 'FR')
            ->assertJsonPath('data.currencies.0', 'EUR')
            ->assertJsonPath('data.credentials.masked.publishable_key', 'pk_test_public')
            ->assertJsonMissing(['rk_test_private_secret'])
            ->assertJsonMissing(['whsec_test_secret']);

        $method = PaymentMethod::firstOrFail();
        $this->assertSame('rk_test_private_secret', $method->credentials['restricted_key']);

        $rawCredentials = DB::table('payment_methods')->value('credentials');
        $this->assertStringNotContainsString('rk_test_private_secret', $rawCredentials);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payments.methods.created',
            'auditable_id' => $method->id,
        ]);
    }

    public function test_payment_method_validation_rejects_missing_provider_credentials(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('finance_manager');

        Sanctum::actingAs($manager);

        $this->postJson('/api/v1/admin/payment-methods', [
            'code' => 'paypal_missing_secret',
            'provider' => 'paypal',
            'display_name' => [
                'fr' => 'PayPal',
            ],
            'environment' => 'sandbox',
            'credentials' => [
                'client_id' => 'paypal-client-id',
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['credentials.client_secret']);
    }

    public function test_payment_method_can_be_activated_and_configuration_tested_without_external_call(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('finance_manager');
        $method = PaymentMethod::create([
            'code' => 'bank_transfer_fr',
            'provider' => 'bank_transfer',
            'display_name' => ['fr' => 'Virement bancaire'],
            'environment' => 'manual',
            'credentials' => [
                'account_holder' => 'DEN et FILS',
                'iban' => 'FR7612345678901234567890185',
                'bic' => 'AGRIFRPP',
            ],
        ]);

        Sanctum::actingAs($manager);

        $this->postJson("/api/v1/admin/payment-methods/{$method->id}/activate")
            ->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.is_enabled', true);

        $this->postJson("/api/v1/admin/payment-methods/{$method->id}/test-connection")
            ->assertOk()
            ->assertJsonPath('data.status', 'ready')
            ->assertJsonPath('data.external_call_executed', false);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payments.methods.tested',
            'auditable_id' => $method->id,
        ]);
    }

    public function test_customer_cannot_manage_payment_methods(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/admin/payment-methods')->assertForbidden();
    }
}
