<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\Seo\SeoPayloadBuilder;

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
        $seo = app(SeoPayloadBuilder::class);

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
            'short_description' => $product->localized('short_description', $locale),
            'origin' => $product->localized('origin', $locale),
            'sku' => $product->sku,
            'price_cents' => $product->price_cents,
            'formatted_price' => MoneyFormatter::format($product->price_cents, $product->currency, $locale),
            'tax_class' => $product->tax_class,
            'currency' => $product->currency,
            'weight_grams' => $product->weight_grams,
            'stock_quantity' => $product->stock_quantity,
            'max_order_quantity' => $product->max_order_quantity,
            'is_active' => $product->is_active,
            'primary_image' => $primaryImage ? self::productImage($primaryImage, $locale, true) : null,
            'images' => $product->images
                ->map(fn (ProductImage $image) => self::productImage($image, $locale, $primaryImage?->id === $image->id))
                ->values()
                ->all(),
            'variants' => $product->variants
                ->where('is_active', true)
                ->map(fn (ProductVariant $variant) => self::variant($product, $variant, $locale))
                ->values()
                ->all(),
            'rich_content' => self::richContent($product, $locale),
            'commerce' => self::commerce($product, $locale),
            'seo' => $seo->product($product, $locale),
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

    private static function productImage(ProductImage $image, string $locale, bool $isPrimary = false): array
    {
        return app(SeoPayloadBuilder::class)->imagePayload($image, $locale, $isPrimary);
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

    private static function richContent(Product $product, string $locale): array
    {
        return [
            'badges' => self::localizedNested($product->badges, $locale, []),
            'highlights' => self::localizedNested($product->highlights, $locale, []),
            'tags' => self::localizedNested($product->tags, $locale, []),
            'ingredients' => self::localizedNested($product->ingredients, $locale),
            'allergens' => self::localizedNested($product->allergens, $locale, []),
            'nutrition_facts' => $product->nutrition_facts ?? [],
            'certifications' => self::localizedNested($product->certifications, $locale, []),
            'storage_instructions' => self::localizedNested($product->storage_instructions, $locale),
            'usage_instructions' => self::localizedNested($product->usage_instructions, $locale),
        ];
    }

    private static function commerce(Product $product, string $locale): array
    {
        $stockState = match (true) {
            $product->stock_quantity <= 0 => 'out_of_stock',
            $product->stock_quantity <= 5 => 'low_stock',
            default => 'in_stock',
        };

        return [
            'brand' => config('seo.brand_name', config('shop.name')),
            'availability' => $stockState,
            'is_available' => $product->is_active && $product->stock_quantity > 0,
            'max_order_quantity' => $product->max_order_quantity
                ? min($product->max_order_quantity, $product->stock_quantity)
                : $product->stock_quantity,
            'rating' => [
                'average' => (float) $product->rating_average,
                'count' => $product->rating_count,
            ],
            'sales_count' => $product->sales_count,
            'shipping' => self::localizedNested($product->shipping_profile, $locale, []),
            'return_policy' => self::localizedNested($product->return_policy, $locale, []),
            'guarantee' => self::localizedNested($product->guarantee, $locale, []),
        ];
    }

    private static function localizedNested(mixed $value, string $locale, mixed $default = null): mixed
    {
        if ($value === null) {
            return $default;
        }

        if (! is_array($value)) {
            return $value;
        }

        if (array_key_exists($locale, $value) || array_key_exists('fr', $value) || array_key_exists('en', $value)) {
            return $value[$locale] ?? $value['fr'] ?? $value['en'] ?? $default;
        }

        return collect($value)
            ->map(fn (mixed $item) => self::localizedNested($item, $locale, $item))
            ->all();
    }
}
