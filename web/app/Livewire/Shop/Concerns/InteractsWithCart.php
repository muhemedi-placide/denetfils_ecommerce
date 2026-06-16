<?php

namespace App\Livewire\Shop\Concerns;

use App\Services\ShopApiClient;
use Livewire\Attributes\On;

trait InteractsWithCart
{
    public ?string $cartToken = null;

    public array $cart = [];

    public ?string $cartError = null;

    public bool $cartLoading = false;

    public bool $cartMutating = false;

    public function restoreFromBrowser(?string $token): void
    {
        $this->restoreCart($token);
    }

    #[On('cart:cleared')]
    public function clearSyncedCart(): void
    {
        $this->clearCartState(false);
    }

    public function itemCount(): int
    {
        return collect($this->cart['items'] ?? [])
            ->sum(fn (array $item) => (int) ($item['quantity'] ?? 0));
    }

    public function cartItems(): array
    {
        return $this->cart['items'] ?? [];
    }

    public function formattedTotal(): string
    {
        return (string) ($this->cart['formatted_total'] ?? __('home.cart.empty_total'));
    }

    protected function initializeCart(): void
    {
        $this->cart = $this->emptyCart();
    }

    protected function restoreCart(?string $token): void
    {
        $token = trim((string) $token);

        if ($token === '') {
            $this->cartToken = null;
            $this->cart = $this->emptyCart();
            $this->cartError = null;
            $this->dispatchCartBrowserState();

            return;
        }

        $this->cartLoading = true;
        $response = $this->cartApi()->cart($token, $this->locale);
        $this->cartLoading = false;

        if (! $response['ok']) {
            $this->cartToken = null;
            $this->cart = $this->emptyCart();
            $this->cartError = __('home.cart.expired');
            $this->dispatch('cart-token-cleared');
            $this->dispatchCartBrowserState();

            return;
        }

        $this->setCartFromResponse($response['data'], $token);
        $this->cartError = null;
        $this->dispatchCartBrowserState();
    }

    protected function addProductToCart(int $productId, int|string|null $variantId = null, int $quantity = 1): void
    {
        $token = $this->ensureCart();

        if (! $token) {
            return;
        }

        $payload = [
            'product_id' => $productId,
            'quantity' => max(1, $quantity),
        ];

        if ($variantId) {
            $payload['product_variant_id'] = (int) $variantId;
        }

        $this->cartMutating = true;
        $response = $this->cartApi()->addCartItem($token, $this->locale, $payload);
        $this->cartMutating = false;

        $this->handleCartMutationResponse($response, $token);
    }

    protected function updateCartLine(int $itemId, int $quantity): void
    {
        if (! $this->cartToken || $quantity < 1) {
            return;
        }

        $this->cartMutating = true;
        $response = $this->cartApi()->updateCartItem($this->cartToken, $this->locale, $itemId, [
            'quantity' => $quantity,
        ]);
        $this->cartMutating = false;

        $this->handleCartMutationResponse($response, $this->cartToken);
    }

    protected function removeCartLine(int $itemId): void
    {
        if (! $this->cartToken) {
            return;
        }

        $this->cartMutating = true;
        $response = $this->cartApi()->deleteCartItem($this->cartToken, $this->locale, $itemId);
        $this->cartMutating = false;

        $this->handleCartMutationResponse($response, $this->cartToken);
    }

    protected function ensureCart(): ?string
    {
        if ($this->cartToken) {
            return $this->cartToken;
        }

        $this->cartMutating = true;
        $response = $this->cartApi()->createCart($this->locale);
        $this->cartMutating = false;

        if (! $response['ok']) {
            $this->cartError = $response['message'] ?: __('home.cart.api_error');

            return null;
        }

        $this->setCartFromResponse($response['data']);
        $this->dispatchCartBrowserState();

        return $this->cartToken;
    }

    protected function handleCartMutationResponse(array $response, ?string $fallbackToken = null): void
    {
        if (! $response['ok']) {
            $this->cartError = $response['message'] ?: __('home.cart.api_error');
            $this->dispatchCartBrowserState();

            return;
        }

        $this->setCartFromResponse($response['data'], $fallbackToken);
        $this->cartError = null;
        $this->dispatch('cart:changed', token: $this->cartToken);
        $this->dispatchCartBrowserState();
    }

    protected function setCartFromResponse(array $cart, ?string $fallbackToken = null): void
    {
        $this->cart = array_replace($this->emptyCart(), $cart);
        $this->cartToken = $this->cart['cart_token'] ?? $fallbackToken;

        if ($this->cartToken) {
            $this->dispatch('cart-token-stored', token: $this->cartToken);
        }
    }

    protected function emptyCart(): array
    {
        return [
            'cart_token' => null,
            'subtotal_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 0,
            'formatted_total' => __('home.cart.empty_total'),
            'items' => [],
        ];
    }

    protected function clearCartState(bool $broadcast = true): void
    {
        $this->cartToken = null;
        $this->cart = $this->emptyCart();
        $this->cartError = null;
        $this->cartLoading = false;
        $this->cartMutating = false;

        $this->dispatch('cart-token-cleared');
        $this->dispatchCartBrowserState();

        if ($broadcast) {
            $this->dispatch('cart:cleared');
        }
    }

    protected function dispatchCartBrowserState(): void
    {
        $this->dispatch('cart-count-updated', count: $this->itemCount());
    }

    protected function cartApi(): ShopApiClient
    {
        return app(ShopApiClient::class);
    }
}
