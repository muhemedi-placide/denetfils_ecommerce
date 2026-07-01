<?php

namespace App\Services\Catalog;

use App\Models\Product;

class ProductHealthService
{
    public function analyze(Product $product, string $locale = 'fr'): array
    {
        $product->loadMissing(['category', 'images', 'iconImage']);
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $primaryImage = $product->images->first();

        $checks = [
            $this->check('name_fr', 'content', filled(data_get($product->name, 'fr')), 'Nom français', 'French name'),
            $this->check('name_en', 'content', filled(data_get($product->name, 'en')), 'Nom anglais', 'English name'),
            $this->check('short_description_fr', 'content', filled(data_get($product->short_description, 'fr')), 'Description courte FR', 'French short description'),
            $this->check('short_description_en', 'content', filled(data_get($product->short_description, 'en')), 'Description courte EN', 'English short description'),
            $this->check('description_fr', 'content', filled(data_get($product->description, 'fr')), 'Description complète FR', 'French full description'),
            $this->check('description_en', 'content', filled(data_get($product->description, 'en')), 'Description complète EN', 'English full description'),
            $this->check('origin_fr', 'content', filled(data_get($product->origin, 'fr')), 'Origine FR', 'French origin'),
            $this->check('origin_en', 'content', filled(data_get($product->origin, 'en')), 'Origine EN', 'English origin'),
            $this->check('category', 'classification', $product->category && $product->category->slug !== 'non-classe', 'Catégorie précise', 'Specific category'),
            $this->check('brand', 'classification', filled($product->brand), 'Marque', 'Brand'),
            $this->check('barcode', 'commerce', filled($product->barcode), 'Code-barres / EAN', 'Barcode / EAN'),
            $this->check('supplier_reference', 'commerce', filled($product->supplier_reference), 'Référence fournisseur', 'Supplier reference'),
            $this->check('purchase_price', 'commerce', $product->purchase_price_cents !== null, 'Prix d’achat', 'Purchase price'),
            $this->check('sale_price', 'commerce', $product->price_cents > 0, 'Prix de vente TTC', 'Sale price incl. VAT', true),
            $this->check('stock', 'commerce', $product->stock_quantity > 0, 'Stock disponible', 'Available stock', true, 'out_of_stock'),
            $this->check('max_order_quantity', 'commerce', $product->max_order_quantity !== null, 'Maximum par commande', 'Maximum per order'),
            $this->check('weight', 'logistics', $product->weight_grams !== null, 'Poids', 'Weight'),
            $this->check('unit_label', 'logistics', filled($product->unit_label), 'Unité de vente', 'Sales unit'),
            $this->check('primary_image', 'media', $primaryImage !== null, 'Image principale', 'Primary image', true),
            $this->check('gallery', 'media', $product->images->count() >= 2, 'Deux images de galerie minimum', 'At least two gallery images'),
            $this->check('icon', 'media', $product->iconImage !== null, 'Icône produit', 'Product icon'),
            $this->check('image_alt_fr', 'media', filled(data_get($primaryImage?->alt_text, 'fr')), 'Texte alternatif image FR', 'French image alt text'),
            $this->check('image_alt_en', 'media', filled(data_get($primaryImage?->alt_text, 'en')), 'Texte alternatif image EN', 'English image alt text'),
            $this->check('seo_title_fr', 'seo', filled(data_get($product->seo_title, 'fr')), 'Titre SEO FR', 'French SEO title'),
            $this->check('seo_title_en', 'seo', filled(data_get($product->seo_title, 'en')), 'Titre SEO EN', 'English SEO title'),
            $this->check('seo_description_fr', 'seo', filled(data_get($product->seo_description, 'fr')), 'Description SEO FR', 'French SEO description'),
            $this->check('seo_description_en', 'seo', filled(data_get($product->seo_description, 'en')), 'Description SEO EN', 'English SEO description'),
        ];

        $missing = collect($checks)->where('passed', false)->values();
        $score = (int) round((collect($checks)->where('passed', true)->count() / count($checks)) * 100);
        [$status, $visibility] = match (true) {
            $score >= 90 => ['excellent', 'maximum'],
            $score >= 70 => ['good', 'high'],
            $score >= 45 => ['incomplete', 'limited'],
            default => ['critical', 'minimal'],
        };

        return [
            'score' => $score,
            'status' => $status,
            'visibility' => $visibility,
            'checks_count' => count($checks),
            'completed_count' => count($checks) - $missing->count(),
            'missing_count' => $missing->count(),
            'critical_count' => $missing->where('critical', true)->count(),
            'missing' => $missing->map(fn (array $check) => [
                'key' => $check['key'],
                'section' => $check['section'],
                'type' => $check['type'],
                'critical' => $check['critical'],
                'label' => $check['label'][$locale],
            ])->all(),
        ];
    }

    private function check(
        string $key,
        string $section,
        bool $passed,
        string $fr,
        string $en,
        bool $critical = false,
        string $type = 'missing',
    ): array {
        return compact('key', 'section', 'passed', 'critical', 'type') + [
            'label' => ['fr' => $fr, 'en' => $en],
        ];
    }
}
