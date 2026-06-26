<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\OrderResource;
use App\Support\OrderStatusCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $this->customer_locale ?: $request->query('locale', 'fr');
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $base = (new OrderResource($this->resource))->toArray($request);
        $metadata = is_array($this->metadata) ? $this->metadata : [];
        $tracking = $metadata['tracking'] ?? [];
        $baseTracking = is_array($base['tracking'] ?? null) ? $base['tracking'] : [];

        return [
            ...$base,
            'status_label' => OrderStatusCatalog::label('order', $this->status, $locale),
            'payment_status_label' => OrderStatusCatalog::label('payment', $this->payment_status, $locale),
            'fulfillment_status_label' => OrderStatusCatalog::label('fulfillment', $this->fulfillment_status, $locale),
            'tracking' => [
                'number' => $tracking['number'] ?? $baseTracking['number'] ?? null,
                'url' => $tracking['url'] ?? $baseTracking['url'] ?? null,
                'updated_at' => $tracking['updated_at'] ?? $baseTracking['updated_at'] ?? null,
                'shipment_status' => $baseTracking['shipment_status'] ?? null,
                'external_shipment_id' => $baseTracking['external_shipment_id'] ?? null,
            ],
            'payment_method' => $metadata['payment']['method'] ?? $metadata['payment_method'] ?? null,
            'is_new_customer' => $this->user_id
                ? ! \App\Models\Order::query()
                    ->where('user_id', $this->user_id)
                    ->where('id', '<', $this->id)
                    ->exists()
                : null,
            'admin_notes' => $metadata['admin_notes'] ?? [],
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'status' => $this->user->status,
                'country_code' => $this->user->country_code,
            ] : null),
            'status_options' => [
                'order' => OrderStatusCatalog::options('order', $locale),
                'payment' => OrderStatusCatalog::options('payment', $locale),
                'fulfillment' => OrderStatusCatalog::options('fulfillment', $locale),
            ],
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
