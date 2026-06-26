<?php

namespace App\Services\Shipping;

use App\Models\Cart;
use App\Models\CartShippingSelection;
use App\Models\PickupPoint;
use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;
use App\Services\Shipping\Contracts\CarrierProviderInterface;
use App\Services\Shipping\DTO\PickupPointData;
use App\Services\Shipping\DTO\ShippingQuoteData;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ShippingManager
{
    /** @param iterable<CarrierProviderInterface> $providers */
    public function __construct(private iterable $providers) {}

    public function activeCarriers(): Collection
    {
        return ShippingCarrier::query()->where('is_enabled', true)->where('status', 'active')->orderBy('sort_order')->get();
    }

    public function quotes(Cart $cart, string $country, string $locale = 'fr'): array
    {
        $country = strtoupper($country);
        $weight = $this->weight($cart);
        $methods = ShippingMethod::query()->with(['carrier', 'rates.zone'])
            ->where('is_active', true)->where(fn ($query) => $query->whereNull('max_weight_grams')->orWhere('max_weight_grams', '>=', $weight))
            ->whereHas('carrier', fn ($query) => $query->where('is_enabled', true)->where('status', 'active'))
            ->orderBy('sort_order')->get();

        return $methods->filter(fn (ShippingMethod $method) => in_array($country, $method->carrier->countries ?? [], true))
            ->map(function (ShippingMethod $method) use ($cart, $country, $weight, $locale) {
                $rate = $method->rates->first(fn ($rate) => $rate->is_active && $weight >= $rate->min_weight_grams && $weight <= $rate->max_weight_grams && in_array($country, $rate->zone->countries ?? [], true));
                if (! $rate) return null;
                $price = $cart->subtotal_cents >= $this->freeShippingThreshold($cart) ? 0 : $rate->price_cents;
                return (new ShippingQuoteData($method->id, $method->code, $method->carrier->code, $method->localized('name', $locale) ?? $method->code, $method->delivery_type, $price, $rate->currency, $method->requires_pickup_point, $method->requires_phone, $method->min_delivery_days, $method->max_delivery_days))->toArray();
            })->filter()->values()->all();
    }

    public function quote(Cart $cart, ShippingMethod $method, string $country, string $locale = 'fr'): ShippingQuoteData
    {
        $quote = collect($this->quotes($cart, $country, $locale))->firstWhere('method_id', $method->id);
        if (! $quote) throw ValidationException::withMessages(['shipping_method_id' => 'The selected shipping method is unavailable for this cart.']);
        return new ShippingQuoteData(
            $quote['method_id'], $quote['method_code'], $quote['carrier_code'], $quote['name'],
            $quote['delivery_type'], $quote['price_cents'], $quote['currency'],
            $quote['requires_pickup_point'], $quote['requires_phone'],
            $quote['min_delivery_days'], $quote['max_delivery_days'],
        );
    }

    public function searchPickupPoints(ShippingMethod $method, array $criteria): array
    {
        if (! $method->requires_pickup_point) throw ValidationException::withMessages(['shipping_method_id' => 'This method does not use a pickup point.']);
        $provider = $this->provider($method->carrier->provider);
        return collect($provider->searchPickupPoints($method->carrier, [...$criteria, 'service_code' => $method->service_code]))
            ->sortBy(fn (PickupPointData $point) => $point->distanceMeters ?? PHP_INT_MAX)
            ->values()
            ->map(function (PickupPointData $point) {
                $model = PickupPoint::query()->updateOrCreate(
                    ['carrier_code' => $point->carrierCode, 'external_id' => $point->externalId],
                    ['type' => $point->type, 'country' => $point->country, 'name' => $point->name, 'address_line1' => $point->addressLine1, 'address_line2' => $point->addressLine2, 'postal_code' => $point->postalCode, 'city' => $point->city, 'latitude' => $point->latitude, 'longitude' => $point->longitude, 'opening_hours' => $point->openingHours, 'raw_payload' => $point->rawPayload, 'last_seen_at' => now()]
                );
                return ['id' => $model->id, ...$point->toArray()];
            })->all();
    }

    public function select(Cart $cart, ShippingMethod $method, string $country, ?PickupPoint $pickupPoint, array $address = [], string $locale = 'fr'): CartShippingSelection
    {
        $quote = $this->quote($cart, $method, $country, $locale);
        if ($method->requires_pickup_point && ! $pickupPoint) throw ValidationException::withMessages(['pickup_point_id' => 'A pickup point is required.']);
        if ($pickupPoint && ($pickupPoint->carrier_code !== $method->carrier->code || $pickupPoint->country !== strtoupper($country))) throw ValidationException::withMessages(['pickup_point_id' => 'The selected pickup point is incompatible with this method.']);

        return CartShippingSelection::query()->updateOrCreate(['cart_id' => $cart->id], [
            'shipping_method_id' => $method->id, 'pickup_point_id' => $pickupPoint?->id, 'shipping_price_cents' => $quote->priceCents,
            'currency' => $quote->currency, 'country' => strtoupper($country), 'postal_code' => $address['postal_code'] ?? null,
            'city' => $address['city'] ?? null, 'address_snapshot' => $address,
        ]);
    }

    public function provider(string $code): CarrierProviderInterface
    {
        foreach ($this->providers as $provider) if ($provider->code() === $code) return $provider;
        throw new \RuntimeException("Shipping provider [{$code}] is not registered.");
    }

    public function weight(Cart $cart): int
    {
        $cart->loadMissing('items.product');
        return (int) $cart->items->sum(fn ($item) => ((int) ($item->product?->weight_grams ?? 0)) * $item->quantity);
    }

    private function freeShippingThreshold(Cart $cart): int
    {
        $thresholds = $cart->items->map(fn ($item) => data_get($item->product?->shipping_profile, 'free_shipping_threshold_cents'))->filter(fn ($value) => is_numeric($value));
        return $thresholds->isNotEmpty() ? (int) $thresholds->min() : 6900;
    }
}
