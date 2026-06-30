<?php

namespace App\Services\Payments;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\Payments\Concerns\UpdatesOrderPaymentStatus;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PayPalPaymentService
{
    use UpdatesOrderPaymentStatus;

    public function __construct(private readonly PaymentMethodResolver $methods)
    {
    }

    public function createOrder(Order $order, array $options = []): array
    {
        $method = $this->methods->activeForOrder($order, 'paypal');

        $existingPayment = $order->payments()
            ->where('provider', 'paypal')
            ->whereNotNull('provider_reference')
            ->whereNotIn('status', ['failed', 'denied', 'declined', 'captured'])
            ->latest('id')
            ->first();

        if ($existingPayment) {
            return $this->orderResponse($existingPayment);
        }

        $experienceContext = array_filter([
            'brand_name' => Str::limit((string) config('app.name'), 127, ''),
            'user_action' => 'PAY_NOW',
            'shipping_preference' => 'GET_FROM_FILE',
            'return_url' => $this->absoluteUrl($options['return_url'] ?? config('services.paypal.return_url')),
            'cancel_url' => $this->absoluteUrl($options['cancel_url'] ?? config('services.paypal.cancel_url')),
        ]);

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order->order_number,
                'invoice_id' => $order->order_number,
                'amount' => [
                    'currency_code' => strtoupper($order->currency),
                    'value' => $this->decimalAmount($order->total_cents),
                ],
            ]],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => $experienceContext,
                ],
            ],
        ];

        try {
            $response = Http::withToken($this->accessToken($method))
                ->acceptJson()
                ->withHeaders(['PayPal-Request-Id' => "order-{$order->id}-paypal-create"])
                ->post($this->baseUrl($method).'/v2/checkout/orders', $payload)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new PaymentGatewayException($this->paypalErrorMessage($exception), 502, $exception);
        }

        if (blank($response['id'] ?? null)) {
            throw new PaymentGatewayException('PayPal did not return an order id.', 502);
        }

        $approvalUrl = collect($response['links'] ?? [])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        $payment = OrderPayment::query()->updateOrCreate(
            ['provider' => 'paypal', 'provider_reference' => $response['id'] ?? null],
            [
                'order_id' => $order->id,
                'payment_method_id' => $method->exists ? $method->id : null,
                'status' => $response['status'] ?? 'CREATED',
                'amount_cents' => $order->total_cents,
                'currency' => strtoupper($order->currency),
                'approval_url' => $approvalUrl,
                'provider_payload' => $response,
            ],
        );

        return $this->orderResponse($payment);
    }

    public function captureOrder(Order $order, string $paypalOrderId): array
    {
        $method = $this->methods->activeForOrder($order, 'paypal');
        $payment = $order->payments()
            ->where('provider', 'paypal')
            ->where('provider_reference', $paypalOrderId)
            ->firstOrFail();

        try {
            $response = Http::withToken($this->accessToken($method))
                ->acceptJson()
                ->withHeaders(['PayPal-Request-Id' => "order-{$order->id}-paypal-capture-{$paypalOrderId}"])
                ->post($this->baseUrl($method)."/v2/checkout/orders/{$paypalOrderId}/capture")
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new PaymentGatewayException($this->paypalErrorMessage($exception), 502, $exception);
        }

        $payment->forceFill([
            'status' => $response['status'] ?? $payment->status,
            'provider_payload' => $response,
        ])->save();

        if (($response['status'] ?? null) === 'COMPLETED') {
            $this->syncPaypalCustomerDetails($order, $response);
            $this->markOrderPaymentSucceeded($order, $payment);
        }

        return $this->orderResponse($payment->refresh());
    }

    public function createExpressOrder(Cart $cart, array $quote, string $countryCode, array $options = []): array
    {
        $method = $this->methods->activeForContext('paypal', $cart->currency, $countryCode);
        $checkoutToken = Str::random(64);
        $experienceContext = array_filter([
            'brand_name' => Str::limit((string) config('app.name'), 127, ''),
            'user_action' => 'PAY_NOW',
            'shipping_preference' => 'GET_FROM_FILE',
            'return_url' => $this->absoluteUrl($options['return_url'] ?? config('services.paypal.return_url')),
            'cancel_url' => $this->absoluteUrl($options['cancel_url'] ?? config('services.paypal.cancel_url')),
        ]);
        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => 'express-'.$cart->id,
                'custom_id' => hash('sha256', $checkoutToken),
                'amount' => [
                    'currency_code' => strtoupper($cart->currency),
                    'value' => $this->decimalAmount((int) $quote['total_cents']),
                ],
            ]],
            'payment_source' => ['paypal' => ['experience_context' => $experienceContext]],
        ];

        try {
            $response = Http::withToken($this->accessToken($method))
                ->acceptJson()
                ->withHeaders(['PayPal-Request-Id' => 'express-cart-'.$cart->id])
                ->post($this->baseUrl($method).'/v2/checkout/orders', $payload)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new PaymentGatewayException($this->paypalErrorMessage($exception), 502, $exception);
        }

        $paypalOrderId = (string) ($response['id'] ?? '');
        $approvalLink = collect($response['links'] ?? [])
            ->first(fn (array $link) => in_array($link['rel'] ?? null, ['payer-action', 'approve'], true));
        $approvalUrl = $approvalLink['href'] ?? null;

        if ($paypalOrderId === '' || blank($approvalUrl)) {
            throw new PaymentGatewayException('PayPal did not return an express checkout link.', 502);
        }

        Cache::put("paypal-express:{$checkoutToken}", [
            'paypal_order_id' => $paypalOrderId,
            'cart_token' => $cart->cart_token,
            'amount_cents' => (int) $quote['total_cents'],
            'currency' => strtoupper($cart->currency),
            'country_code' => strtoupper($countryCode),
            'locale' => $options['locale'] ?? 'fr',
            'payment_method_id' => $method->exists ? $method->id : null,
            'approval_url' => $approvalUrl,
        ], now()->addHour());

        return [
            'checkout_token' => $checkoutToken,
            'external_id' => $paypalOrderId,
            'approval_url' => $approvalUrl,
        ];
    }

    public function approvedExpressOrder(string $checkoutToken, string $paypalOrderId): array
    {
        $context = Cache::get("paypal-express:{$checkoutToken}");

        if (! is_array($context) || ! hash_equals((string) ($context['paypal_order_id'] ?? ''), $paypalOrderId)) {
            throw new PaymentGatewayException('The PayPal express checkout session is invalid or expired.', 422);
        }

        $method = $this->methods->activeForContext('paypal', $context['currency'], $context['country_code']);

        try {
            $details = Http::withToken($this->accessToken($method))
                ->acceptJson()
                ->get($this->baseUrl($method)."/v2/checkout/orders/{$paypalOrderId}")
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new PaymentGatewayException($this->paypalErrorMessage($exception), 502, $exception);
        }

        if (! in_array(strtoupper((string) ($details['status'] ?? '')), ['APPROVED', 'COMPLETED'], true)) {
            throw new PaymentGatewayException('The PayPal order has not been approved.', 422);
        }

        if (! hash_equals(hash('sha256', $checkoutToken), (string) data_get($details, 'purchase_units.0.custom_id', ''))) {
            throw new PaymentGatewayException('The PayPal order does not match this checkout.', 409);
        }

        return ['context' => $context, 'details' => $details];
    }

    public function captureExpressOrder(Order $order, array $express, string $checkoutToken): array
    {
        $context = $express['context'];
        $details = $express['details'];
        $paypalOrderId = (string) $context['paypal_order_id'];

        OrderPayment::query()->updateOrCreate(
            ['provider' => 'paypal', 'provider_reference' => $paypalOrderId],
            [
                'order_id' => $order->id,
                'payment_method_id' => $context['payment_method_id'],
                'status' => $details['status'] ?? 'APPROVED',
                'amount_cents' => $context['amount_cents'],
                'currency' => $context['currency'],
                'approval_url' => $context['approval_url'],
                'provider_payload' => $details,
            ],
        );

        $result = $this->captureOrder($order, $paypalOrderId);
        Cache::forget("paypal-express:{$checkoutToken}");

        return $result;
    }

    public function handleWebhook(array $payload): ?OrderPayment
    {
        $eventType = (string) ($payload['event_type'] ?? '');
        $resource = $payload['resource'] ?? [];
        $payment = $this->paymentForWebhook($payload);

        if (! $payment) {
            return null;
        }

        $payment->forceFill([
            'status' => $resource['status'] ?? $payment->status,
            'provider_payload' => $payload,
        ])->save();

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            $this->markOrderPaymentSucceeded($payment->order, $payment);
        }

        if (in_array($eventType, ['PAYMENT.CAPTURE.DENIED', 'PAYMENT.CAPTURE.DECLINED', 'CHECKOUT.PAYMENT-APPROVAL.REVERSED'], true)) {
            $this->markOrderPaymentFailed($payment->order, $payment, 'failed');
        }

        return $payment->refresh();
    }

    public function paymentForWebhook(array $payload): ?OrderPayment
    {
        $resource = $payload['resource'] ?? [];
        $paypalOrderId = $resource['supplementary_data']['related_ids']['order_id']
            ?? $resource['id']
            ?? null;

        if (! $paypalOrderId) {
            return null;
        }

        return OrderPayment::query()
            ->where('provider', 'paypal')
            ->where('provider_reference', $paypalOrderId)
            ->first();
    }

    public function verifyWebhook(array $payload, array $headers, OrderPayment $payment): bool
    {
        $method = $payment->paymentMethod ?: $this->methods->activeForOrder($payment->order, 'paypal');
        $webhookId = ($method->credentials ?? [])['webhook_id'] ?? config('services.paypal.webhook_id');

        if (blank($webhookId)) {
            return true;
        }

        try {
            $response = Http::withToken($this->accessToken($method))
                ->acceptJson()
                ->post($this->baseUrl($method).'/v1/notifications/verify-webhook-signature', [
                    'auth_algo' => $headers['paypal-auth-algo'] ?? null,
                    'cert_url' => $headers['paypal-cert-url'] ?? null,
                    'transmission_id' => $headers['paypal-transmission-id'] ?? null,
                    'transmission_sig' => $headers['paypal-transmission-sig'] ?? null,
                    'transmission_time' => $headers['paypal-transmission-time'] ?? null,
                    'webhook_id' => $webhookId,
                    'webhook_event' => $payload,
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new PaymentGatewayException($this->paypalErrorMessage($exception), 502, $exception);
        }

        return ($response['verification_status'] ?? null) === 'SUCCESS';
    }

    private function accessToken($method): string
    {
        $credentials = $this->methods->credentials($method);
        $clientId = $credentials['client_id'] ?? config('services.paypal.client_id');
        $clientSecret = $credentials['client_secret'] ?? config('services.paypal.client_secret');

        if (blank($clientId) || blank($clientSecret)) {
            throw new PaymentGatewayException('PayPal client credentials are not configured.', 422);
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->acceptJson()
                ->post($this->baseUrl($method).'/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new PaymentGatewayException($this->paypalErrorMessage($exception), 502, $exception);
        }

        if (blank($response['access_token'] ?? null)) {
            throw new PaymentGatewayException('PayPal did not return an access token.', 502);
        }

        return (string) $response['access_token'];
    }

    private function baseUrl($method): string
    {
        $environment = $method->environment ?: config('services.paypal.mode', 'sandbox');

        return $environment === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function decimalAmount(int $amountCents): string
    {
        return number_format($amountCents / 100, 2, '.', '');
    }

    private function absoluteUrl(mixed $value): ?string
    {
        $url = trim((string) $value);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }

    private function orderResponse(OrderPayment $payment): array
    {
        $credentials = $payment->paymentMethod?->credentials ?? [];
        $payload = $payment->provider_payload ?? [];
        $paypal = data_get($payload, 'payment_source.paypal', []);
        $shipping = data_get($payload, 'purchase_units.0.shipping', []);

        return [
            'provider' => 'paypal',
            'payment_id' => $payment->id,
            'external_id' => $payment->provider_reference,
            'status' => $payment->status,
            'amount_cents' => $payment->amount_cents,
            'currency' => $payment->currency,
            'approval_url' => $payment->approval_url,
            'client_id' => $credentials['client_id'] ?? config('services.paypal.client_id'),
            'payer' => [
                'email' => $paypal['email_address'] ?? null,
                'first_name' => data_get($paypal, 'name.given_name'),
                'last_name' => data_get($paypal, 'name.surname'),
            ],
            'shipping' => [
                'recipient_name' => data_get($shipping, 'name.full_name'),
                'street_line_1' => data_get($shipping, 'address.address_line_1'),
                'street_line_2' => data_get($shipping, 'address.address_line_2'),
                'city' => data_get($shipping, 'address.admin_area_2'),
                'region' => data_get($shipping, 'address.admin_area_1'),
                'postal_code' => data_get($shipping, 'address.postal_code'),
                'country_code' => data_get($shipping, 'address.country_code'),
            ],
        ];
    }

    private function syncPaypalCustomerDetails(Order $order, array $payload): void
    {
        $paypal = data_get($payload, 'payment_source.paypal', []);
        $shipping = data_get($payload, 'purchase_units.0.shipping', []);
        $address = $shipping['address'] ?? [];
        $recipientName = trim((string) data_get($shipping, 'name.full_name'));

        $order->forceFill(array_filter([
            'customer_email' => $paypal['email_address'] ?? null,
            'customer_name' => trim(implode(' ', array_filter([
                data_get($paypal, 'name.given_name'),
                data_get($paypal, 'name.surname'),
            ]))) ?: $recipientName,
            'customer_country_code' => $address['country_code'] ?? null,
        ]))->save();

        if ($recipientName === '' || blank($address['address_line_1'] ?? null) || blank($address['postal_code'] ?? null) || blank($address['admin_area_2'] ?? null) || blank($address['country_code'] ?? null)) {
            return;
        }

        $order->addresses()->where('type', 'shipping')->update([
            'recipient_name' => $recipientName,
            'street_line_1' => $address['address_line_1'],
            'street_line_2' => $address['address_line_2'] ?? null,
            'postal_code' => $address['postal_code'],
            'city' => $address['admin_area_2'],
            'region' => $address['admin_area_1'] ?? null,
            'country_code' => strtoupper($address['country_code']),
        ]);
    }

    private function paypalErrorMessage(RequestException $exception): string
    {
        $response = $exception->response?->json();
        $message = $response['message'] ?? $response['error_description'] ?? $exception->getMessage();
        $detail = collect($response['details'] ?? [])
            ->map(fn (array $detail) => trim(implode(' ', array_filter([
                $detail['issue'] ?? null,
                $detail['field'] ?? null,
                $detail['description'] ?? null,
            ]))))
            ->filter()
            ->first();

        if ($detail) {
            $message .= " {$detail}";
        }

        return Str::limit((string) $message, 300, '');
    }
}
