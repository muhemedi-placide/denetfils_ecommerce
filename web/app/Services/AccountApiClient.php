<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AccountApiClient
{
    public function register(array $payload): array
    {
        return $this->send('post', 'auth/register', $payload);
    }

    public function login(array $payload): array
    {
        return $this->send('post', 'auth/login', $payload);
    }

    public function logout(string $token): array
    {
        return $this->send('post', 'auth/logout', [], $token);
    }

    public function me(string $token): array
    {
        return $this->send('get', 'me', [], $token);
    }

    public function updateMe(string $token, array $payload): array
    {
        return $this->send('patch', 'me', $payload, $token);
    }

    public function addresses(string $token): array
    {
        return $this->send('get', 'me/addresses', [], $token);
    }

    public function createAddress(string $token, array $payload): array
    {
        return $this->send('post', 'me/addresses', $payload, $token);
    }

    public function updateAddress(string $token, int|string $address, array $payload): array
    {
        return $this->send('patch', "me/addresses/{$address}", $payload, $token);
    }

    public function deleteAddress(string $token, int|string $address): array
    {
        return $this->send('delete', "me/addresses/{$address}", [], $token);
    }

    public function checkoutQuote(string $token, array $payload): array
    {
        return $this->send('post', 'checkout/quote', $payload, $token);
    }

    public function createOrder(string $token, array $payload): array
    {
        return $this->send('post', 'orders', $payload, $token);
    }

    public function orders(string $token, string $locale = 'fr', int $perPage = 5): array
    {
        return $this->send('get', 'me/orders', [
            'locale' => $this->locale($locale),
            'per_page' => max(5, min(15, $perPage)),
        ], $token);
    }

    public function supportedCountries(string $locale): array
    {
        $result = $this->send('get', 'supported-countries', [
            'locale' => $this->locale($locale),
        ]);

        if ($result['ok']) {
            return $result;
        }

        return [
            ...$result,
            'data' => $this->fallbackCountries($locale),
        ];
    }

    public function currentConsents(): array
    {
        return $this->send('get', 'privacy/consents/current');
    }

    private function send(string $method, string $uri, array $payload = [], ?string $token = null): array
    {
        try {
            $request = Http::baseUrl($this->baseUrl())
                ->acceptJson()
                ->timeout(8);

            if ($token) {
                $request = $request->withToken($token);
            }

            /** @var Response $response */
            $response = in_array($method, ['get', 'delete'], true)
                ? $request->{$method}($uri, $payload)
                : $request->{$method}($uri, $payload);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json('data', []),
                'message' => $response->json('message'),
                'errors' => $response->json('errors', []),
            ];
        } catch (ConnectionException) {
            return [
                'ok' => false,
                'status' => 0,
                'data' => [],
                'message' => __('home.account.api_error'),
                'errors' => [],
            ];
        }
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.denetfils_api.base_url'), '/');
    }

    private function locale(string $locale): string
    {
        return in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
    }

    private function fallbackCountries(string $locale): array
    {
        $names = [
            'fr' => [
                'FR' => 'France',
                'BE' => 'Belgique',
                'DE' => 'Allemagne',
                'NL' => 'Pays-Bas',
                'LU' => 'Luxembourg',
                'ES' => 'Espagne',
                'IT' => 'Italie',
                'PT' => 'Portugal',
            ],
            'en' => [
                'FR' => 'France',
                'BE' => 'Belgium',
                'DE' => 'Germany',
                'NL' => 'Netherlands',
                'LU' => 'Luxembourg',
                'ES' => 'Spain',
                'IT' => 'Italy',
                'PT' => 'Portugal',
            ],
        ];

        return collect($names[$this->locale($locale)])
            ->map(fn (string $name, string $code) => [
                'code' => $code,
                'name' => $name,
                'currency' => 'EUR',
                'default_locale' => $code === 'FR' ? 'fr' : 'en',
                'timezone' => 'Europe/Paris',
                'is_eu' => true,
                'is_active' => true,
            ])
            ->values()
            ->all();
    }
}
