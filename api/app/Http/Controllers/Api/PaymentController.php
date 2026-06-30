<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Notifications\WelcomeCustomerNotification;
use App\Services\Checkout\CheckoutQuoteService;
use App\Services\Core\UserProvisioningService;
use App\Services\Orders\OrderCreationService;
use App\Services\Payments\PayPalPaymentService;
use App\Services\Payments\PaymentGatewayException;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class PaymentController extends Controller
{
    public function createStripePaymentIntent(Request $request, Order $order, StripePaymentService $stripe): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        return $this->gatewayResponse(fn () => $stripe->createPaymentIntent($order));
    }

    public function confirmStripePaymentIntent(Request $request, Order $order, StripePaymentService $stripe): JsonResponse
    {
        $this->authorizeOrder($request, $order, allowPaid: true);

        $data = $request->validate([
            'payment_intent_id' => ['required', 'string', 'max:255'],
        ]);

        return $this->gatewayResponse(fn () => $stripe->confirmPaymentIntent($order, $data['payment_intent_id']));
    }

    public function createPaypalOrder(Request $request, Order $order, PayPalPaymentService $paypal): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'return_url' => ['nullable', 'url', 'max:2048'],
            'cancel_url' => ['nullable', 'url', 'max:2048'],
        ]);

        return $this->gatewayResponse(fn () => $paypal->createOrder($order, $data));
    }

    public function createPaypalExpressOrder(Request $request, PayPalPaymentService $paypal, CheckoutQuoteService $quotes): JsonResponse
    {
        $data = $request->validate([
            'cart_token' => ['required', 'string', 'max:64'],
            'country_code' => ['required', 'string', 'size:2'],
            'locale' => ['nullable', 'in:fr,en'],
            'return_url' => ['required', 'url', 'max:2048'],
            'cancel_url' => ['required', 'url', 'max:2048'],
        ]);

        return $this->gatewayResponse(function () use ($data, $paypal, $quotes) {
            $quote = $quotes->standardEstimate($data['cart_token'], $data['country_code'], $data['locale'] ?? 'fr');

            return $paypal->createExpressOrder(
                $quotes->cart($data['cart_token']),
                $quote,
                $data['country_code'],
                $data,
            );
        });
    }

    public function finalizePaypalExpressOrder(
        Request $request,
        PayPalPaymentService $paypal,
        CheckoutQuoteService $quotes,
        UserProvisioningService $users,
        OrderCreationService $orders,
    ): JsonResponse {
        $data = $request->validate([
            'checkout_token' => ['required', 'string', 'size:64'],
            'paypal_order_id' => ['required', 'string', 'max:255'],
        ]);

        return $this->gatewayResponse(function () use ($request, $data, $paypal, $quotes, $users, $orders) {
            $express = $paypal->approvedExpressOrder($data['checkout_token'], $data['paypal_order_id']);
            $details = $express['details'];
            $context = $express['context'];
            $paypalAccount = data_get($details, 'payment_source.paypal', []);
            $shipping = data_get($details, 'purchase_units.0.shipping', []);
            $address = $shipping['address'] ?? [];
            $email = strtolower(trim((string) ($paypalAccount['email_address'] ?? '')));
            $firstName = trim((string) data_get($paypalAccount, 'name.given_name'));
            $lastName = trim((string) data_get($paypalAccount, 'name.surname'));

            if ($email === '' || blank($address['address_line_1'] ?? null) || blank($address['postal_code'] ?? null) || blank($address['admin_area_2'] ?? null) || blank($address['country_code'] ?? null)) {
                throw new PaymentGatewayException('PayPal did not provide complete customer and shipping information.', 422);
            }

            $user = User::query()->where('email', $email)->first();
            $temporaryPassword = null;

            if (! $user) {
                $temporaryPassword = Str::password(14);
                $user = $users->registerCustomer([
                    'first_name' => $firstName ?: 'Client',
                    'last_name' => $lastName ?: 'PayPal',
                    'email' => $email,
                    'phone' => null,
                    'password' => $temporaryPassword,
                    'country_code' => strtoupper($address['country_code']),
                    'preferred_locale' => $context['locale'],
                    'timezone' => 'Europe/Paris',
                    'privacy_policy_consent' => true,
                    'terms_consent' => true,
                    'marketing_consent' => false,
                ], $request);
                $user->notify(new WelcomeCustomerNotification($context['locale'], $temporaryPassword));
            }

            if (! $user->isActive()) {
                throw new PaymentGatewayException('This customer account is not active.', 403);
            }

            $recipientName = trim((string) data_get($shipping, 'name.full_name')) ?: trim("{$firstName} {$lastName}");
            $shippingAddress = $user->addresses()->updateOrCreate([
                'type' => 'shipping',
                'street_line_1' => $address['address_line_1'],
                'postal_code' => $address['postal_code'],
                'city' => $address['admin_area_2'],
                'country_code' => strtoupper($address['country_code']),
            ], [
                'label' => 'PayPal',
                'recipient_name' => $recipientName,
                'street_line_2' => $address['address_line_2'] ?? null,
                'region' => $address['admin_area_1'] ?? null,
                'phone' => null,
                'is_default' => ! $user->addresses()->where('type', 'shipping')->where('is_default', true)->exists(),
            ]);

            $orderPayload = [
                'cart_token' => $context['cart_token'],
                'shipping_address_id' => $shippingAddress->id,
                'locale' => $context['locale'],
                'delivery_method' => 'standard',
                'carrier' => 'paypal_express',
                'metadata' => ['paypal_express' => true],
            ];
            $quote = $quotes->quoteForUser($user, $orderPayload);

            if ((int) $quote['total_cents'] !== (int) $context['amount_cents']) {
                throw new PaymentGatewayException('The delivery total changed for the PayPal address. Please restart express checkout.', 409);
            }

            $order = $orders->createFromCart($user, $orderPayload);
            $payment = $paypal->captureExpressOrder($order, $express, $data['checkout_token']);
            $token = $user->createToken('paypal-express-checkout')->plainTextToken;

            return [
                'token' => $token,
                'user' => $user->fresh(),
                'order' => $order->fresh()->load(['items', 'addresses', 'shipments.method', 'shipments.pickupPoint']),
                'payment' => $payment,
                'temporary_password_created' => $temporaryPassword !== null,
            ];
        });
    }

    public function capturePaypalOrder(Request $request, Order $order, string $paypalOrderId, PayPalPaymentService $paypal): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        return $this->gatewayResponse(fn () => $paypal->captureOrder($order, $paypalOrderId));
    }

    public function stripeWebhook(Request $request, StripePaymentService $stripe): JsonResponse
    {
        $payload = $request->getContent();

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            $secret = $stripe->webhookSigningSecret($decoded);
            $event = filled($secret)
                ? Webhook::constructEvent($payload, (string) $request->header('Stripe-Signature'), $secret)
                : $decoded;
        } catch (UnexpectedValueException|SignatureVerificationException|\JsonException) {
            return response()->json(['message' => 'Invalid Stripe webhook payload.'], Response::HTTP_BAD_REQUEST);
        }

        $stripe->handleWebhook(is_array($event) ? $event : $event->toArray());

        return response()->json(['received' => true]);
    }

    public function paypalWebhook(Request $request, PayPalPaymentService $paypal): JsonResponse
    {
        $payload = $request->json()->all();
        $payment = $paypal->paymentForWebhook($payload);

        try {
            if ($payment && ! $paypal->verifyWebhook($payload, $this->normalizedHeaders($request), $payment)) {
                return response()->json(['message' => 'Invalid PayPal webhook signature.'], Response::HTTP_BAD_REQUEST);
            }
        } catch (PaymentGatewayException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->statusCode());
        }

        if ($payment) {
            $paypal->handleWebhook($payload);
        }

        return response()->json(['received' => true]);
    }

    private function authorizeOrder(Request $request, Order $order, bool $allowPaid = false): void
    {
        abort_unless($order->user_id === $request->user()?->id, 404);
        abort_if(! $allowPaid && $order->payment_status === 'paid', 409, 'This order is already paid.');
    }

    private function gatewayResponse(callable $callback): JsonResponse
    {
        try {
            return response()->json(['data' => $callback()]);
        } catch (PaymentGatewayException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->statusCode());
        }
    }

    private function normalizedHeaders(Request $request): array
    {
        return collect($request->headers->all())
            ->mapWithKeys(fn (array $values, string $key) => [strtolower($key) => $values[0] ?? null])
            ->all();
    }
}
