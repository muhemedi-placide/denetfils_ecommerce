<?php

namespace Tests\Feature\Api\Core;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\Orders\InvoiceService;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SupportedCountrySeeder::class, AccessControlSeeder::class]);
    }

    public function test_operations_manager_can_filter_and_read_bilingual_invoices(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('operations_manager');
        $customer = Customer::factory()->create([
            'name' => 'Alice Client',
            'email' => 'alice.invoice@example.test',
        ]);
        $order = $this->order($customer, 'DF-INVOICE-001', 'paid', 4990);
        $invoice = app(InvoiceService::class)->syncForOrder($order);

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/admin/invoices?locale=fr&q=alice&status=paid')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.invoice_number', 'FAC-DF-INVOICE-001')
            ->assertJsonPath('data.0.status_label', 'Payee')
            ->assertJsonPath('data.0.order.customer.email', 'alice.invoice@example.test')
            ->assertJsonPath('summary.paid_invoices', 1)
            ->assertJsonPath('summary.total_cents', 4990);

        $this->getJson("/api/v1/admin/invoices/{$invoice->id}?locale=en")
            ->assertOk()
            ->assertJsonPath('data.status_label', 'Paid')
            ->assertJsonPath('data.order_detail.order_number', 'DF-INVOICE-001');
    }

    public function test_invoice_status_tracks_order_payment_and_catalog_role_is_denied(): void
    {
        $customer = Customer::factory()->create();
        $order = $this->order($customer, 'DF-INVOICE-002', 'unpaid', 1200);
        $service = app(InvoiceService::class);

        $this->assertSame('draft', $service->syncForOrder($order)->status);

        $order->forceFill(['payment_status' => 'paid', 'status' => 'confirmed'])->save();
        $this->assertSame('paid', $service->syncForOrder($order)->status);

        $order->forceFill(['payment_status' => 'refunded', 'status' => 'refunded'])->save();
        $this->assertSame('refunded', $service->syncForOrder($order)->status);

        $catalogManager = User::factory()->create();
        $catalogManager->assignRole('catalog_manager');
        Sanctum::actingAs($catalogManager);
        $this->getJson('/api/v1/admin/invoices')->assertForbidden();
    }

    private function order(Customer $customer, string $number, string $paymentStatus, int $total): Order
    {
        $order = Order::create([
            'order_number' => $number,
            'customer_id' => $customer->id,
            'status' => $paymentStatus === 'paid' ? 'confirmed' : 'pending_payment',
            'payment_status' => $paymentStatus,
            'fulfillment_status' => 'unfulfilled',
            'currency' => 'EUR',
            'subtotal_cents' => $total,
            'tax_cents' => 0,
            'shipping_cents' => 0,
            'discount_cents' => 0,
            'total_cents' => $total,
            'customer_email' => $customer->email,
            'customer_name' => $customer->name,
            'customer_locale' => 'fr',
            'customer_country_code' => 'FR',
            'placed_at' => now(),
        ]);
        $order->items()->create([
            'product_name' => ['fr' => 'Produit test', 'en' => 'Test product'],
            'product_sku' => 'TEST-001',
            'quantity' => 1,
            'unit_price_cents' => $total,
            'line_total_cents' => $total,
            'currency' => 'EUR',
            'tax_class' => 'standard',
            'tax_rate_percent' => 20,
            'tax_cents' => 0,
        ]);

        return $order;
    }
}
