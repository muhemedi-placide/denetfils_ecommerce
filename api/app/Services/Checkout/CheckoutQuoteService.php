<?php

namespace App\Services\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ShippingMethod;
use App\Models\SupportedCountry;
use App\Models\Customer;
use App\Models\UserAddress;
use App\Services\Shipping\ShippingManager;
use App\Support\MoneyFormatter;
use Illuminate\Validation\ValidationException;

class CheckoutQuoteService
{
    public function __construct(private ShippingManager $shipping) {}

    public function quoteForUser(Customer $user, array $data): array
    {
        $locale = $this->locale($data['locale'] ?? $user->preferred_locale ?? 'fr');
        $country = $this->destinationCountry($user, $data);
        $cart = $this->cart((string) $data['cart_token']);
        $cart->forceFill([
            'customer_id' => $user->id,
            'last_activity_at' => now(),
        ])->save();

        return $this->quote(
            $cart,
            $country,
            $locale,
            $data,
            false,
            ! empty($data['shipping_address_id']) ? 'shipping_address' : 'country',
        );
    }

    public function estimate(string $cartToken, string $countryCode, string $locale = 'fr'): array
    {
        $cart = $this->cart($cartToken);
        $locale = $this->locale($locale);
        $countryCode = strtoupper($countryCode);
        $country = SupportedCountry::query()
            ->where('code', $countryCode)
            ->where('is_active', true)
            ->first();

        if (! $country) {
            $cart->loadMissing('items');

            return [
                'cart_token' => $cart->cart_token,
                'currency' => $cart->currency,
                'destination_country' => ['code' => $countryCode, 'name' => $countryCode],
                'country_source' => 'visitor_context',
                'is_estimate' => true,
                'is_supported' => false,
                'prices_include_tax' => true,
                'shipping_options' => [],
                'subtotal_cents' => $cart->subtotal_cents,
                'formatted_subtotal' => MoneyFormatter::format($cart->subtotal_cents, $cart->currency, $locale),
                'tax_cents' => null,
                'formatted_tax' => null,
                'shipping_cents' => null,
                'formatted_shipping' => null,
                'shipping_from_cents' => null,
                'formatted_shipping_from' => null,
                'discount_cents' => 0,
                'formatted_discount' => MoneyFormatter::format(0, $cart->currency, $locale),
                'total_cents' => null,
                'formatted_total' => null,
                'tax_breakdown' => [],
                'tax_summary' => [],
            ];
        }

        return $this->quote($cart, $country, $locale, [], true, 'visitor_context');
    }

    public function standardEstimate(string $cartToken, string $countryCode, string $locale = 'fr'): array
    {
        $country = SupportedCountry::query()
            ->where('code', strtoupper($countryCode))
            ->where('is_active', true)
            ->first();

        if (! $country) {
            throw ValidationException::withMessages(['country_code' => 'The selected country is not supported.']);
        }

        return $this->quote(
            $this->cart($cartToken),
            $country,
            $this->locale($locale),
            ['delivery_method' => 'standard'],
            false,
            'paypal_wallet',
        );
    }

    public function cart(string $cartToken): Cart
    {
        $cart = Cart::query()
            ->where('cart_token', $cartToken)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->first();

        if (! $cart) {
            throw ValidationException::withMessages(['cart_token' => 'The selected cart is invalid or expired.']);
        }

        return $cart;
    }

    public function destinationCountry(Customer $user, array $data): SupportedCountry
    {
        $countryCode = null;

        if (! empty($data['shipping_address_id'])) {
            $countryCode = $this->address($user, (int) $data['shipping_address_id'], 'shipping_address_id')->country_code;
        }

        $countryCode = strtoupper((string) ($countryCode ?: ($data['country_code'] ?? '')));
        $country = SupportedCountry::query()->where('code', $countryCode)->where('is_active', true)->first();

        if (! $country) {
            throw ValidationException::withMessages(['country_code' => 'The selected country is not supported.']);
        }

        return $country;
    }

    public function address(Customer $user, int $addressId, string $field): UserAddress
    {
        $address = $user->addresses()->whereKey($addressId)->first();

        if (! $address) {
            throw ValidationException::withMessages([$field => 'The selected address is invalid.']);
        }

        return $address;
    }

    private function quote(
        Cart $cart,
        SupportedCountry $country,
        string $locale,
        array $data,
        bool $isEstimate,
        string $countrySource,
    ): array {
        $cart->loadMissing(['items.product.category', 'items.product.images', 'items.variant']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart_token' => 'The cart is empty.']);
        }

        $this->assertOrderableItems($cart);

        $shippingOptions = $this->shipping->quotes($cart, $country->code, $locale);
        $selectedMethod = $isEstimate ? null : $this->selectedMethod($cart, $data);
        $shippingCents = $isEstimate
            ? collect($shippingOptions)->min('price_cents')
            : ($selectedMethod
                ? $this->shipping->quote($cart, $selectedMethod, $country->code, $locale)->priceCents
                : $this->legacyShippingCents($cart, $country, $data['delivery_method'] ?? 'standard'));
        $shippingCents = $shippingCents === null ? null : (int) $shippingCents;
        $taxBreakdown = $this->taxBreakdown($cart, $country, $shippingCents, $locale);
        $taxCents = (int) collect($taxBreakdown)->sum('tax_cents');
        $discountCents = 0;
        $totalCents = $shippingCents === null
            ? null
            : max(0, $cart->subtotal_cents + $shippingCents - $discountCents);

        return [
            'cart_token' => $cart->cart_token,
            'currency' => $cart->currency,
            'destination_country' => [
                'code' => $country->code,
                'name' => $country->localized('name', $locale),
                'currency' => $country->currency,
                'is_eu' => $country->is_eu,
            ],
            'country_source' => $countrySource,
            'is_estimate' => $isEstimate,
            'is_supported' => true,
            'prices_include_tax' => true,
            'delivery_method' => $data['delivery_method'] ?? 'standard',
            'carrier' => $data['carrier'] ?? null,
            'shipping_method_id' => $selectedMethod?->id,
            'shipping_method' => $selectedMethod ? [
                'id' => $selectedMethod->id,
                'code' => $selectedMethod->code,
                'requires_pickup_point' => $selectedMethod->requires_pickup_point,
            ] : null,
            'shipping_options' => $shippingOptions,
            'subtotal_cents' => $cart->subtotal_cents,
            'formatted_subtotal' => MoneyFormatter::format($cart->subtotal_cents, $cart->currency, $locale),
            'tax_cents' => $taxCents,
            'formatted_tax' => MoneyFormatter::format($taxCents, $cart->currency, $locale),
            'shipping_cents' => $shippingCents,
            'formatted_shipping' => $shippingCents === null ? null : MoneyFormatter::format($shippingCents, $cart->currency, $locale),
            'shipping_from_cents' => $shippingCents,
            'formatted_shipping_from' => $shippingCents === null ? null : MoneyFormatter::format($shippingCents, $cart->currency, $locale),
            'discount_cents' => $discountCents,
            'formatted_discount' => MoneyFormatter::format($discountCents, $cart->currency, $locale),
            'total_cents' => $totalCents,
            'formatted_total' => $totalCents === null ? null : MoneyFormatter::format($totalCents, $cart->currency, $locale),
            'total_weight_grams' => $this->totalWeight($cart),
            'free_shipping_threshold_cents' => $this->freeShippingThreshold($cart),
            'formatted_free_shipping_threshold' => MoneyFormatter::format($this->freeShippingThreshold($cart), $cart->currency, $locale),
            'tax_breakdown' => $taxBreakdown,
            'tax_summary' => collect($taxBreakdown)
                ->groupBy(fn (array $line) => $line['tax_class'].'|'.$line['rate_percent'])
                ->map(fn ($lines) => [
                    'tax_class' => $lines->first()['tax_class'],
                    'rate_percent' => $lines->first()['rate_percent'],
                    'taxable_cents' => $lines->sum('taxable_cents'),
                    'tax_cents' => $lines->sum('tax_cents'),
                ])
                ->values()
                ->all(),
        ];
    }

    private function taxBreakdown(Cart $cart, SupportedCountry $country, ?int $shippingCents, string $locale): array
    {
        $lines = $cart->items->map(function (CartItem $item) use ($cart, $country, $locale) {
            $taxClass = in_array($item->product?->tax_class, ['food', 'standard'], true)
                ? $item->product->tax_class
                : 'food';
            $rate = $this->taxRate($country, $taxClass);
            $taxCents = $this->includedTaxCents($item->line_total_cents, $rate);

            return [
                'type' => 'product',
                'tax_class' => $taxClass,
                'label' => $item->product?->localized('name', $locale),
                'country_code' => $country->code,
                'rate_percent' => $rate,
                'taxable_cents' => $item->line_total_cents,
                'formatted_taxable' => MoneyFormatter::format($item->line_total_cents, $cart->currency, $locale),
                'tax_cents' => $taxCents,
                'formatted_tax' => MoneyFormatter::format($taxCents, $cart->currency, $locale),
            ];
        });

        if ($shippingCents !== null && $shippingCents > 0) {
            $rate = $this->taxRate($country, 'standard');
            $taxCents = $this->includedTaxCents($shippingCents, $rate);
            $lines->push([
                'type' => 'shipping',
                'tax_class' => 'shipping',
                'label' => $locale === 'fr' ? 'Livraison' : 'Shipping',
                'country_code' => $country->code,
                'rate_percent' => $rate,
                'taxable_cents' => $shippingCents,
                'formatted_taxable' => MoneyFormatter::format($shippingCents, $cart->currency, $locale),
                'tax_cents' => $taxCents,
                'formatted_tax' => MoneyFormatter::format($taxCents, $cart->currency, $locale),
            ]);
        }

        return $lines->values()->all();
    }

    private function taxRate(SupportedCountry $country, string $taxClass): float
    {
        if (! $country->is_eu) {
            return 0.0;
        }

        return (float) ($taxClass === 'food'
            ? ($country->food_vat_rate_percent ?? $country->standard_vat_rate_percent)
            : $country->standard_vat_rate_percent);
    }

    private function includedTaxCents(int $grossCents, float $ratePercent): int
    {
        return $ratePercent <= 0 ? 0 : (int) round($grossCents * ($ratePercent / (100 + $ratePercent)));
    }

    private function assertOrderableItems(Cart $cart): void
    {
        $cart->items->each(function (CartItem $item) {
            $product = $item->product;
            if (! $product || ! $product->is_active || ! $product->category?->is_active) {
                throw ValidationException::withMessages(['cart_token' => 'The cart contains a product that is no longer available.']);
            }
            if ($item->product_variant_id && (! $item->variant || ! $item->variant->is_active)) {
                throw ValidationException::withMessages(['cart_token' => 'The cart contains a variant that is no longer available.']);
            }
            $available = $item->variant?->stock_quantity ?? $product->stock_quantity;
            if ($item->quantity > $available) {
                throw ValidationException::withMessages(['cart_token' => "Insufficient stock for {$product->sku}."]);
            }
            if ($product->max_order_quantity && $item->quantity > $product->max_order_quantity) {
                throw ValidationException::withMessages(['cart_token' => "Maximum quantity exceeded for {$product->sku}."]);
            }
        });
    }

    private function legacyShippingCents(Cart $cart, SupportedCountry $country, string $deliveryMethod): int
    {
        if ($cart->subtotal_cents >= $this->freeShippingThreshold($cart)) {
            return 0;
        }
        $zone = match (true) {
            $country->code === 'FR' => 'domestic',
            $country->is_eu => 'eu',
            default => 'non_eu',
        };
        $rates = [
            'domestic' => ['standard' => 590, 'relay' => 490],
            'eu' => ['standard' => 990, 'relay' => 790],
            'non_eu' => ['standard' => 1490, 'relay' => 1290],
        ];

        return $rates[$zone][$deliveryMethod] ?? $rates[$zone]['standard'];
    }

    private function freeShippingThreshold(Cart $cart): int
    {
        $thresholds = $cart->items
            ->map(fn (CartItem $item) => data_get($item->product?->shipping_profile, 'free_shipping_threshold_cents'))
            ->filter(fn (mixed $value) => is_numeric($value))
            ->map(fn (mixed $value) => (int) $value);

        return $thresholds->isNotEmpty() ? $thresholds->min() : 6900;
    }

    private function totalWeight(Cart $cart): int
    {
        return (int) $cart->items->sum(fn (CartItem $item) => ((int) ($item->product?->weight_grams ?? 0)) * $item->quantity);
    }

    private function selectedMethod(Cart $cart, array $data): ?ShippingMethod
    {
        if (! empty($data['shipping_method_id'])) {
            return ShippingMethod::query()->with('carrier')->find((int) $data['shipping_method_id']);
        }

        return $cart->shippingSelection()->with('method.carrier')->first()?->method;
    }

    private function locale(string $locale): string
    {
        return in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
    }
}
