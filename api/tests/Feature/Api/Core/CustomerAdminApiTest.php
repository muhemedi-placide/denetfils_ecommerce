<?php

namespace Tests\Feature\Api\Core;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SupportedCountrySeeder::class, AccessControlSeeder::class]);
    }

    public function test_customer_index_contains_only_customers_and_supports_filters(): void
    {
        $staff = User::factory()->create(['email' => 'staff@example.test']);
        $staff->assignRole('support_agent');
        $customer = $this->customer([
            'first_name' => 'Alice',
            'last_name' => 'Client',
            'email' => 'alice@example.test',
            'country_code' => 'BE',
            'status' => 'active',
        ]);
        $this->customer(['email' => 'blocked@example.test', 'status' => 'suspended']);

        Sanctum::actingAs($staff);

        $this->getJson('/api/v1/admin/customers?q=alice&status=active&country_code=be')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $customer->id)
            ->assertJsonPath('data.0.email', 'alice@example.test')
            ->assertJsonMissing(['email' => 'staff@example.test']);

        $this->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonPath('data.0.email', 'staff@example.test')
            ->assertJsonMissing(['email' => 'alice@example.test']);
    }

    public function test_customer_detail_is_scoped_to_its_addresses_orders_payments_and_conversations(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('support_agent');
        $customer = $this->customer(['email' => 'owner@example.test']);
        $other = $this->customer(['email' => 'other@example.test']);
        $address = $customer->addresses()->create($this->addressPayload('Paris'));
        $other->addresses()->create($this->addressPayload('Bruxelles'));
        $order = $this->order($customer, 'DF-CUSTOMER-1', 4590);
        $this->order($other, 'DF-OTHER-1', 9900);
        $order->payments()->create([
            'provider' => 'stripe',
            'provider_reference' => 'pi_customer',
            'status' => 'captured',
            'amount_cents' => 4590,
            'currency' => 'EUR',
        ]);
        $conversation = $order->conversation()->create(['status' => 'open']);
        $conversation->messages()->create([
            'customer_id' => $customer->id,
            'sender_type' => 'customer',
            'body' => 'Question client',
        ]);

        Sanctum::actingAs($staff);

        $this->getJson("/api/v1/admin/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.addresses.0.id', $address->id)
            ->assertJsonPath('data.orders.0.order_number', 'DF-CUSTOMER-1')
            ->assertJsonPath('data.orders.0.payments.0.provider_reference', 'pi_customer')
            ->assertJsonPath('data.orders.0.conversation.messages.0.body', 'Question client')
            ->assertJsonPath('data.summary.orders_count', 1)
            ->assertJsonPath('data.summary.open_conversations_count', 1)
            ->assertJsonMissing(['order_number' => 'DF-OTHER-1'])
            ->assertJsonMissing(['city' => 'Bruxelles']);
    }

    public function test_customer_permissions_control_read_and_management_without_role_assignment_endpoint(): void
    {
        $catalogManager = User::factory()->create();
        $catalogManager->assignRole('catalog_manager');
        Sanctum::actingAs($catalogManager);
        $this->getJson('/api/v1/admin/customers')->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->assignRole('operations_manager');
        $customer = $this->customer();

        Sanctum::actingAs($viewer);
        $this->getJson('/api/v1/admin/customers')->assertOk();
        $this->patchJson("/api/v1/admin/customers/{$customer->id}", [
            'status' => 'suspended',
        ])->assertForbidden();
        $this->postJson("/api/v1/admin/customers/{$customer->id}/roles", [
            'roles' => ['admin'],
        ])->assertNotFound();

        $manager = User::factory()->create();
        $manager->assignRole('support_agent');
        Sanctum::actingAs($manager);
        $this->patchJson("/api/v1/admin/customers/{$customer->id}", [
            'status' => 'suspended',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'suspended');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'customers.status_updated',
            'auditable_id' => $customer->id,
        ]);
    }

    private function customer(array $attributes = []): Customer
    {
        return Customer::factory()->create([
            'role_id' => Role::findByName('customer', 'web')->id,
            ...$attributes,
        ]);
    }

    private function addressPayload(string $city): array
    {
        return [
            'type' => 'shipping',
            'label' => 'Maison',
            'recipient_name' => 'Client Test',
            'street_line_1' => '1 rue Test',
            'postal_code' => '75001',
            'city' => $city,
            'country_code' => 'FR',
            'is_default' => true,
        ];
    }

    private function order(Customer $customer, string $number, int $total): Order
    {
        return Order::create([
            'order_number' => $number,
            'customer_id' => $customer->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'fulfillment_status' => 'preparing',
            'currency' => 'EUR',
            'subtotal_cents' => $total,
            'tax_cents' => 0,
            'shipping_cents' => 0,
            'discount_cents' => 0,
            'total_cents' => $total,
            'customer_email' => $customer->email,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_locale' => 'fr',
            'customer_country_code' => $customer->country_code,
            'placed_at' => now(),
        ]);
    }
}
