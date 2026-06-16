<?php

namespace App\Livewire\Shop;

use App\Livewire\Shop\Concerns\InteractsWithCart;
use Livewire\Attributes\On;
use Livewire\Component;

class CartManager extends Component
{
    use InteractsWithCart;

    public string $locale = 'fr';

    public bool $isOpen = false;

    public function mount(string $locale): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->initializeCart();
    }

    #[On('cart:add')]
    public function addToCart(int $productId, int|string|null $variantId = null): void
    {
        $this->isOpen = true;
        $this->addProductToCart($productId, $variantId);
    }

    #[On('cart:open')]
    public function open(): void
    {
        $this->isOpen = true;
    }

    #[On('cart:changed')]
    public function syncCart(?string $token = null): void
    {
        $this->restoreCart($token ?: $this->cartToken);
    }

    public function close(): void
    {
        $this->isOpen = false;
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

    public function render()
    {
        return view('livewire.shop.cart-manager');
    }
}
