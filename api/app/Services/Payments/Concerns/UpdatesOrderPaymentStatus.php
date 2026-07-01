<?php

namespace App\Services\Payments\Concerns;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\Orders\InvoiceService;

trait UpdatesOrderPaymentStatus
{
    private function markOrderPaymentSucceeded(Order $order, OrderPayment $payment, string $status = 'captured'): void
    {
        $payment->forceFill([
            'status' => $status,
            'captured_at' => $payment->captured_at ?? now(),
        ])->save();

        $order->forceFill([
            'payment_status' => 'paid',
            'status' => $order->status === 'pending_payment' ? 'confirmed' : $order->status,
            'fulfillment_status' => $order->fulfillment_status === 'unfulfilled' ? 'preparing' : $order->fulfillment_status,
        ])->save();
        app(InvoiceService::class)->syncForOrder($order);
    }

    private function markOrderPaymentFailed(Order $order, OrderPayment $payment, string $status): void
    {
        $payment->forceFill(['status' => $status])->save();

        if ($order->payment_status !== 'paid') {
            $order->forceFill(['payment_status' => 'failed'])->save();
            app(InvoiceService::class)->syncForOrder($order);
        }
    }
}
