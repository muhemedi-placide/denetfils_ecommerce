<?php

namespace App\Livewire\Shop;

use Livewire\Component;

class HeaderSearch extends Component
{
    public string $locale = 'fr';

    public string $q = '';

    public string $inputId = 'global-search';

    public string $formClass = '';

    public bool $onCatalogPage = false;

    public function mount(string $locale, string $inputId, string $formClass = '', bool $onCatalogPage = false): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->inputId = $inputId;
        $this->formClass = $formClass;
        $this->onCatalogPage = $onCatalogPage;
    }

    public function search()
    {
        $query = trim($this->q);

        if ($this->onCatalogPage) {
            $this->dispatch('catalog:search', q: $query)->to(ProductCatalog::class);
            $this->dispatch('scroll-to-products');

            return null;
        }

        return $this->redirectRoute('home.localized', [
            'locale' => $this->locale,
            'q' => $query,
        ], navigate: true);
    }

    public function render()
    {
        return view('livewire.shop.header-search');
    }
}
