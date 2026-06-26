<?php

namespace App\Services\Orders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\Checkout\CheckoutQuoteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderCreationService
{
    public function __construct(private CheckoutQuoteService $quotes)
    {
    }

    public function createFromCart(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $cart = $this->cart((string) $data['cart_token']);

            $existingOrder = Order::query()
                ->where('cart_id', $cart->id)
                ->first();

            if ($existingOrder && (int) $existingOrder->user_id === (int) $user->id) {
                return $existingOrder->load(['items', 'addresses', 'shipments.method', 'shipments.pickupPoint']);
            }

            if ($existingOrder) {
                throw ValidationException::withMessages([
                    'cart_token' => 'This cart has already been converted to an order.',
                ]);
            }

            $cart->loadMissing(['items.product.category', 'items.product.images', 'items.variant']);

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart_token' => 'The cart is empty.',
                ]);
            }

            $shippingAddress = $this->address($user, (int) $data['shipping_address_id'], 'shipping_address_id');
            $billingAddress = isset($data['billing_address_id'])
                ? $this->address($user, (int) $data['billing_address_id'], 'billing_address_id')
                : $shippingAddress;

            $this->assertOrderableItems($cart);
            $quote = $this->quotes->quoteForUser($user, $data);
            $selection = $cart->shippingSelection()->with(['method.carrier', 'pickupPoint'])->first();

            if (! empty($data['shipping_method_id']) && (! $selection || $selection->shipping_method_id !== (int) $data['shipping_method_id'])) {
                throw ValidationException::withMessages(['shipping_method_id' => 'Save the shipping selection before creating the order.']);
            }
            if ($selection?->method->requires_pickup_point && ! $selection->pickupPoint) {
                throw ValidationException::withMessages(['pickup_point_id' => 'A pickup point is required for the selected method.']);
            }

            $shippingCents = (int) $quote['shipping_cents'];
            $taxCents = (int) $quote['tax_cents'];
            $discountCents = (int) $quote['discount_cents'];
            $totalCents = (int) $quote['total_cents'];

            $order = Order::query()->create([
                'order_number' => $this->orderNumber(),
                'user_id' => $user->id,
                'cart_id' => $cart->id,
                'status' => 'pending_payment',
                'payment_status' => 'unpaid',
                'fulfillment_status' => 'unfulfilled',
                'currency' => $cart->currency,
                'subtotal_cents' => $cart->subtotal_cents,
                'tax_cents' => $taxCents,
                'shipping_cents' => $shippingCents,
                'discount_cents' => $discountCents,
                'total_cents' => $totalCents,
                'customer_email' => $user->email,
                'customer_name' => trim(($user->first_name ?: '') . ' ' . ($user->last_name ?: '')) ?: $user->name,
                'customer_phone' => $user->phone,
                'customer_locale' => $data['locale'] ?? $user->preferred_locale ?? 'fr',
                'customer_country_code' => $user->country_code,
                'delivery_method' => $selection?->method->delivery_type ?? ($data['delivery_method'] ?? null),
                'carrier' => $selection?->method->carrier->code ?? ($data['carrier'] ?? null),
                'metadata' => $selection ? [
                    'shipping_method_code' => $selection->method->code,
                    'pickup_point' => $selection->pickupPoint?->only(['external_id', 'type', 'country', 'name', 'address_line1', 'address_line2', 'postal_code', 'city']),
                ] : ($data['metadata'] ?? null),
                'placed_at' => now(),
            ]);

            $cart->items->each(fn (CartItem $item) => $this->createOrderItem($order, $item, $cart->currency));
            $this->createAddressSnapshot($order, $shippingAddress, 'shipping');
            $this->createAddressSnapshot($order, $billingAddress, 'billing');

            if ($selection) {
                $order->shipments()->create([
                    'shipping_carrier_id' => $selection->method->shipping_carrier_id,
                    'shipping_method_id' => $selection->shipping_method_id,
                    'pickup_point_id' => $selection->pickup_point_id,
                    'status' => 'pending',
                ]);
            }

            return $order->load(['items', 'addresses', 'shipments.method', 'shipments.pickupPoint']);
        });
    }

    private function cart(string $cartToken): Cart
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

    private function address(User $user, int $addressId, string $field): UserAddress
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

    private function createOrderItem(Order $order, CartItem $item, string $currency): void
    {
        /** @var Product $product */
        $product = $item->product;
        /** @var ProductVariant|null $variant */
        $variant = $item->variant;
        $category = $product->category;
        $image = $product->images->first();

        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'category_id' => $category?->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'product_sku' => $product->sku,
            'variant_name' => $variant?->name,
            'variant_sku' => $variant?->sku,
            'category_slug' => $category?->slug,
            'category_name' => $category?->name,
            'image_url' => $image?->url,
            'image_alt_text' => $image?->alt_text,
            'weight_grams' => $product->weight_grams,
            'quantity' => $item->quantity,
            'unit_price_cents' => $item->unit_price_cents,
            'line_total_cents' => $item->line_total_cents,
            'currency' => $currency,
        ]);
    }

    private function createAddressSnapshot(Order $order, UserAddress $address, string $type): void
    {
        $order->addresses()->create([
            'user_address_id' => $address->id,
            'type' => $type,
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
        ]);
    }

    private function orderNumber(): string
    {
        do {
            $number = 'DF-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
