<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;

class ApiPresenter
{
    public static function category(Category $category, string $locale): array
    {
        return [
            'id' => $category->id,
            'slug' => $category->slug,
            'name' => $category->localized('name', $locale),
            'products_count' => $category->products_count ?? null,
        ];
    }

    public static function product(Product $product, string $locale): array
    {
        $product->loadMissing(['category', 'images', 'variants']);

        $primaryImage = $product->images->first();

        return [
            'id' => $product->id,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'slug' => $product->category->slug,
                'name' => $product->category->localized('name', $locale),
            ] : null,
            'name' => $product->localized('name', $locale),
            'slug' => $product->slug,
            'description' => $product->localized('description', $locale),
            'origin' => $product->localized('origin', $locale),
            'sku' => $product->sku,
            'price_cents' => $product->price_cents,
            'formatted_price' => MoneyFormatter::format($product->price_cents, $product->currency, $locale),
            'currency' => $product->currency,
            'weight_grams' => $product->weight_grams,
            'stock_quantity' => $product->stock_quantity,
            'is_active' => $product->is_active,
            'primary_image' => $primaryImage ? self::productImage($primaryImage, $locale) : null,
            'images' => $product->images
                ->map(fn (ProductImage $image) => self::productImage($image, $locale))
                ->values()
                ->all(),
            'variants' => $product->variants
                ->where('is_active', true)
                ->map(fn (ProductVariant $variant) => self::variant($product, $variant, $locale))
                ->values()
                ->all(),
        ];
    }

    public static function cart(Cart $cart, string $locale): array
    {
        $cart->loadMissing(['items.product.images', 'items.variant']);

        return [
            'cart_token' => $cart->cart_token,
            'currency' => $cart->currency,
            'subtotal_cents' => $cart->subtotal_cents,
            'formatted_subtotal' => MoneyFormatter::format($cart->subtotal_cents, $cart->currency, $locale),
            'tax_cents' => $cart->tax_cents,
            'formatted_tax' => MoneyFormatter::format($cart->tax_cents, $cart->currency, $locale),
            'total_cents' => $cart->total_cents,
            'formatted_total' => MoneyFormatter::format($cart->total_cents, $cart->currency, $locale),
            'items' => $cart->items
                ->map(function ($item) use ($cart, $locale) {
                    $product = $item->product;
                    $variant = $item->variant;
                    $primaryImage = $product?->images->first();

                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                        'unit_price_cents' => $item->unit_price_cents,
                        'formatted_unit_price' => MoneyFormatter::format($item->unit_price_cents, $cart->currency, $locale),
                        'line_total_cents' => $item->line_total_cents,
                        'formatted_line_total' => MoneyFormatter::format($item->line_total_cents, $cart->currency, $locale),
                        'product' => $product ? [
                            'id' => $product->id,
                            'name' => $product->localized('name', $locale),
                            'slug' => $product->slug,
                            'origin' => $product->localized('origin', $locale),
                            'image' => $primaryImage ? self::productImage($primaryImage, $locale) : null,
                        ] : null,
                        'variant' => $variant ? [
                            'id' => $variant->id,
                            'name' => $variant->localized('name', $locale),
                            'sku' => $variant->sku,
                        ] : null,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private static function productImage(ProductImage $image, string $locale): array
    {
        return [
            'id' => $image->id,
            'url' => $image->url,
            'alt_text' => $image->localized('alt_text', $locale),
        ];
    }

    private static function variant(Product $product, ProductVariant $variant, string $locale): array
    {
        $priceCents = max(0, $product->price_cents + $variant->price_adjustment_cents);

        return [
            'id' => $variant->id,
            'name' => $variant->localized('name', $locale),
            'sku' => $variant->sku,
            'price_adjustment_cents' => $variant->price_adjustment_cents,
            'price_cents' => $priceCents,
            'formatted_price' => MoneyFormatter::format($priceCents, $product->currency, $locale),
            'currency' => $product->currency,
            'stock_quantity' => $variant->stock_quantity,
            'is_active' => $variant->is_active,
        ];
    }
}
