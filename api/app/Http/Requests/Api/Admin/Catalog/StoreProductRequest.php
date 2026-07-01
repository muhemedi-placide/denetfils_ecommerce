<?php

namespace App\Http\Requests\Api\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'array'],
            'name.fr' => ['nullable', 'string', 'max:180'],
            'name.en' => ['nullable', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:220', 'alpha_dash:ascii', Rule::unique('products', 'slug')],
            'description' => ['nullable', 'array'],
            'description.fr' => ['nullable', 'string', 'max:5000'],
            'description.en' => ['nullable', 'string', 'max:5000'],
            'short_description' => ['nullable', 'array'],
            'short_description.fr' => ['nullable', 'string', 'max:500'],
            'short_description.en' => ['nullable', 'string', 'max:500'],
            'origin' => ['nullable', 'array'],
            'origin.fr' => ['nullable', 'string', 'max:180'],
            'origin.en' => ['nullable', 'string', 'max:180'],
            'highlights' => ['nullable', 'array'],
            'badges' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'ingredients' => ['nullable', 'array'],
            'allergens' => ['nullable', 'array'],
            'nutrition_facts' => ['nullable', 'array'],
            'certifications' => ['nullable', 'array'],
            'storage_instructions' => ['nullable', 'array'],
            'usage_instructions' => ['nullable', 'array'],
            'shipping_profile' => ['nullable', 'array'],
            'return_policy' => ['nullable', 'array'],
            'guarantee' => ['nullable', 'array'],
            'sku' => ['nullable', 'string', 'max:80', Rule::unique('products', 'sku')],
            'barcode' => ['nullable', 'string', 'max:64', Rule::unique('products', 'barcode')],
            'brand' => ['nullable', 'string', 'max:120'],
            'supplier_reference' => ['nullable', 'string', 'max:120'],
            'purchase_price_cents' => ['nullable', 'integer', 'min:0'],
            'price_cents' => ['required', 'integer', 'min:1'],
            'compare_at_price_cents' => ['nullable', 'integer', 'min:1', 'gte:price_cents'],
            'currency' => ['nullable', 'string', 'size:3', Rule::in(['EUR'])],
            'price_includes_tax' => ['nullable', 'boolean'],
            'tax_class' => ['nullable', Rule::in(['food', 'standard'])],
            'weight_grams' => ['nullable', 'integer', 'min:1'],
            'unit_label' => ['nullable', 'string', 'max:40'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'max_order_quantity' => ['nullable', 'integer', 'min:1'],
            'rating_average' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'rating_count' => ['nullable', 'integer', 'min:0'],
            'sales_count' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'array'],
            'seo_title.fr' => ['nullable', 'string', 'max:180'],
            'seo_title.en' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'array'],
            'seo_description.fr' => ['nullable', 'string', 'max:320'],
            'seo_description.en' => ['nullable', 'string', 'max:320'],
            'seo_keywords' => ['nullable', 'array'],
            'canonical_path' => ['nullable', 'string', 'max:255', 'starts_with:/'],
            'published_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['required', 'array', 'min:1', 'max:13'],
            'images.*.url' => ['required_with:images', 'url', 'max:2048'],
            'images.*.width' => ['nullable', 'integer', 'min:1'],
            'images.*.height' => ['nullable', 'integer', 'min:1'],
            'images.*.dominant_color' => ['nullable', 'string', 'max:16'],
            'images.*.alt_text' => ['nullable', 'array'],
            'images.*.alt_text.fr' => ['nullable', 'string', 'max:180'],
            'images.*.alt_text.en' => ['nullable', 'string', 'max:180'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'images.*.role' => ['nullable', Rule::in(['gallery', 'icon'])],
            'images.*.is_primary' => ['nullable', 'boolean'],
            'images.*.original_name' => ['nullable', 'string', 'max:255'],
            'images.*.mime_type' => ['nullable', 'string', 'max:100'],
            'images.*.size_bytes' => ['nullable', 'integer', 'min:0'],
            'variants' => ['nullable', 'array', 'max:30'],
            'variants.*.name' => ['required_with:variants', 'array'],
            'variants.*.name.fr' => ['required_with:variants.*.name', 'string', 'max:180'],
            'variants.*.name.en' => ['required_with:variants.*.name', 'string', 'max:180'],
            'variants.*.sku' => ['nullable', 'string', 'max:80', 'distinct', Rule::unique('product_variants', 'sku')],
            'variants.*.price_adjustment_cents' => ['nullable', 'integer'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! filled($this->input('name.fr')) && ! filled($this->input('name.en'))) {
                $validator->errors()->add('name', 'At least one product name is required.');
            }

            $hasGalleryImage = collect($this->input('images', []))
                ->contains(fn (array $image) => ($image['role'] ?? 'gallery') === 'gallery');

            if (! $hasGalleryImage) {
                $validator->errors()->add('images', 'At least one gallery image is required.');
            }
        });
    }
}
