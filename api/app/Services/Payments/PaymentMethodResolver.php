<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Validation\ValidationException;

class PaymentMethodResolver
{
    public function activeForOrder(Order $order, string $provider): PaymentMethod
    {
        $method = PaymentMethod::query()
            ->where('provider', $provider)
            ->where('status', 'active')
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->first(fn (PaymentMethod $method) => $this->supportsOrder($method, $order));

        if ($method) {
            return $method;
        }

        $fallback = $this->fallbackMethod($provider, $order);

        if ($fallback) {
            return $fallback;
        }

        throw ValidationException::withMessages([
            'provider' => "No active {$provider} payment method is configured for this order.",
        ]);
    }

    public function credentials(PaymentMethod $method): array
    {
        return $method->credentials ?? [];
    }

    private function supportsOrder(PaymentMethod $method, Order $order): bool
    {
        $currencies = array_map('strtoupper', $method->currencies ?? []);
        $countries = array_map('strtoupper', $method->countries ?? []);

        if ($currencies !== [] && ! in_array(strtoupper($order->currency), $currencies, true)) {
            return false;
        }

        if ($countries !== [] && $order->customer_country_code && ! in_array(strtoupper($order->customer_country_code), $countries, true)) {
            return false;
        }

        return true;
    }

    private function fallbackMethod(string $provider, Order $order): ?PaymentMethod
    {
        $credentials = match ($provider) {
            'stripe' => [
                'publishable_key' => config('services.stripe.key'),
                'secret_key' => config('services.stripe.secret'),
                'webhook_signing_secret' => config('services.stripe.webhook_secret'),
            ],
            'paypal' => [
                'client_id' => config('services.paypal.client_id'),
                'client_secret' => config('services.paypal.client_secret'),
                'webhook_id' => config('services.paypal.webhook_id'),
            ],
            default => [],
        };

        $credentials = array_filter($credentials, fn ($value) => filled($value));

        if ($provider === 'stripe' && blank($credentials['secret_key'] ?? null)) {
            return null;
        }

        if ($provider === 'paypal' && (blank($credentials['client_id'] ?? null) || blank($credentials['client_secret'] ?? null))) {
            return null;
        }

        return PaymentMethod::make([
            'provider' => $provider,
            'code' => "{$provider}_env",
            'display_name' => ['fr' => ucfirst($provider)],
            'environment' => config("services.{$provider}.mode", config("services.{$provider}.environment", 'sandbox')),
            'status' => 'active',
            'is_enabled' => true,
            'currencies' => [strtoupper($order->currency)],
            'countries' => $order->customer_country_code ? [strtoupper($order->customer_country_code)] : [],
            'credentials' => $credentials,
        ]);
    }
}
