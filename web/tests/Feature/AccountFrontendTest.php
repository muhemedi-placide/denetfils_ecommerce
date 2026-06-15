<?php

namespace Tests\Feature;

use App\Livewire\Account\LoginForm;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AccountFrontendTest extends TestCase
{
    public function test_login_page_is_rendered_and_private_for_seo(): void
    {
        $this->withoutVite();

        $this->get('/fr/connexion')
            ->assertOk()
            ->assertSee('Connectez-vous')
            ->assertSee('wire:submit.prevent="login"', false)
            ->assertSee('noindex,nofollow', false);
    }

    public function test_register_page_uses_supported_countries_from_api(): void
    {
        $this->withoutVite();
        $this->fakeCountriesAndConsents();

        $this->get('/en/inscription')
            ->assertOk()
            ->assertSee('Create your customer account.')
            ->assertSee('wire:submit.prevent="register"', false)
            ->assertSee('France')
            ->assertSee('Belgium');
    }

    public function test_customer_can_login_and_token_is_saved_in_session(): void
    {
        $this->withoutVite();
        Http::fake([
            '*/auth/login' => Http::response([
                'data' => [
                    'token' => 'api-token-123',
                    'token_type' => 'Bearer',
                    'user' => $this->user(),
                ],
            ]),
        ]);

        $this->post('/en/connexion', [
            'email' => 'jean@example.test',
            'password' => 'password-secret',
        ])
            ->assertRedirect('/en/mon-compte')
            ->assertSessionHas('customer_api_token', 'api-token-123')
            ->assertSessionHas('customer_user.email', 'jean@example.test');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/auth/login')
            && $request['email'] === 'jean@example.test'
            && $request['device_name'] === 'denetfils-web');
    }

    public function test_account_page_consumes_me_and_addresses_api(): void
    {
        $this->withoutVite();
        $this->fakeAuthenticatedAccount();

        $this->withSession(['customer_api_token' => 'api-token-123'])
            ->get('/fr/mon-compte')
            ->assertOk()
            ->assertSee('Mon compte')
            ->assertSee('wire:submit.prevent="updateProfile"', false)
            ->assertSee('wire:submit.prevent="createAddress"', false)
            ->assertSee('wire:click="logout"', false)
            ->assertSee('Jean Martin')
            ->assertSee('12 Rue du Test')
            ->assertSee('France');
    }

    public function test_livewire_login_posts_to_api_and_stores_token(): void
    {
        Http::fake([
            '*/auth/login' => Http::response([
                'data' => [
                    'token' => 'api-token-123',
                    'token_type' => 'Bearer',
                    'user' => $this->user(),
                ],
            ]),
        ]);

        Livewire::test(LoginForm::class, ['locale' => 'en'])
            ->set('email', 'jean@example.test')
            ->set('password', 'password-secret')
            ->call('login')
            ->assertRedirect(route('account.show', ['locale' => 'en']));

        $this->assertSame('api-token-123', session('customer_api_token'));
        $this->assertSame('jean@example.test', session('customer_user.email'));

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/auth/login')
            && $request['email'] === 'jean@example.test'
            && $request['device_name'] === 'denetfils-web');
    }

    public function test_profile_update_is_sent_to_authenticated_api(): void
    {
        $this->withoutVite();
        Http::fake([
            '*/me' => Http::response(['data' => $this->user(['first_name' => 'Jeanne'])]),
        ]);

        $this->withSession(['customer_api_token' => 'api-token-123'])
            ->patch('/fr/mon-compte', [
                'first_name' => 'Jeanne',
                'last_name' => 'Martin',
                'phone' => '+33600000000',
                'preferred_locale' => 'fr',
                'country_code' => 'FR',
                'timezone' => 'Europe/Paris',
            ])
            ->assertRedirect()
            ->assertSessionHas('customer_user.first_name', 'Jeanne');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/me')
            && $request->method() === 'PATCH'
            && $request->hasHeader('Authorization', 'Bearer api-token-123')
            && $request['first_name'] === 'Jeanne');
    }

    public function test_address_creation_is_sent_to_authenticated_api(): void
    {
        $this->withoutVite();
        Http::fake([
            '*/me/addresses' => Http::response([
                'data' => $this->address(),
            ], 201),
        ]);

        $this->withSession(['customer_api_token' => 'api-token-123'])
            ->post('/fr/mon-compte/adresses', $this->addressPayload())
            ->assertRedirect();

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/me/addresses')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer api-token-123')
            && $request['country_code'] === 'FR');
    }

    public function test_registration_posts_gdpr_consents_and_stores_token(): void
    {
        $this->withoutVite();
        Http::fake([
            '*/auth/register' => Http::response([
                'data' => [
                    'token' => 'new-token-123',
                    'token_type' => 'Bearer',
                    'user' => $this->user(['email' => 'new@example.test']),
                ],
            ], 201),
        ]);

        $this->post('/fr/inscription', [
            'first_name' => 'Jean',
            'last_name' => 'Martin',
            'email' => 'new@example.test',
            'phone' => '+33600000000',
            'country_code' => 'FR',
            'password' => 'password-secret',
            'password_confirmation' => 'password-secret',
            'privacy_policy_consent' => '1',
            'terms_consent' => '1',
            'marketing_consent' => '1',
        ])
            ->assertRedirect('/fr/mon-compte')
            ->assertSessionHas('customer_api_token', 'new-token-123');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/auth/register')
            && $request['privacy_policy_consent'] === '1'
            && $request['terms_consent'] === '1'
            && $request['marketing_consent'] === true
            && $request['preferred_locale'] === 'fr'
            && $request['timezone'] === 'Europe/Paris');
    }

    private function fakeAuthenticatedAccount(): void
    {
        Http::fake([
            '*/me' => Http::response(['data' => $this->user()]),
            '*/me/addresses' => Http::response(['data' => [$this->address()]]),
            '*/supported-countries*' => Http::response(['data' => $this->countries()]),
        ]);
    }

    private function fakeCountriesAndConsents(): void
    {
        Http::fake([
            '*/supported-countries*' => Http::response(['data' => $this->countries()]),
            '*/privacy/consents/current' => Http::response([
                'data' => [
                    ['type' => 'privacy_policy', 'version' => '2026-06-13', 'required' => true],
                    ['type' => 'terms', 'version' => '2026-06-13', 'required' => true],
                    ['type' => 'marketing_email', 'version' => '2026-06-13', 'required' => false],
                ],
            ]),
        ]);
    }

    private function user(array $overrides = []): array
    {
        return array_merge([
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
            'permissions' => [],
        ], $overrides);
    }

    private function address(array $overrides = []): array
    {
        return array_merge([
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
        ], $overrides);
    }

    private function addressPayload(): array
    {
        return [
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
            'is_default' => '1',
        ];
    }

    private function countries(): array
    {
        return [
            ['code' => 'FR', 'name' => 'France', 'currency' => 'EUR', 'default_locale' => 'fr', 'timezone' => 'Europe/Paris', 'is_eu' => true, 'is_active' => true],
            ['code' => 'BE', 'name' => 'Belgium', 'currency' => 'EUR', 'default_locale' => 'fr', 'timezone' => 'Europe/Brussels', 'is_eu' => true, 'is_active' => true],
        ];
    }
}
