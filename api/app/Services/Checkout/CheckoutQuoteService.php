<?php

namespace App\Services\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\SupportedCountry;
use App\Models\User;
use App\Models\UserAddress;
use App\Support\MoneyFormatter;
use Illuminate\Validation\ValidationException;

class CheckoutQuoteService
{
    public function quoteForUser(User $user, array $data): array
    {
        $locale = $this->locale($data['locale'] ?? $user->preferred_locale ?? 'fr');
        $cart = $this->cart((string) $data['cart_token']);
        $country = $this->destinationCountry($user, $data);
        $deliveryMethod = $data['delivery_method'] ?? 'standard';
        $carrier = $data['carrier'] ?? null;

        $cart->loadMissing(['items.product.category', 'items.product.images', 'items.variant']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart_token' => 'The cart is empty.',
            ]);
        }

        $this->assertOrderableItems($cart);

        $productTaxRate = $this->taxRate($country, true);
        $shippingTaxRate = $this->taxRate($country, false);
        $shippingCents = $this->shippingCents($cart, $country, $deliveryMethod);
        $taxBreakdown = $this->taxBreakdown($cart, $country, $productTaxRate, $shippingCents, $shippingTaxRate, $locale);
        $taxCents = collect($taxBreakdown)->sum('tax_cents');
        $discountCents = 0;
        $totalCents = max(0, $cart->subtotal_cents + $shippingCents + $taxCents - $discountCents);

        return [
            'cart_token' => $cart->cart_token,
            'currency' => $cart->currency,
            'destination_country' => [
                'code' => $country->code,
                'name' => $country->localized('name', $locale),
                'currency' => $country->currency,
                'is_eu' => $country->is_eu,
            ],
            'delivery_method' => $deliveryMethod,
            'carrier' => $carrier,
            'subtotal_cents' => $cart->subtotal_cents,
            'formatted_subtotal' => MoneyFormatter::format($cart->subtotal_cents, $cart->currency, $locale),
            'tax_cents' => $taxCents,
            'formatted_tax' => MoneyFormatter::format($taxCents, $cart->currency, $locale),
            'shipping_cents' => $shippingCents,
            'formatted_shipping' => MoneyFormatter::format($shippingCents, $cart->currency, $locale),
            'discount_cents' => $discountCents,
            'formatted_discount' => MoneyFormatter::format($discountCents, $cart->currency, $locale),
            'total_cents' => $totalCents,
            'formatted_total' => MoneyFormatter::format($totalCents, $cart->currency, $locale),
            'total_weight_grams' => $this->totalWeight($cart),
            'free_shipping_threshold_cents' => $this->freeShippingThreshold($cart),
            'formatted_free_shipping_threshold' => MoneyFormatter::format($this->freeShippingThreshold($cart), $cart->currency, $locale),
            'tax_breakdown' => $taxBreakdown,
        ];
    }

    public function cart(string $cartToken): Cart
    {
        $cart = Cart::query()
            ->where('cart_token', $cartToken)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $cart) {
            throw ValidationException::withMessages([
                'cart_token' => 'The selected cart is invalid or expired.',
            ]);
        }

        return $cart;
    }

    public function destinationCountry(User $user, array $data): SupportedCountry
    {
        $countryCode = null;

        if (! empty($data['shipping_address_id'])) {
            $address = $this->address($user, (int) $data['shipping_address_id'], 'shipping_address_id');
            $countryCode = $address->country_code;
        }

        $countryCode = strtoupper((string) ($countryCode ?: ($data['country_code'] ?? '')));

        $country = SupportedCountry::query()
            ->where('code', $countryCode)
            ->where('is_active', true)
            ->first();

        if (! $country) {
            throw ValidationException::withMessages([
                'country_code' => 'The selected country is not supported.',
            ]);
        }

        return $country;
    }

    public function address(User $user, int $addressId, string $field): UserAddress
    {
        $address = $user->addresses()->whereKey($addressId)->first();

        if (! $address) {
            throw ValidationException::withMessages([
                $field => 'The selected address is invalid.',
            ]);
        }

        return $address;
    }

    private function assertOrderableItems(Cart $cart): void
    {
        $cart->items->each(function (CartItem $item) {
            $product = $item->product;

            if (! $product || ! $product->is_active || ! $product->category?->is_active) {
                throw ValidationException::withMessages([
                    'cart_token' => 'The cart contains a product that is no longer available.',
                ]);
            }

            $variant = $item->variant;

            if ($item->product_variant_id && (! $variant || ! $variant->is_active)) {
                throw ValidationException::withMessages([
                    'cart_token' => 'The cart contains a variant that is no longer available.',
                ]);
            }

            $available = $variant?->stock_quantity ?? $product->stock_quantity;

            if ($item->quantity > $available) {
                throw ValidationException::withMessages([
                    'cart_token' => "Insufficient stock for {$product->sku}.",
                ]);
            }

            if ($product->max_order_quantity && $item->quantity > $product->max_order_quantity) {
                throw ValidationException::withMessages([
                    'cart_token' => "Maximum quantity exceeded for {$product->sku}.",
                ]);
            }
        });
    }

    private function taxBreakdown(
        Cart $cart,
        SupportedCountry $country,
        float $productTaxRate,
        int $shippingCents,
        float $shippingTaxRate,
        string $locale
    ): array {
        $lines = $cart->items
            ->map(function (CartItem $item) use ($cart, $country, $productTaxRate, $locale) {
                $taxCents = $this->taxCents($item->line_total_cents, $productTaxRate);

                return [
                    'type' => 'product',
                    'label' => $item->product?->localized('name', $locale),
                    'country_code' => $country->code,
                    'rate_percent' => $productTaxRate,
                    'taxable_cents' => $item->line_total_cents,
                    'formatted_taxable' => MoneyFormatter::format($item->line_total_cents, $cart->currency, $locale),
                    'tax_cents' => $taxCents,
                    'formatted_tax' => MoneyFormatter::format($taxCents, $cart->currency, $locale),
                ];
            });

        if ($shippingCents > 0) {
            $shippingTaxCents = $this->taxCents($shippingCents, $shippingTaxRate);

            $lines->push([
                'type' => 'shipping',
                'label' => 'Shipping',
                'country_code' => $country->code,
                'rate_percent' => $shippingTaxRate,
                'taxable_cents' => $shippingCents,
                'formatted_taxable' => MoneyFormatter::format($shippingCents, $cart->currency, $locale),
                'tax_cents' => $shippingTaxCents,
                'formatted_tax' => MoneyFormatter::format($shippingTaxCents, $cart->currency, $locale),
            ]);
        }

        return $lines->values()->all();
    }

    private function taxRate(SupportedCountry $country, bool $food): float
    {
        if (! $country->is_eu) {
            return 0.0;
        }

        $rate = $food
            ? ($country->food_vat_rate_percent ?? $country->standard_vat_rate_percent)
            : $country->standard_vat_rate_percent;

        return (float) $rate;
    }

    private function taxCents(int $amountCents, float $ratePercent): int
    {
        return (int) round($amountCents * ($ratePercent / 100));
    }

    private function shippingCents(Cart $cart, SupportedCountry $country, string $deliveryMethod): int
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
            ->map(function (CartItem $item) {
                $profile = $item->product?->shipping_profile;

                return is_array($profile) ? ($profile['free_shipping_threshold_cents'] ?? null) : null;
            })
            ->filter(fn (mixed $value) => is_numeric($value))
            ->map(fn (mixed $value) => (int) $value);

        return $thresholds->isNotEmpty() ? $thresholds->min() : 6900;
    }

    private function totalWeight(Cart $cart): int
    {
        return (int) $cart->items->sum(fn (CartItem $item) => ((int) ($item->product?->weight_grams ?? 0)) * $item->quantity);
    }

    private function locale(string $locale): string
    {
        return in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
    }
}
