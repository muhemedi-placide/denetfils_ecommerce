<?php

namespace App\Livewire\Shop;

use Livewire\Component;

class ProductPurchasePanel extends Component
{
    public string $locale = 'fr';

    public array $product = [];

    public int|string|null $variantId = null;

    public function mount(string $locale, array $product): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->product = $product;
        $this->variantId = $product['variants'][0]['id'] ?? null;
    }

    public function addToCart(): void
    {
        $this->dispatch(
            'cart:add',
            productId: (int) $this->product['id'],
            variantId: $this->variantId ?: null
        )->to(CartManager::class);
    }

    public function render()
    {
        return view('livewire.shop.product-purchase-panel', [
            'ratingAverage' => (float) data_get($this->product, 'commerce.rating.average', 0),
            'ratingCount' => (int) data_get($this->product, 'commerce.rating.count', 0),
            'isAvailable' => (bool) data_get($this->product, 'commerce.is_available', ((int) ($this->product['stock_quantity'] ?? 0)) > 0),
            'shipping' => data_get($this->product, 'commerce.shipping', []),
        ]);
    }
}
