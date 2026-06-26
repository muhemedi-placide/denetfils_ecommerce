<?php

namespace App\Http\Resources\Admin\BackOffice;

use App\Services\Admin\BackOfficeMetricsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $threshold = max(0, min(100, (int) $request->query('threshold', 5)));
        $metrics = app(BackOfficeMetricsService::class);
        $primaryImage = $this->relationLoaded('images') ? $this->images->first() : null;

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'name' => $this->name,
            'preview_name' => [
                'fr' => $this->localized('name', 'fr'),
                'en' => $this->localized('name', 'en'),
            ],
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'slug' => $this->category->slug,
                'name' => $this->category->name,
                'is_active' => $this->category->is_active,
            ]),
            'stock_quantity' => $this->stock_quantity,
            'stock_status' => $metrics->stockStatus($this->resource, $threshold),
            'low_stock_threshold' => $threshold,
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
            'price_cents' => $this->price_cents,
            'currency' => $this->currency,
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'name' => $variant->name,
                'stock_quantity' => $variant->stock_quantity,
                'is_active' => $variant->is_active,
            ])->values()),
            'updated_at' => $this->updated_at,
        ];
    }
}
