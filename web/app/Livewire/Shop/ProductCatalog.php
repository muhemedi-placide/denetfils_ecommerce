<?php

namespace App\Livewire\Shop;

use App\Services\ShopApiClient;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductCatalog extends Component
{
    public string $locale = 'fr';

    public array $categories = [];

    public array $products = [];

    public ?string $apiError = null;

    public bool $filtersOpen = false;

    public string $q = '';

    public string $category = '';

    public string $sort = 'default';

    public function mount(
        string $locale,
        array $categories,
        array $products,
        array $filters = [],
        ?string $apiError = null
    ): void {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->categories = $categories;
        $this->products = $products;
        $this->apiError = $apiError;
        $this->q = (string) ($filters['q'] ?? '');
        $this->category = (string) ($filters['category'] ?? '');
        $this->sort = $this->normalizeSort((string) ($filters['sort'] ?? 'default'));
        $this->filtersOpen = $this->hasActiveFilters();
    }

    public function applyFilters(): void
    {
        $this->loadProducts();
        $this->filtersOpen = $this->hasActiveFilters();
    }

    public function resetFilters(): void
    {
        $this->q = '';
        $this->category = '';
        $this->sort = 'default';
        $this->loadProducts();
        $this->filtersOpen = false;
    }

    public function addToCart(int $productId): void
    {
        $this->dispatch('cart:add', productId: $productId)->to(CartManager::class);
    }

    #[On('catalog:filter-category')]
    public function filterCategory(string $category): void
    {
        $this->category = $category;
        $this->filtersOpen = true;
        $this->loadProducts();
    }

    #[On('catalog:search')]
    public function searchFromHeader(string $q): void
    {
        $this->q = trim($q);
        $this->filtersOpen = true;
        $this->loadProducts();
    }

    public function render()
    {
        return view('livewire.shop.product-catalog', [
            'hasActiveFilters' => $this->hasActiveFilters(),
        ]);
    }

    private function loadProducts(): void
    {
        $this->sort = $this->normalizeSort($this->sort);

        $response = app(ShopApiClient::class)->products($this->locale, [
            'category' => $this->category,
            'q' => trim($this->q),
            'sort' => $this->sort,
        ]);

        $this->products = $response['data'];
        $this->apiError = $response['error'];
        $this->dispatch('catalog-updated');
    }

    private function hasActiveFilters(): bool
    {
        return trim($this->q) !== ''
            || $this->category !== ''
            || $this->sort !== 'default';
    }

    private function normalizeSort(string $sort): string
    {
        return in_array($sort, ['default', 'price_asc', 'price_desc', 'latest'], true) ? $sort : 'default';
    }
}
