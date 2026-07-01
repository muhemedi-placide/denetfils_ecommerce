<?php

namespace App\Http\Resources\Admin;

use App\Support\MoneyFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = in_array($request->query('locale'), ['fr', 'en'], true)
            ? (string) $request->query('locale')
            : 'fr';

        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'status_label' => $this->statusLabel($locale),
            'currency' => $this->currency,
            'total_cents' => $this->total_cents,
            'formatted_total' => MoneyFormatter::format($this->total_cents, $this->currency, $locale),
            'issued_at' => $this->issued_at?->toIso8601String(),
            'due_at' => $this->due_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status,
                'payment_status' => $this->order->payment_status,
                'fulfillment_status' => $this->order->fulfillment_status,
                'customer' => [
                    'id' => $this->order->customer_id,
                    'name' => $this->order->customer_name,
                    'email' => $this->order->customer_email,
                    'phone' => $this->order->customer_phone,
                    'country_code' => $this->order->customer_country_code,
                ],
                'placed_at' => $this->order->placed_at?->toIso8601String(),
            ]),
            'order_detail' => $this->when(
                $this->relationLoaded('order') && $this->order->relationLoaded('items'),
                fn () => new OrderAdminResource($this->order),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function statusLabel(string $locale): string
    {
        $labels = [
            'fr' => [
                'draft' => 'Brouillon',
                'issued' => 'Emise',
                'paid' => 'Payee',
                'refunded' => 'Remboursee',
                'void' => 'Annulee',
            ],
            'en' => [
                'draft' => 'Draft',
                'issued' => 'Issued',
                'paid' => 'Paid',
                'refunded' => 'Refunded',
                'void' => 'Void',
            ],
        ];

        return $labels[$locale][$this->status] ?? $this->status;
    }
}
