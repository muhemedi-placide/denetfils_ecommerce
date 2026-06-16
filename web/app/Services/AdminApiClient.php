<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AdminApiClient
{
    public function me(string $token): array
    {
        return $this->send('get', 'auth/me', [], $token);
    }

    public function dashboard(string $token, string $locale, array $filters = []): array
    {
        return $this->send('get', 'admin/dashboard', [
            'locale' => $this->locale($locale),
            'threshold' => $filters['threshold'] ?? 5,
        ], $token);
    }

    public function products(string $token, array $filters = []): array
    {
        return $this->send('get', 'admin/products', $this->clean([
            'q' => $filters['q'] ?? null,
            'category_id' => $filters['category_id'] ?? null,
            'publication_status' => $filters['publication_status'] ?? null,
            'stock_status' => $filters['stock_status'] ?? null,
            'threshold' => $filters['threshold'] ?? null,
            'per_page' => $filters['per_page'] ?? 20,
        ]), $token);
    }

    public function categories(string $token, array $filters = []): array
    {
        return $this->send('get', 'admin/categories', $this->clean([
            'q' => $filters['q'] ?? null,
            'is_active' => $filters['is_active'] ?? null,
            'per_page' => $filters['per_page'] ?? 20,
        ]), $token);
    }

    public function inventory(string $token, array $filters = []): array
    {
        return $this->send('get', 'admin/inventory', $this->clean([
            'q' => $filters['q'] ?? null,
            'category_id' => $filters['category_id'] ?? null,
            'status' => $filters['status'] ?? null,
            'sort' => $filters['sort'] ?? null,
            'threshold' => $filters['threshold'] ?? 5,
            'per_page' => $filters['per_page'] ?? 25,
        ]), $token);
    }

    public function users(string $token, array $filters = []): array
    {
        return $this->send('get', 'admin/users', $this->clean([
            'q' => $filters['q'] ?? null,
            'status' => $filters['status'] ?? null,
            'role' => $filters['role'] ?? null,
            'country_code' => $filters['country_code'] ?? null,
            'per_page' => $filters['per_page'] ?? 25,
        ]), $token);
    }

    public function roles(string $token): array
    {
        return $this->send('get', 'admin/roles', [], $token);
    }

    public function permissions(string $token): array
    {
        return $this->send('get', 'admin/permissions', [], $token);
    }

    public function auditLogs(string $token, array $filters = []): array
    {
        return $this->send('get', 'admin/audit-logs', $this->clean([
            'action' => $filters['action'] ?? null,
            'actor_id' => $filters['actor_id'] ?? null,
            'auditable_type' => $filters['auditable_type'] ?? null,
            'per_page' => $filters['per_page'] ?? 50,
        ]), $token);
    }

    private function send(string $method, string $uri, array $payload = [], ?string $token = null): array
    {
        try {
            $request = Http::baseUrl($this->baseUrl())
                ->acceptJson()
                ->timeout(10);

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
                'meta' => $response->json('meta', []),
                'links' => $response->json('links', []),
                'summary' => $response->json('summary', []),
                'message' => $response->json('message'),
                'errors' => $response->json('errors', []),
            ];
        } catch (ConnectionException) {
            return [
                'ok' => false,
                'status' => 0,
                'data' => [],
                'meta' => [],
                'links' => [],
                'summary' => [],
                'message' => 'Impossible de joindre l API admin pour le moment.',
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

    private function clean(array $payload): array
    {
        return array_filter($payload, fn (mixed $value) => $value !== null && $value !== '');
    }
}
