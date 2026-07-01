<?php

namespace App\Services\Orders;

use App\Models\Invoice;
use App\Models\Order;

class InvoiceService
{
    public function syncForOrder(Order $order): Invoice
    {
        $status = match (true) {
            $order->payment_status === 'refunded' || $order->status === 'refunded' => 'refunded',
            $order->status === 'cancelled' => 'void',
            $order->payment_status === 'paid' => 'paid',
            in_array($order->status, ['confirmed', 'processing', 'completed'], true) => 'issued',
            default => 'draft',
        };
        $invoice = $order->invoice()->firstOrNew();
        $issuedAt = $invoice->issued_at;

        if ($status !== 'draft' && ! $issuedAt) {
            $issuedAt = $order->placed_at ?? $order->created_at ?? now();
        }

        $invoice->fill([
            'invoice_number' => $invoice->invoice_number ?: 'FAC-'.$order->order_number,
            'status' => $status,
            'currency' => $order->currency,
            'total_cents' => $order->total_cents,
            'issued_at' => $issuedAt,
            'due_at' => $issuedAt ? $issuedAt->copy()->addDays(30) : null,
            'paid_at' => $status === 'paid' ? ($invoice->paid_at ?? now()) : null,
        ])->save();

        return $invoice;
    }
}
