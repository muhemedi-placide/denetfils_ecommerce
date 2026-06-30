<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryImage = $this->relationLoaded('images') ? $this->images->first() : null;

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'is_active' => $this->category->is_active,
            ]),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'origin' => $this->origin,
            'highlights' => $this->highlights,
            'badges' => $this->badges,
            'tags' => $this->tags,
            'ingredients' => $this->ingredients,
            'allergens' => $this->allergens,
            'nutrition_facts' => $this->nutrition_facts,
            'certifications' => $this->certifications,
            'storage_instructions' => $this->storage_instructions,
            'usage_instructions' => $this->usage_instructions,
            'shipping_profile' => $this->shipping_profile,
            'return_policy' => $this->return_policy,
            'guarantee' => $this->guarantee,
            'sku' => $this->sku,
            'price_cents' => $this->price_cents,
            'currency' => $this->currency,
            'tax_class' => $this->tax_class,
            'weight_grams' => $this->weight_grams,
            'stock_quantity' => $this->stock_quantity,
            'max_order_quantity' => $this->max_order_quantity,
            'rating_average' => $this->rating_average,
            'rating_count' => $this->rating_count,
            'sales_count' => $this->sales_count,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_keywords' => $this->seo_keywords,
            'canonical_path' => $this->canonical_path,
            'published_at' => $this->published_at,
            'is_active' => $this->is_active,
            'primary_image' => $primaryImage ? [
                'id' => $primaryImage->id,
                'url' => $primaryImage->url,
                'width' => $primaryImage->width,
                'height' => $primaryImage->height,
                'dominant_color' => $primaryImage->dominant_color,
                'alt_text' => $primaryImage->alt_text,
                'sort_order' => $primaryImage->sort_order,
            ] : null,
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => $image->url,
                'width' => $image->width,
                'height' => $image->height,
                'dominant_color' => $image->dominant_color,
                'alt_text' => $image->alt_text,
                'sort_order' => $image->sort_order,
            ])->values()),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'price_adjustment_cents' => $variant->price_adjustment_cents,
                'stock_quantity' => $variant->stock_quantity,
                'is_active' => $variant->is_active,
            ])->values()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
