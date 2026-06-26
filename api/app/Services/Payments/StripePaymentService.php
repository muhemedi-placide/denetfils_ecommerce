<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\Payments\Concerns\UpdatesOrderPaymentStatus;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripePaymentService
{
    use UpdatesOrderPaymentStatus;

    public function __construct(private readonly PaymentMethodResolver $methods)
    {
    }

    public function createPaymentIntent(Order $order): array
    {
        $method = $this->methods->activeForOrder($order, 'stripe');
        $credentials = $this->methods->credentials($method);
        $secretKey = $credentials['restricted_key'] ?? $credentials['secret_key'] ?? config('services.stripe.secret');
        $publishableKey = $credentials['publishable_key'] ?? config('services.stripe.key');

        if (blank($secretKey)) {
            throw new PaymentGatewayException('Stripe secret key is not configured.', 422);
        }

        $existing = $this->reusablePayment($order);

        if ($existing) {
            return $this->paymentIntentResponse($existing, $publishableKey);
        }

        try {
            $intent = (new StripeClient($secretKey))->paymentIntents->create([
                'amount' => $order->total_cents,
                'currency' => strtolower($order->currency),
                'automatic_payment_methods' => ['enabled' => true],
                'description' => "Order {$order->order_number}",
                'receipt_email' => $order->customer_email,
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => (string) $order->user_id,
                ],
            ], [
                'idempotency_key' => "order:{$order->id}:stripe:payment_intent",
            ]);
        } catch (ApiErrorException $exception) {
            throw new PaymentGatewayException($exception->getMessage(), 502, $exception);
        }

        $payment = OrderPayment::query()->updateOrCreate(
            ['provider' => 'stripe', 'provider_reference' => $intent->id],
            [
                'order_id' => $order->id,
                'payment_method_id' => $method->exists ? $method->id : null,
                'status' => $intent->status,
                'amount_cents' => $intent->amount,
                'currency' => strtoupper($intent->currency),
                'client_secret' => $intent->client_secret,
                'provider_payload' => $intent->toArray(),
            ],
        );

        return $this->paymentIntentResponse($payment, $publishableKey);
    }

    public function confirmPaymentIntent(Order $order, string $paymentIntentId): array
    {
        $payment = $order->payments()
            ->with('paymentMethod')
            ->where('provider', 'stripe')
            ->where('provider_reference', $paymentIntentId)
            ->first();

        if (! $payment) {
            throw new PaymentGatewayException('Stripe payment intent was not found for this order.', 404);
        }

        $method = $payment->paymentMethod ?: $this->methods->activeForOrder($order, 'stripe');
        $credentials = $this->methods->credentials($method);
        $secretKey = $credentials['restricted_key'] ?? $credentials['secret_key'] ?? config('services.stripe.secret');
        $publishableKey = $credentials['publishable_key'] ?? config('services.stripe.key');

        if (blank($secretKey)) {
            throw new PaymentGatewayException('Stripe secret key is not configured.', 422);
        }

        try {
            $intent = (new StripeClient($secretKey))->paymentIntents->retrieve($paymentIntentId, []);
        } catch (ApiErrorException $exception) {
            throw new PaymentGatewayException($exception->getMessage(), 502, $exception);
        }

        if ((int) $intent->amount !== (int) $order->total_cents || strtoupper((string) $intent->currency) !== strtoupper($order->currency)) {
            throw new PaymentGatewayException('Stripe payment intent does not match this order.', 409);
        }

        $payment->forceFill([
            'status' => $intent->status,
            'amount_cents' => $intent->amount,
            'currency' => strtoupper($intent->currency),
            'client_secret' => $intent->client_secret ?? $payment->client_secret,
            'provider_payload' => $intent->toArray(),
        ])->save();

        match ($intent->status) {
            'succeeded' => $this->markOrderPaymentSucceeded($order, $payment),
            'canceled' => $this->markOrderPaymentFailed($order, $payment, 'canceled'),
            default => null,
        };

        $freshOrder = $order->fresh();

        return [
            ...$this->paymentIntentResponse($payment->refresh(), $publishableKey),
            'order' => [
                'id' => $freshOrder->id,
                'status' => $freshOrder->status,
                'payment_status' => $freshOrder->payment_status,
                'fulfillment_status' => $freshOrder->fulfillment_status,
            ],
        ];
    }

    public function handleWebhook(array $payload): ?OrderPayment
    {
        $eventType = (string) ($payload['type'] ?? '');
        $intent = $payload['data']['object'] ?? [];

        if (($intent['object'] ?? null) !== 'payment_intent') {
            return null;
        }

        $payment = OrderPayment::query()
            ->where('provider', 'stripe')
            ->where('provider_reference', $intent['id'] ?? null)
            ->first();

        if (! $payment) {
            return null;
        }

        $payment->forceFill([
            'status' => $intent['status'] ?? $payment->status,
            'provider_payload' => $intent,
        ])->save();

        $order = $payment->order;

        match ($eventType) {
            'payment_intent.succeeded' => $this->markOrderPaymentSucceeded($order, $payment),
            'payment_intent.payment_failed' => $this->markOrderPaymentFailed($order, $payment, 'failed'),
            'payment_intent.canceled' => $this->markOrderPaymentFailed($order, $payment, 'canceled'),
            default => null,
        };

        return $payment->refresh();
    }

    public function webhookSigningSecret(array $payload): ?string
    {
        $intent = $payload['data']['object'] ?? [];

        if (($intent['object'] ?? null) === 'payment_intent' && filled($intent['id'] ?? null)) {
            $payment = OrderPayment::query()
                ->with('paymentMethod')
                ->where('provider', 'stripe')
                ->where('provider_reference', $intent['id'])
                ->first();

            $secret = $payment?->paymentMethod?->credentials['webhook_signing_secret'] ?? null;

            if (filled($secret)) {
                return $secret;
            }
        }

        return config('services.stripe.webhook_secret');
    }


    private function reusablePayment(Order $order): ?OrderPayment
    {
        return $order->payments()
            ->where('provider', 'stripe')
            ->whereIn('status', ['requires_payment_method', 'requires_confirmation', 'requires_action', 'processing'])
            ->whereNotNull('client_secret')
            ->latest('id')
            ->first();
    }

    private function paymentIntentResponse(OrderPayment $payment, ?string $publishableKey): array
    {
        return [
            'provider' => 'stripe',
            'payment_id' => $payment->id,
            'external_id' => $payment->provider_reference,
            'status' => $payment->status,
            'amount_cents' => $payment->amount_cents,
            'currency' => $payment->currency,
            'client_secret' => $payment->client_secret,
            'publishable_key' => $publishableKey,
        ];
    }
}
