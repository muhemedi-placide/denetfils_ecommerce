<?php

namespace App\Livewire\Shop;

use Livewire\Component;

class CartOpenButton extends Component
{
    public string $buttonClass = 'btn-primary';

    public function mount(string $buttonClass = 'btn-primary'): void
    {
        $this->buttonClass = $buttonClass;
    }

    public function open(): void
    {
        $this->dispatch('cart:open')->to(CartManager::class);
    }

    public function render()
    {
        return view('livewire.shop.cart-open-button');
    }
}
