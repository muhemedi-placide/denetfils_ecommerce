<?php

namespace App\Livewire\Shop;

use Livewire\Component;

class CategoryGrid extends Component
{
    public string $locale = 'fr';

    public array $categories = [];

    public function mount(string $locale, array $categories): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->categories = $categories;
    }

    public function selectCategory(string $category): void
    {
        $this->dispatch('catalog:filter-category', category: $category)->to(ProductCatalog::class);
        $this->dispatch('scroll-to-products');
    }

    public function render()
    {
        return view('livewire.shop.category-grid');
    }
}
