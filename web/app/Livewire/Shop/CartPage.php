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

    public function mount(string $locale, array $recommendedProducts = []): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->recommendedProducts = $recommendedProducts;
        $this->initializeCart();
    }

    #[On('cart:changed')]
    public function syncCart(?string $token = null): void
    {
        $this->restoreCart($token ?: $this->cartToken);
    }

    public function incrementItem(int $itemId, int $quantity): void
    {
        $this->updateCartLine($itemId, $quantity + 1);
    }

    public function decrementItem(int $itemId, int $quantity): void
    {
        $this->updateCartLine($itemId, $quantity - 1);
    }

    public function updateItem(int $itemId, mixed $quantity): void
    {
        $this->updateCartLine($itemId, (int) $quantity);
    }

    public function removeItem(int $itemId): void
    {
        $this->removeCartLine($itemId);
    }

    public function addRecommended(int $productId): void
    {
        $this->addProductToCart($productId);
    }

    public function render()
    {
        return view('livewire.shop.cart-page');
    }
}
