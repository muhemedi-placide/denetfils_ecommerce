<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class ShopApiClient
{
    public function siteSeo(string $locale): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl())
                ->acceptJson()
                ->timeout(5)
                ->get('seo/site', ['locale' => $this->locale($locale)])
                ->throw();

            return [
                'data' => $response->json('data', []),
                'error' => null,
            ];
        } catch (ConnectionException|RequestException) {
            return [
                'data' => [],
                'error' => __('home.products.api_error'),
            ];
        }
    }

    public function categories(string $locale): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl())
                ->acceptJson()
                ->timeout(5)
                ->get('categories', ['locale' => $this->locale($locale)])
                ->throw();

            return [
                'data' => $response->json('data', []),
                'error' => null,
            ];
        } catch (ConnectionException|RequestException) {
            return [
                'data' => [],
                'error' => __('home.products.api_error'),
            ];
        }
    }

    public function products(string $locale, array $filters = []): array
    {
        try {
            $query = array_filter([
                'locale' => $this->locale($locale),
                'category' => $filters['category'] ?? null,
                'q' => $filters['q'] ?? null,
                'sort' => $filters['sort'] ?? null,
            ], fn ($value) => $value !== null && $value !== '');

            $response = Http::baseUrl($this->baseUrl())
                ->acceptJson()
                ->timeout(5)
                ->get('products', $query)
                ->throw();

            return [
                'data' => $response->json('data', []),
                'error' => null,
            ];
        } catch (ConnectionException|RequestException) {
            return [
                'data' => [],
                'error' => __('home.products.api_error'),
            ];
        }
    }

    public function product(string $slug, string $locale): ?array
    {
        try {
            $response = Http::baseUrl($this->baseUrl())
                ->acceptJson()
                ->timeout(5)
                ->get("products/{$slug}", ['locale' => $this->locale($locale)]);

            if ($response->notFound()) {
                return null;
            }

            $response->throw();

            return $response->json('data');
        } catch (ConnectionException|RequestException) {
            return null;
        }
    }

    public function createCart(string $locale): array
    {
        return $this->send('post', 'carts', [
            'locale' => $this->locale($locale),
        ]);
    }

    public function cart(string $token, string $locale): array
    {
        return $this->send('get', "carts/{$token}", [
            'locale' => $this->locale($locale),
        ]);
    }

    public function addCartItem(string $token, string $locale, array $payload): array
    {
        return $this->send('post', "carts/{$token}/items", [
            ...$payload,
            'locale' => $this->locale($locale),
        ]);
    }

    public function updateCartItem(string $token, string $locale, int|string $item, array $payload): array
    {
        return $this->send('patch', "carts/{$token}/items/{$item}", [
            ...$payload,
            'locale' => $this->locale($locale),
        ]);
    }

    public function deleteCartItem(string $token, string $locale, int|string $item): array
    {
        return $this->send('delete', "carts/{$token}/items/{$item}", [
            'locale' => $this->locale($locale),
        ]);
    }

    public function estimateCart(string $token, string $locale, string $countryCode): array
    {
        return $this->send('post', "carts/{$token}/estimate", [
            'locale' => $this->locale($locale),
            'country_code' => strtoupper($countryCode),
        ]);
    }

    public function sitemapXml(): ?string
    {
        try {
            $response = Http::baseUrl($this->baseUrl())
                ->accept('application/xml')
                ->timeout(5)
                ->get('sitemap.xml')
                ->throw();

            return $response->body();
        } catch (ConnectionException|RequestException) {
            return null;
        }
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.denetfils_api.base_url'), '/');
    }

    private function send(string $method, string $uri, array $payload = []): array
    {
        try {
            $request = Http::baseUrl($this->baseUrl())
                ->acceptJson()
                ->timeout(8);

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
                'message' => __('home.cart.api_error'),
                'errors' => [],
            ];
        }
    }

    private function locale(string $locale): string
    {
        return in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
    }
}
