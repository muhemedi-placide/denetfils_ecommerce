<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\PickupPoint;
use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;
use App\Services\Shipping\ShippingManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class ShippingController extends Controller
{
    public function methods(Request $request, ShippingManager $shipping): JsonResponse
    {
        $data = $request->validate(['cart_token' => ['required', 'string', 'max:64'], 'shipping_address_id' => ['required', 'integer'], 'locale' => ['sometimes', Rule::in(['fr', 'en'])]]);
        $address = $request->user()->addresses()->findOrFail($data['shipping_address_id']);
        $cart = Cart::query()->where('cart_token', $data['cart_token'])->firstOrFail();
        return response()->json(['data' => $shipping->quotes($cart, $address->country_code, $data['locale'] ?? 'fr')]);
    }

    public function pickupPoints(Request $request, ShippingManager $shipping): JsonResponse
    {
        $data = $request->validate([
            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'], 'shipping_address_id' => ['required', 'integer'],
            'cart_token' => ['required', 'string', 'max:64'], 'postal_code' => ['nullable', 'string', 'max:32'], 'city' => ['nullable', 'string', 'max:120'],
            'radius_km' => ['nullable', 'integer', 'between:1,100'], 'limit' => ['nullable', 'integer', 'between:1,30'],
        ]);
        $address = $request->user()->addresses()->findOrFail($data['shipping_address_id']);
        $cart = Cart::query()->where('cart_token', $data['cart_token'])->firstOrFail();
        $method = ShippingMethod::query()->with('carrier')->findOrFail($data['shipping_method_id']);
        $automatic = blank($data['postal_code'] ?? null) && blank($data['city'] ?? null);
        $postalCode = $data['postal_code'] ?? $address->postal_code;
        $city = $data['city'] ?? $address->city;
        $radius = $data['radius_km'] ?? 20;
        $points = $shipping->searchPickupPoints($method, [
            'country' => $address->country_code, 'postal_code' => $postalCode, 'city' => $city,
            'weight_grams' => $shipping->weight($cart), 'radius_km' => $radius, 'limit' => $data['limit'] ?? 15,
        ]);
        $nearest = collect($points)->first(fn (array $point) => isset($point['latitude'], $point['longitude']));

        return response()->json(['data' => [
            'source' => $method->carrier->provider,
            'search' => [
                'mode' => $automatic ? 'automatic_address' : 'manual',
                'country' => $address->country_code, 'postal_code' => $postalCode, 'city' => $city, 'radius_km' => $radius,
            ],
            'center' => $nearest ? ['latitude' => $nearest['latitude'], 'longitude' => $nearest['longitude'], 'zoom' => 13] : null,
            'points' => $points,
        ]]);
    }

    public function select(Request $request, ShippingManager $shipping): JsonResponse
    {
        $data = $request->validate([
            'cart_token' => ['required', 'string', 'max:64'], 'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],
            'shipping_address_id' => ['required', 'integer'], 'pickup_point_id' => ['nullable', 'integer', 'exists:pickup_points,id'], 'locale' => ['sometimes', Rule::in(['fr', 'en'])],
        ]);
        $address = $request->user()->addresses()->findOrFail($data['shipping_address_id']);
        $cart = Cart::query()->where('cart_token', $data['cart_token'])->firstOrFail();
        $method = ShippingMethod::query()->with('carrier')->findOrFail($data['shipping_method_id']);
        $point = isset($data['pickup_point_id']) ? PickupPoint::query()->findOrFail($data['pickup_point_id']) : null;
        $selection = $shipping->select($cart, $method, $address->country_code, $point, $address->only(['postal_code', 'city', 'country_code']), $data['locale'] ?? 'fr');
        return response()->json(['data' => $selection->load(['method.carrier', 'pickupPoint'])]);
    }

    public function tracking(Request $request, ShippingManager $shipping): JsonResponse
    {
        $data = $request->validate([
            'tracking_number' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9._-]+$/'],
            'carrier_code' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', Rule::in(['fr', 'en'])],
        ]);

        $carrier = ShippingCarrier::query()
            ->where('provider', 'mondial_relay')
            ->where('is_enabled', true)
            ->where('status', 'active')
            ->when($data['carrier_code'] ?? null, fn ($query, string $code) => $query->where('code', $code))
            ->orderBy('sort_order')
            ->first();

        abort_unless($carrier, 404, 'No active Mondial Relay carrier is configured.');

        $provider = $shipping->provider($carrier->provider);
        abort_unless(method_exists($provider, 'trackShipmentNumber'), 422, 'Tracking is not supported for this carrier.');

        try {
            $payload = $provider->trackShipmentNumber($carrier, $data['tracking_number'], ($data['locale'] ?? 'fr') === 'en' ? 'EN' : 'FR');
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => 'Tracking lookup failed.',
                'errors' => ['tracking_number' => [$exception->getMessage()]],
            ], 422);
        }

        return response()->json(['data' => [
            'source' => $carrier->provider,
            'carrier_code' => $carrier->code,
            'tracking_number' => $data['tracking_number'],
            ...$this->trackingPayload($payload),
        ]]);
    }

    public function pickupPointDetail(Request $request, ShippingManager $shipping): JsonResponse
    {
        $data = $request->validate([
            'carrier_code' => ['nullable', 'string', 'max:64'],
            'country' => ['nullable', 'string', 'size:2'],
            'number' => ['required', 'string', 'max:20'],
            'include_hours' => ['nullable', 'boolean'],
        ]);
        $carrier = $this->activeMondialRelayCarrier($data['carrier_code'] ?? null);
        $provider = $shipping->provider($carrier->provider);
        abort_unless(method_exists($provider, 'pickupPointDetail'), 422, 'Pickup point detail is not supported for this carrier.');

        $country = strtoupper($data['country'] ?? 'FR');
        $detail = $provider->pickupPointDetail($carrier, $data['number'], $country);
        $hours = $request->boolean('include_hours') && method_exists($provider, 'pickupPointHours')
            ? $provider->pickupPointHours($carrier, $data['number'], $country)
            : null;

        return response()->json(['data' => [
            'source' => $carrier->provider,
            'carrier_code' => $carrier->code,
            'country' => $country,
            'number' => $data['number'],
            'detail' => $detail,
            'hours' => $hours,
        ]]);
    }

    public function postalCodes(Request $request, ShippingManager $shipping): JsonResponse
    {
        $data = $request->validate([
            'carrier_code' => ['nullable', 'string', 'max:64'],
            'country' => ['nullable', 'string', 'size:2'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'city' => ['nullable', 'string', 'max:120'],
            'limit' => ['nullable', 'integer', 'between:1,30'],
        ]);
        $carrier = $this->activeMondialRelayCarrier($data['carrier_code'] ?? null);
        $provider = $shipping->provider($carrier->provider);
        abort_unless(method_exists($provider, 'searchPostalCodes'), 422, 'Postal code search is not supported for this carrier.');

        return response()->json(['data' => [
            'source' => $carrier->provider,
            'carrier_code' => $carrier->code,
            'result' => $provider->searchPostalCodes(
                $carrier,
                strtoupper($data['country'] ?? 'FR'),
                (string) ($data['postal_code'] ?? ''),
                (string) ($data['city'] ?? ''),
                (int) ($data['limit'] ?? 10),
            ),
        ]]);
    }

    private function activeMondialRelayCarrier(?string $code = null): ShippingCarrier
    {
        $carrier = ShippingCarrier::query()
            ->where('provider', 'mondial_relay')
            ->where('is_enabled', true)
            ->where('status', 'active')
            ->when($code, fn ($query, string $code) => $query->where('code', $code))
            ->orderBy('sort_order')
            ->first();

        abort_unless($carrier, 404, 'No active Mondial Relay carrier is configured.');

        return $carrier;
    }

    private function trackingPayload(array $payload): array
    {
        $rows = [];
        foreach ($payload as $key => $value) {
            if (! is_string($key) || ! preg_match('/^(Libelle|Date|Heure|Emplacement)(\d+)$/', $key, $matches)) {
                continue;
            }

            $rows[$matches[2]][$matches[1]] = $value;
        }

        ksort($rows, SORT_NATURAL);
        $events = collect($rows)
            ->map(fn (array $row) => [
                'label' => $this->scalar($row['Libelle'] ?? null),
                'date' => $this->scalar($row['Date'] ?? null),
                'time' => $this->scalar($row['Heure'] ?? null),
                'location' => $this->scalar($row['Emplacement'] ?? null),
            ])
            ->filter(fn (array $event) => filled($event['label']) || filled($event['date']))
            ->values()
            ->all();

        return [
            'status_code' => $this->scalar($payload['STAT'] ?? $payload['Stat'] ?? null),
            'status_label' => $events[0]['label'] ?? $this->scalar($payload['Libelle'] ?? $payload['Libelle01'] ?? null),
            'delivered' => str_contains(mb_strtolower((string) ($events[0]['label'] ?? '')), 'livr'),
            'events' => $events,
            'raw' => $payload,
        ];
    }

    private function scalar(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
