<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\Core\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogManagementService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function createCategory(array $data, User $actor, Request $request): Category
    {
        return DB::transaction(function () use ($data, $actor, $request) {
            $category = Category::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->auditLogger->record($actor, 'catalog.categories.created', $category, $request, [
                'slug' => $category->slug,
            ]);

            return $category->loadCount('products');
        });
    }

    public function updateCategory(Category $category, array $data, User $actor, Request $request): Category
    {
        return DB::transaction(function () use ($category, $data, $actor, $request) {
            $category->fill($data);
            $changed = array_keys($category->getDirty());
            $category->save();

            $this->auditLogger->record($actor, 'catalog.categories.updated', $category, $request, [
                'slug' => $category->slug,
                'changed' => $changed,
            ]);

            return $category->refresh()->loadCount('products');
        });
    }

    public function setCategoryActivation(Category $category, bool $active, User $actor, Request $request): Category
    {
        return DB::transaction(function () use ($category, $active, $actor, $request) {
            $category->forceFill(['is_active' => $active])->save();

            $this->auditLogger->record(
                $actor,
                $active ? 'catalog.categories.activated' : 'catalog.categories.deactivated',
                $category,
                $request,
                [
                    'slug' => $category->slug,
                    'is_active' => $category->is_active,
                ],
            );

            return $category->refresh()->loadCount('products');
        });
    }

    public function createProduct(array $data, User $actor, Request $request): Product
    {
        return DB::transaction(function () use ($data, $actor, $request) {
            $name = $this->localizedPair($data['name'] ?? []);
            $description = $this->localizedPair($data['description'] ?? [], '');
            $categoryId = $data['category_id'] ?? Category::query()->firstOrCreate(
                ['slug' => 'non-classe'],
                [
                    'name' => ['fr' => 'Non classé', 'en' => 'Uncategorized'],
                    'sort_order' => 9999,
                    'is_active' => true,
                ],
            )->id;
            $slug = $data['slug'] ?? $this->uniqueProductValue(
                'slug',
                Str::slug($name['fr'] ?: $name['en']) ?: 'produit',
            );
            $sku = $data['sku'] ?? $this->uniqueProductValue(
                'sku',
                'MP-'.Str::upper(Str::limit(str_replace('-', '', $slug), 18, '')).'-'.Str::upper(Str::random(6)),
            );

            $product = Product::create([
                ...Arr::except($data, ['images', 'variants']),
                'category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'slug' => $slug,
                'sku' => $sku,
                'currency' => $data['currency'] ?? 'EUR',
                'tax_class' => $data['tax_class'] ?? 'food',
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->syncImages($product, $data['images'] ?? []);
            $this->syncVariants($product, $data['variants'] ?? []);

            $this->auditLogger->record($actor, 'catalog.products.created', $product, $request, [
                'sku' => $product->sku,
                'slug' => $product->slug,
            ]);

            return $product->refresh()->load(['category', 'images', 'iconImage', 'variants']);
        });
    }

    public function updateProduct(Product $product, array $data, User $actor, Request $request): Product
    {
        return DB::transaction(function () use ($product, $data, $actor, $request) {
            $product->fill(Arr::except($data, ['images', 'variants']));
            $changed = array_keys($product->getDirty());
            $product->save();

            if (array_key_exists('images', $data)) {
                $this->syncImages($product, $data['images']);
                $changed[] = 'images';
            }

            if (array_key_exists('variants', $data)) {
                $this->syncVariants($product, $data['variants']);
                $changed[] = 'variants';
            }

            $this->auditLogger->record($actor, 'catalog.products.updated', $product, $request, [
                'sku' => $product->sku,
                'slug' => $product->slug,
                'changed' => array_values(array_unique($changed)),
            ]);

            return $product->refresh()->load(['category', 'images', 'iconImage', 'variants']);
        });
    }

    public function setProductPublication(Product $product, bool $published, User $actor, Request $request): Product
    {
        return DB::transaction(function () use ($product, $published, $actor, $request) {
            $product->forceFill([
                'is_active' => $published,
                'published_at' => $published ? ($product->published_at ?: now()) : $product->published_at,
            ])->save();

            $this->auditLogger->record(
                $actor,
                $published ? 'catalog.products.published' : 'catalog.products.unpublished',
                $product,
                $request,
                [
                    'sku' => $product->sku,
                    'slug' => $product->slug,
                    'is_active' => $product->is_active,
                    'published_at' => $product->published_at?->toIso8601String(),
                ],
            );

            return $product->refresh()->load(['category', 'images', 'iconImage', 'variants']);
        });
    }

    private function syncImages(Product $product, array $images): void
    {
        $product->media()->delete();

        $primaryAssigned = false;

        foreach ($images as $index => $image) {
            $role = $image['role'] ?? 'gallery';
            $isPrimary = $role === 'gallery' && ! $primaryAssigned && (bool) ($image['is_primary'] ?? true);
            $primaryAssigned = $primaryAssigned || $isPrimary;

            $product->media()->create([
                'role' => $role,
                'is_primary' => $isPrimary,
                'url' => $image['url'],
                'original_name' => $image['original_name'] ?? null,
                'mime_type' => $image['mime_type'] ?? null,
                'size_bytes' => $image['size_bytes'] ?? null,
                'width' => $image['width'] ?? null,
                'height' => $image['height'] ?? null,
                'dominant_color' => $image['dominant_color'] ?? null,
                'alt_text' => $image['alt_text'] ?? null,
                'sort_order' => $role === 'icon' ? 0 : ($image['sort_order'] ?? $index + 1),
            ]);
        }
    }

    private function localizedPair(array $values, ?string $fallback = null): array
    {
        $first = collect([$values['fr'] ?? null, $values['en'] ?? null, $fallback])
            ->first(fn ($value) => $value !== null && $value !== '');

        return [
            'fr' => $values['fr'] ?? $first ?? '',
            'en' => $values['en'] ?? $first ?? '',
        ];
    }

    private function uniqueProductValue(string $column, string $base): string
    {
        $candidate = $base;
        $suffix = 1;

        while (Product::query()->where($column, $candidate)->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function syncVariants(Product $product, array $variants): void
    {
        $seenVariantIds = [];

        foreach ($variants as $variantData) {
            $variantId = $variantData['id'] ?? null;
            $attributes = Arr::except($variantData, ['id']);

            if ($variantId) {
                $variant = $product->variants()->whereKey($variantId)->firstOrFail();
                $variant->update($attributes);
            } else {
                $variant = $product->variants()->create([
                    ...$attributes,
                    'price_adjustment_cents' => $attributes['price_adjustment_cents'] ?? 0,
                    'is_active' => $attributes['is_active'] ?? true,
                ]);
            }

            $seenVariantIds[] = $variant->id;
        }

        $query = $product->variants();

        if ($seenVariantIds !== []) {
            $query->whereNotIn('id', $seenVariantIds);
        }

        $query->update(['is_active' => false]);
    }
}
