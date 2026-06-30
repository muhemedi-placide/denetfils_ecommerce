<?php

namespace App\Livewire\Shop;

use App\Livewire\Shop\Concerns\InteractsWithCart;
use Livewire\Attributes\On;
use Livewire\Component;

class CartPage extends Component
{
    use InteractsWithCart;

    public string $locale = 'fr';

    public array $recommendedProducts = [];
    public string $countryCode = 'FR';
    public array $estimate = [];
    public ?string $estimateError = null;

    public function mount(string $locale, array $recommendedProducts = [], string $countryCode = 'FR'): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->recommendedProducts = $recommendedProducts;
        $this->countryCode = strtoupper($countryCode);
        $this->initializeCart();
    }

    public function restoreFromBrowser(?string $token): void
    {
        $this->restoreCart($token);
        $this->refreshEstimate();
    }

    #[On('cart:changed')]
    public function syncCart(?string $token = null): void
    {
        $this->restoreCart($token ?: $this->cartToken);
        $this->refreshEstimate();
    }

    public function incrementItem(int $itemId, int $quantity): void
    {
        $this->updateCartLine($itemId, $quantity + 1);
        $this->refreshEstimate();
    }

    public function decrementItem(int $itemId, int $quantity): void
    {
        $this->updateCartLine($itemId, $quantity - 1);
        $this->refreshEstimate();
    }

    public function updateItem(int $itemId, mixed $quantity): void
    {
        $this->updateCartLine($itemId, (int) $quantity);
        $this->refreshEstimate();
    }

    public function removeItem(int $itemId): void
    {
        $this->removeCartLine($itemId);
        $this->refreshEstimate();
    }

    public function addRecommended(int $productId): void
    {
        $this->addProductToCart($productId);
        $this->refreshEstimate();
    }

    public function render()
    {
        return view('livewire.shop.cart-page');
    }

    #[On('cart:cleared')]
    public function clearEstimate(): void
    {
        $this->estimate = [];
        $this->estimateError = null;
    }

    private function refreshEstimate(): void
    {
        $this->estimate = [];
        $this->estimateError = null;

        if (! $this->cartToken || empty($this->cartItems())) {
            return;
        }

        $response = app(\App\Services\ShopApiClient::class)
            ->estimateCart($this->cartToken, $this->locale, $this->countryCode);

        if (! $response['ok']) {
            $this->estimateError = $this->locale === 'fr'
                ? 'Estimation TVA et livraison indisponible.'
                : 'VAT and delivery estimate unavailable.';
            return;
        }

        $this->estimate = $response['data'];
    }
}
