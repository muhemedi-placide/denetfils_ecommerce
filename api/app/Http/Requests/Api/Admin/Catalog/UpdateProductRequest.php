<?php

namespace App\Http\Requests\Api\Admin\Catalog;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['sometimes', 'integer', Rule::exists('categories', 'id')],
            'name' => ['sometimes', 'array'],
            'name.fr' => ['required_with:name', 'string', 'max:180'],
            'name.en' => ['required_with:name', 'string', 'max:180'],
            'slug' => [
                'sometimes',
                'string',
                'max:220',
                'alpha_dash:ascii',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'description' => ['sometimes', 'array'],
            'description.fr' => ['required_with:description', 'string', 'max:5000'],
            'description.en' => ['required_with:description', 'string', 'max:5000'],
            'short_description' => ['sometimes', 'nullable', 'array'],
            'short_description.fr' => ['nullable', 'string', 'max:500'],
            'short_description.en' => ['nullable', 'string', 'max:500'],
            'origin' => ['sometimes', 'nullable', 'array'],
            'origin.fr' => ['nullable', 'string', 'max:180'],
            'origin.en' => ['nullable', 'string', 'max:180'],
            'highlights' => ['sometimes', 'nullable', 'array'],
            'badges' => ['sometimes', 'nullable', 'array'],
            'tags' => ['sometimes', 'nullable', 'array'],
            'ingredients' => ['sometimes', 'nullable', 'array'],
            'allergens' => ['sometimes', 'nullable', 'array'],
            'nutrition_facts' => ['sometimes', 'nullable', 'array'],
            'certifications' => ['sometimes', 'nullable', 'array'],
            'storage_instructions' => ['sometimes', 'nullable', 'array'],
            'usage_instructions' => ['sometimes', 'nullable', 'array'],
            'shipping_profile' => ['sometimes', 'nullable', 'array'],
            'return_policy' => ['sometimes', 'nullable', 'array'],
            'guarantee' => ['sometimes', 'nullable', 'array'],
            'sku' => ['sometimes', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($product?->id)],
            'price_cents' => ['sometimes', 'integer', 'min:1'],
            'currency' => ['sometimes', 'string', 'size:3', Rule::in(['EUR'])],
            'weight_grams' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'max_order_quantity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'rating_average' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:5'],
            'rating_count' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'sales_count' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'seo_title' => ['sometimes', 'nullable', 'array'],
            'seo_title.fr' => ['nullable', 'string', 'max:180'],
            'seo_title.en' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['sometimes', 'nullable', 'array'],
            'seo_description.fr' => ['nullable', 'string', 'max:320'],
            'seo_description.en' => ['nullable', 'string', 'max:320'],
            'seo_keywords' => ['sometimes', 'nullable', 'array'],
            'canonical_path' => ['sometimes', 'nullable', 'string', 'max:255', 'starts_with:/'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'images' => ['sometimes', 'array', 'max:12'],
            'images.*.url' => ['required_with:images', 'url', 'max:2048'],
            'images.*.width' => ['nullable', 'integer', 'min:1'],
            'images.*.height' => ['nullable', 'integer', 'min:1'],
            'images.*.dominant_color' => ['nullable', 'string', 'max:16'],
            'images.*.alt_text' => ['nullable', 'array'],
            'images.*.alt_text.fr' => ['nullable', 'string', 'max:180'],
            'images.*.alt_text.en' => ['nullable', 'string', 'max:180'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'variants' => ['sometimes', 'array', 'max:30'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required_with:variants', 'array'],
            'variants.*.name.fr' => ['required_with:variants.*.name', 'string', 'max:180'],
            'variants.*.name.en' => ['required_with:variants.*.name', 'string', 'max:180'],
            'variants.*.sku' => ['nullable', 'string', 'max:80', 'distinct'],
            'variants.*.price_adjustment_cents' => ['nullable', 'integer'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = $this->route('product');

            foreach ($this->input('variants', []) as $index => $variant) {
                $variantId = $variant['id'] ?? null;

                if ($variantId && ! ProductVariant::query()
                    ->whereKey($variantId)
                    ->where('product_id', $product?->id)
                    ->exists()) {
                    $validator->errors()->add("variants.{$index}.id", 'The selected variant does not belong to this product.');
                }

                $sku = $variant['sku'] ?? null;

                if ($sku && ProductVariant::query()
                    ->where('sku', $sku)
                    ->when($variantId, fn ($query) => $query->whereKeyNot($variantId))
                    ->exists()) {
                    $validator->errors()->add("variants.{$index}.sku", 'The variant SKU has already been taken.');
                }
            }
        });
    }
}
