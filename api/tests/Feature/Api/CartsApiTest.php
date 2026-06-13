<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Database\Seeders\EcommerceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_can_be_created(): void
    {
        $response = $this->postJson('/api/v1/carts');

        $response
            ->assertCreated()
            ->assertJsonPath('data.currency', 'EUR')
            ->assertJsonPath('data.subtotal_cents', 0)
            ->assertJsonPath('data.tax_cents', 0)
            ->assertJsonPath('data.total_cents', 0)
            ->assertJsonCount(0, 'data.items');

        $this->assertNotEmpty($response->json('data.cart_token'));
    }

    public function test_cart_item_can_be_added_updated_and_removed(): void
    {
        $this->seed(EcommerceSeeder::class);
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->postJson('/api/v1/carts')->json('data.cart_token');

        $addResponse = $this->postJson("/api/v1/carts/{$cartToken}/items?locale=fr", [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $addResponse
            ->assertCreated()
            ->assertJsonPath('data.items.0.product.name', 'Miel de montagne')
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.total_cents', $product->price_cents * 2);

        $itemId = $addResponse->json('data.items.0.id');

        $updateResponse = $this->patchJson("/api/v1/carts/{$cartToken}/items/{$itemId}", [
            'quantity' => 3,
        ]);

        $updateResponse
            ->assertOk()
            ->assertJsonPath('data.items.0.quantity', 3)
            ->assertJsonPath('data.total_cents', $product->price_cents * 3);

        $deleteResponse = $this->deleteJson("/api/v1/carts/{$cartToken}/items/{$itemId}");

        $deleteResponse
            ->assertOk()
            ->assertJsonPath('data.total_cents', 0)
            ->assertJsonCount(0, 'data.items');
    }

    public function test_cart_rejects_missing_product_and_invalid_quantity(): void
    {
        $this->seed(EcommerceSeeder::class);
        $product = Product::query()->where('slug', 'miel-de-montagne')->firstOrFail();
        $cartToken = $this->postJson('/api/v1/carts')->json('data.cart_token');

        $this->postJson("/api/v1/carts/{$cartToken}/items", [
            'product_id' => 999999,
            'quantity' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product_id');

        $this->postJson("/api/v1/carts/{$cartToken}/items", [
            'product_id' => $product->id,
            'quantity' => $product->stock_quantity + 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');
    }
}
