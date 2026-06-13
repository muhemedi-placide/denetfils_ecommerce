<?php

namespace Tests\Feature\Api\Core;

use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileAddressApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SupportedCountrySeeder::class, AccessControlSeeder::class]);
    }

    public function test_user_can_update_profile_with_european_defaults(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/me', [
            'preferred_locale' => 'en',
            'country_code' => 'DE',
            'timezone' => 'Europe/Berlin',
        ])
            ->assertOk()
            ->assertJsonPath('data.preferred_locale', 'en')
            ->assertJsonPath('data.country_code', 'DE')
            ->assertJsonPath('data.timezone', 'Europe/Berlin');
    }

    public function test_address_requires_supported_country_and_keeps_single_default_per_type(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/addresses', $this->addressPayload(['country_code' => 'XX']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('country_code');

        $first = $this->postJson('/api/v1/me/addresses', $this->addressPayload([
            'label' => 'Maison',
            'is_default' => true,
        ]))
            ->assertCreated()
            ->json('data.id');

        $second = $this->postJson('/api/v1/me/addresses', $this->addressPayload([
            'label' => 'Bureau',
            'is_default' => true,
        ]))
            ->assertCreated()
            ->json('data.id');

        $this->assertFalse(UserAddress::find($first)->is_default);
        $this->assertTrue(UserAddress::find($second)->is_default);
    }

    public function test_user_cannot_update_another_users_address(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $address = $owner->addresses()->create($this->addressPayload());

        Sanctum::actingAs($other);

        $this->patchJson("/api/v1/me/addresses/{$address->id}", [
            'city' => 'Berlin',
        ])->assertNotFound();
    }

    private function addressPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'billing',
            'label' => 'Home',
            'recipient_name' => 'Jean Martin',
            'street_line_1' => '10 Rue de Rivoli',
            'postal_code' => '75001',
            'city' => 'Paris',
            'country_code' => 'FR',
            'is_default' => false,
        ], $overrides);
    }
}
