<?php

namespace App\Services\Carts;

use App\Models\Cart;
use App\Models\CartRecoveryLink;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartRecoveryService
{
    public function issue(Cart $cart): array
    {
        $cart->loadMissing(['items', 'order']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'An empty cart cannot be shared.']);
        }

        if ($cart->order) {
            throw ValidationException::withMessages(['cart' => 'This cart has already been converted to an order.']);
        }

        if ($cart->expires_at?->isPast()) {
            throw ValidationException::withMessages(['cart' => 'This cart has expired.']);
        }

        $plainToken = Str::random(64);
        $expiresAt = now()->addDays(30);

        if ($cart->expires_at && $cart->expires_at->lessThan($expiresAt)) {
            $expiresAt = $cart->expires_at->copy();
        }

        $link = $cart->recoveryLinks()->create([
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
        ]);

        return [
            'token' => $plainToken,
            'expires_at' => $link->expires_at->toIso8601String(),
        ];
    }

    public function recover(string $plainToken): Cart
    {
        $link = CartRecoveryLink::query()
            ->with(['cart.items.product.images', 'cart.items.variant', 'cart.customer', 'cart.order'])
            ->where('token_hash', hash('sha256', $plainToken))
            ->where('expires_at', '>', now())
            ->first();

        $cart = $link?->cart;

        if (! $cart || $cart->expires_at?->isPast() || $cart->order) {
            throw ValidationException::withMessages(['recovery_token' => 'This recovery link is invalid or expired.']);
        }

        $link->forceFill([
            'last_used_at' => now(),
            'uses_count' => $link->uses_count + 1,
        ])->save();

        $cart->forceFill(['last_activity_at' => now()])->save();

        return $cart;
    }
}
