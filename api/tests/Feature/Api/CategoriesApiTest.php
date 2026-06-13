<?php

namespace Tests\Feature\Api;

use Database\Seeders\EcommerceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_list_returns_localized_categories_with_counts(): void
    {
        $this->seed(EcommerceSeeder::class);

        $response = $this->getJson('/api/v1/categories?locale=en');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'epicerie-fine')
            ->assertJsonPath('data.0.name', 'Fine groceries');

        $this->assertGreaterThan(0, $response->json('data.0.products_count'));
    }
}
