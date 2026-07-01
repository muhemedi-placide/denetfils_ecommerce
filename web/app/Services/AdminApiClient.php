<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AdminApiClient
{
    public function login(array $payload): array
    {
        return $this->send('post', 'admin/auth/login', $payload);
    }

    public function me(string $token): array
    {
        return $this->send('get', 'admin/auth/me', [], $token);
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
            'locale' => $filters['locale'] ?? null,
            'q' => $filters['q'] ?? null,
            'category_id' => $filters['category_id'] ?? null,
            'publication_status' => $filters['publication_status'] ?? null,
            'stock_status' => $filters['stock_status'] ?? null,
            'threshold' => $filters['threshold'] ?? null,
            'per_page' => $filters['per_page'] ?? 20,
        ]), $token);
    }

    public function catalogHealth(string $token, string $locale, array $filters = []): array
    {
        return $this->send('get', 'admin/catalog-health', $this->clean([
            'locale' => $this->locale($locale),
            'q' => $filters['q'] ?? null,
            'status' => $filters['status'] ?? null,
            'page' => $filters['page'] ?? null,
            'per_page' => $filters['per_page'] ?? 25,
        ]), $token);
    }

    public function createProduct(string $token, array $payload): array
    {
        return $this->send('post', 'admin/products', $payload, $token);
    }

    public function product(string $token, int|string $product, string $locale): array
    {
        return $this->send('get', "admin/products/{$product}", [
            'locale' => $this->locale($locale),
        ], $token);
    }

    public function updateProduct(string $token, int|string $product, array $payload): array
    {
        return $this->send('patch', "admin/products/{$product}", $payload, $token);
    }

    public function publishProduct(string $token, int|string $product): array
    {
        return $this->send('post', "admin/products/{$product}/publish", [], $token);
    }

    public function unpublishProduct(string $token, int|string $product): array
    {
        return $this->send('post', "admin/products/{$product}/unpublish", [], $token);
    }

    public function categories(string $token, array $filters = []): array
    {
        return $this->send('get', 'admin/categories', $this->clean([
            'q' => $filters['q'] ?? null,
            'is_active' => $filters['is_active'] ?? null,
            'per_page' => $filters['per_page'] ?? 20,
        ]), $token);
    }

    public function createCategory(string $token, array $payload): array
    {
        return $this->send('post', 'admin/categories', $payload, $token);
    }

    public function updateCategory(string $token, int|string $category, array $payload): array
    {
        return $this->send('patch', "admin/categories/{$category}", $payload, $token);
    }

    public function activateCategory(string $token, int|string $category): array
    {
        return $this->send('post', "admin/categories/{$category}/activate", [], $token);
    }

    public function deactivateCategory(string $token, int|string $category): array
    {
        return $this->send('post', "admin/categories/{$category}/deactivate", [], $token);
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

    public function orders(string $token, string $locale, array $filters = []): array
    {
        return $this->send('get', 'admin/orders', $this->clean([
            'locale' => $this->locale($locale),
            'id' => $filters['id'] ?? null,
            'q' => $filters['q'] ?? null,
            'customer' => $filters['customer'] ?? null,
            'new_customer' => $filters['new_customer'] ?? null,
            'total' => $filters['total'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_status' => $filters['payment_status'] ?? null,
            'fulfillment_status' => $filters['fulfillment_status'] ?? null,
            'carrier' => $filters['carrier'] ?? null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'per_page' => $filters['per_page'] ?? 25,
        ]), $token);
    }

    public function createOrder(string $token, array $payload): array
    {
        return $this->send('post', 'admin/orders', $payload, $token);
    }

    public function order(string $token, int|string $order, string $locale): array
    {
        return $this->send('get', "admin/orders/{$order}", [
            'locale' => $this->locale($locale),
        ], $token);
    }

    public function invoices(string $token, string $locale, array $filters = []): array
    {
        return $this->send('get', 'admin/invoices', $this->clean([
            'locale' => $this->locale($locale),
            'q' => $filters['q'] ?? null,
            'status' => $filters['status'] ?? null,
            'payment_status' => $filters['payment_status'] ?? null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'per_page' => $filters['per_page'] ?? 25,
            'page' => $filters['page'] ?? null,
        ]), $token);
    }

    public function invoice(string $token, int|string $invoice, string $locale): array
    {
        return $this->send('get', "admin/invoices/{$invoice}", [
            'locale' => $this->locale($locale),
        ], $token);
    }

    public function carts(string $token, string $locale, array $filters = []): array
    {
        return $this->send('get', 'admin/carts', $this->clean([
            'locale' => $this->locale($locale),
            'q' => $filters['q'] ?? null,
            'status' => $filters['status'] ?? null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'page' => $filters['page'] ?? null,
            'per_page' => $filters['per_page'] ?? 25,
        ]), $token);
    }

    public function cart(string $token, int|string $cart, string $locale): array
    {
        return $this->send('get', "admin/carts/{$cart}", [
            'locale' => $this->locale($locale),
        ], $token);
    }

    public function createCartRecoveryLink(string $token, int|string $cart): array
    {
        return $this->send('post', "admin/carts/{$cart}/recovery-links", [], $token);
    }

    public function updateOrder(string $token, int|string $order, array $payload): array
    {
        return $this->send('patch', "admin/orders/{$order}", $this->clean($payload), $token);
    }

    public function orderConversation(string $token, int|string $order): array
    {
        return $this->send('get', "admin/orders/{$order}/conversation", [], $token);
    }

    public function openOrderConversation(string $token, int|string $order): array
    {
        return $this->send('post', "admin/orders/{$order}/conversation/open", [], $token);
    }

    public function sendOrderMessage(string $token, int|string $order, string $body): array
    {
        return $this->send('post', "admin/orders/{$order}/conversation/messages", [
            'body' => $body,
        ], $token);
    }

    public function markOrderConversationRead(string $token, int|string $order): array
    {
        return $this->send('post', "admin/orders/{$order}/conversation/read", [], $token);
    }

    public function closeOrderConversation(string $token, int|string $order): array
    {
        return $this->send('post', "admin/orders/{$order}/conversation/close", [], $token);
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

    public function customers(string $token, array $filters = []): array
    {
        return $this->send('get', 'admin/customers', $this->clean([
            'q' => $filters['q'] ?? null,
            'status' => $filters['status'] ?? null,
            'country_code' => $filters['country_code'] ?? null,
            'per_page' => $filters['per_page'] ?? 25,
            'page' => $filters['page'] ?? null,
        ]), $token);
    }

    public function customer(string $token, int|string $customer): array
    {
        return $this->send('get', "admin/customers/{$customer}", [], $token);
    }

    public function updateCustomer(string $token, int|string $customer, array $payload): array
    {
        return $this->send('patch', "admin/customers/{$customer}", $payload, $token);
    }

    public function createUser(string $token, array $payload): array
    {
        return $this->send('post', 'admin/users', $payload, $token);
    }

    public function updateUser(string $token, int|string $user, array $payload): array
    {
        return $this->send('patch', "admin/users/{$user}", $payload, $token);
    }

    public function assignUserRoles(string $token, int|string $user, array $roles): array
    {
        return $this->send('post', "admin/users/{$user}/roles", ['roles' => $roles], $token);
    }

    public function suspendUser(string $token, int|string $user): array
    {
        return $this->send('post', "admin/users/{$user}/suspend", [], $token);
    }

    public function roles(string $token): array
    {
        return $this->send('get', 'admin/roles', [], $token);
    }

    public function syncRolePermissions(string $token, int|string $role, array $permissions): array
    {
        return $this->send('patch', "admin/roles/{$role}/permissions", [
            'permissions' => array_values($permissions),
        ], $token);
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

    public function paymentMethodSchemas(string $token): array
    {
        return $this->send('get', 'admin/payment-methods/schemas', [], $token);
    }

    public function paymentMethods(string $token, array $filters = []): array
    {
        return $this->send('get', 'admin/payment-methods', $this->clean([
            'provider' => $filters['provider'] ?? null,
            'environment' => $filters['environment'] ?? null,
            'status' => $filters['status'] ?? null,
            'is_enabled' => $filters['is_enabled'] ?? null,
            'q' => $filters['q'] ?? null,
            'per_page' => $filters['per_page'] ?? 50,
        ]), $token);
    }

    public function createPaymentMethod(string $token, array $payload): array
    {
        return $this->send('post', 'admin/payment-methods', $payload, $token);
    }

    public function updatePaymentMethod(string $token, int|string $paymentMethod, array $payload): array
    {
        return $this->send('patch', "admin/payment-methods/{$paymentMethod}", $payload, $token);
    }

    public function activatePaymentMethod(string $token, int|string $paymentMethod): array
    {
        return $this->send('post', "admin/payment-methods/{$paymentMethod}/activate", [], $token);
    }

    public function deactivatePaymentMethod(string $token, int|string $paymentMethod): array
    {
        return $this->send('post', "admin/payment-methods/{$paymentMethod}/deactivate", [], $token);
    }

    public function testPaymentMethod(string $token, int|string $paymentMethod): array
    {
        return $this->send('post', "admin/payment-methods/{$paymentMethod}/test-connection", [], $token);
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
