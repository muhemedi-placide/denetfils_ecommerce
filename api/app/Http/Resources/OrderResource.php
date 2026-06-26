<?php

namespace App\Http\Resources;

use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Support\MoneyFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $this->customer_locale ?: $request->query('locale', 'fr');
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';

        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'fulfillment_status' => $this->fulfillment_status,
            'currency' => $this->currency,
            'subtotal_cents' => $this->subtotal_cents,
            'formatted_subtotal' => MoneyFormatter::format($this->subtotal_cents, $this->currency, $locale),
            'tax_cents' => $this->tax_cents,
            'formatted_tax' => MoneyFormatter::format($this->tax_cents, $this->currency, $locale),
            'shipping_cents' => $this->shipping_cents,
            'formatted_shipping' => MoneyFormatter::format($this->shipping_cents, $this->currency, $locale),
            'discount_cents' => $this->discount_cents,
            'formatted_discount' => MoneyFormatter::format($this->discount_cents, $this->currency, $locale),
            'total_cents' => $this->total_cents,
            'formatted_total' => MoneyFormatter::format($this->total_cents, $this->currency, $locale),
            'customer' => [
                'email' => $this->customer_email,
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
                'locale' => $this->customer_locale,
                'country_code' => $this->customer_country_code,
            ],
            'delivery_method' => $this->delivery_method,
            'carrier' => $this->carrier,
            'metadata' => $this->metadata,
            'placed_at' => $this->placed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $this->items
                ->map(fn (OrderItem $item) => $this->item($item, $locale))
                ->values()
                ->all()),
            'addresses' => $this->whenLoaded('addresses', fn () => $this->addresses
                ->sortBy(fn (OrderAddress $address) => $address->type === 'shipping' ? 0 : 1)
                ->map(fn (OrderAddress $address) => $this->address($address))
                ->values()
                ->all()),
            'shipments' => $this->whenLoaded('shipments', fn () => $this->shipments->map(fn ($shipment) => [
                'id' => $shipment->id,
                'status' => $shipment->status,
                'last_error' => $shipment->last_error,
                'tracking_number' => $shipment->tracking_number,
                'external_shipment_id' => $shipment->external_shipment_id,
                'has_label' => filled($shipment->label_path),
                'shipped_at' => $shipment->shipped_at?->toIso8601String(),
                'method' => $shipment->relationLoaded('method') ? ['id' => $shipment->method->id, 'code' => $shipment->method->code] : null,
                'pickup_point' => $shipment->relationLoaded('pickupPoint') ? $shipment->pickupPoint?->only(['external_id', 'name', 'address_line1', 'postal_code', 'city', 'country']) : null,
            ])->all()),
        ];
    }

    private function item(OrderItem $item, string $locale): array
    {
        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'category_id' => $item->category_id,
            'product' => [
                'name' => $item->localized('product_name', $locale),
                'slug' => $item->product_slug,
                'sku' => $item->product_sku,
                'image' => $item->image_url ? [
                    'url' => $item->image_url,
                    'alt_text' => $this->localizedArray($item->image_alt_text, $locale),
                ] : null,
            ],
            'variant' => $item->variant_name ? [
                'name' => $item->localized('variant_name', $locale),
                'sku' => $item->variant_sku,
            ] : null,
            'category' => $item->category_name ? [
                'name' => $this->localizedArray($item->category_name, $locale),
                'slug' => $item->category_slug,
            ] : null,
            'weight_grams' => $item->weight_grams,
            'quantity' => $item->quantity,
            'unit_price_cents' => $item->unit_price_cents,
            'formatted_unit_price' => MoneyFormatter::format($item->unit_price_cents, $item->currency, $locale),
            'line_total_cents' => $item->line_total_cents,
            'formatted_line_total' => MoneyFormatter::format($item->line_total_cents, $item->currency, $locale),
            'currency' => $item->currency,
        ];
    }

    private function address(OrderAddress $address): array
    {
        return [
            'id' => $address->id,
            'user_address_id' => $address->user_address_id,
            'type' => $address->type,
            'label' => $address->label,
            'recipient_name' => $address->recipient_name,
            'company' => $address->company,
            'street_line_1' => $address->street_line_1,
            'street_line_2' => $address->street_line_2,
            'postal_code' => $address->postal_code,
            'city' => $address->city,
            'region' => $address->region,
            'country_code' => $address->country_code,
            'phone' => $address->phone,
        ];
    }

    private function localizedArray(mixed $value, string $locale): ?string
    {
        if (! is_array($value)) {
            return $value;
        }

        return $value[$locale] ?? $value['fr'] ?? $value['en'] ?? reset($value) ?: null;
    }
}
