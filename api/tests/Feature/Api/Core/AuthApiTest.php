<?php

namespace Tests\Feature\Api\Core;

use App\Models\PrivacyConsent;
use App\Models\Customer;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SupportedCountrySeeder::class, AccessControlSeeder::class]);
    }

    public function test_customer_can_register_and_receives_account_and_consents(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Jean',
            'last_name' => 'Martin',
            'email' => 'jean.martin@example.com',
            'password' => 'password-secure',
            'password_confirmation' => 'password-secure',
            'preferred_locale' => 'fr',
            'country_code' => 'FR',
            'privacy_policy_consent' => true,
            'terms_consent' => true,
            'marketing_consent' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', 'jean.martin@example.com')
            ->assertJsonPath('data.user.role', 'customer');

        $user = Customer::where('email', 'jean.martin@example.com')->firstOrFail();

        $this->assertTrue($user->customerProfile->accepts_marketing);
        $this->assertSame('customer', $user->role->name);
        $this->assertSame(3, PrivacyConsent::where('customer_id', $user->id)->count());
    }

    public function test_login_returns_sanctum_token_and_logout_revokes_it(): void
    {
        $user = Customer::factory()->create(['email' => 'client@example.com']);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'client@example.com',
            'password' => 'password',
            'device_name' => 'feature-test',
        ]);

        $login
            ->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer');

        $token = $login->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_suspended_user_cannot_login(): void
    {
        Customer::factory()->create([
            'email' => 'blocked@example.com',
            'status' => 'suspended',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'blocked@example.com',
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_authenticated_user_can_read_auth_me(): void
    {
        $user = Customer::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_customer_and_system_authentication_are_strictly_separated(): void
    {
        $password = 'SecurePass123!';
        $customer = Customer::factory()->create(['password' => $password]);
        $staff = User::factory()->create(['password' => $password]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $staff->email,
            'password' => $password,
        ])->assertUnprocessable();

        $this->postJson('/api/v1/admin/auth/login', [
            'email' => $customer->email,
            'password' => $password,
        ])->assertUnprocessable();

        Sanctum::actingAs($customer);
        $this->getJson('/api/v1/admin/auth/me')->assertForbidden();

        $login = $this->postJson('/api/v1/admin/auth/login', [
            'email' => $staff->email,
            'password' => $password,
        ])->assertOk();

        $this->assertNotEmpty($login->json('data.token'));

        Sanctum::actingAs($staff);
        $this->getJson('/api/v1/admin/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $staff->email);
    }
}
