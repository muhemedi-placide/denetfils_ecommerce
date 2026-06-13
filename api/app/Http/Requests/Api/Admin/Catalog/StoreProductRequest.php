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
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'array'],
            'name.fr' => ['required', 'string', 'max:180'],
            'name.en' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:220', 'alpha_dash:ascii', Rule::unique('products', 'slug')],
            'description' => ['required', 'array'],
            'description.fr' => ['required', 'string', 'max:5000'],
            'description.en' => ['required', 'string', 'max:5000'],
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
            'sku' => ['required', 'string', 'max:80', Rule::unique('products', 'sku')],
            'price_cents' => ['required', 'integer', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3', Rule::in(['EUR'])],
            'weight_grams' => ['nullable', 'integer', 'min:1'],
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
            'images' => ['nullable', 'array', 'max:12'],
            'images.*.url' => ['required_with:images', 'url', 'max:2048'],
            'images.*.width' => ['nullable', 'integer', 'min:1'],
            'images.*.height' => ['nullable', 'integer', 'min:1'],
            'images.*.dominant_color' => ['nullable', 'string', 'max:16'],
            'images.*.alt_text' => ['nullable', 'array'],
            'images.*.alt_text.fr' => ['nullable', 'string', 'max:180'],
            'images.*.alt_text.en' => ['nullable', 'string', 'max:180'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
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
}
