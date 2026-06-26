<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payments\PayPalPaymentService;
use App\Services\Payments\PaymentGatewayException;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
