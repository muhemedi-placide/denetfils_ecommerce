<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Catalog\StoreProductRequest;
use App\Http\Requests\Api\Admin\Catalog\UpdateProductRequest;
use App\Http\Resources\Admin\ProductAdminResource;
use App\Models\Product;
use App\Services\Catalog\CatalogManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'images', 'iconImage', 'variants'])
            ->latest('id');

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('publication_status')) {
            match ($request->query('publication_status')) {
                'published' => $query->where('is_active', true),
                'draft' => $query->where('is_active', false),
                default => null,
            };
        }

        if ($request->filled('stock_status')) {
            $threshold = max(0, min(100, $request->integer('threshold', 5)));

            match ($request->query('stock_status')) {
                'out_of_stock' => $query->where('stock_quantity', 0),
                'low_stock' => $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', $threshold),
                'in_stock' => $query->where('stock_quantity', '>', $threshold),
                default => null,
            };
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($query) => $query->where('slug', $request->query('category')));
        }

        if ($request->filled('q')) {
            $search = '%' . trim((string) $request->query('q')) . '%';

            $query->where(function ($query) use ($search) {
                $query
                    ->where('sku', 'like', $search)
                    ->orWhere('slug', 'like', $search)
                    ->orWhere('name->fr', 'like', $search)
                    ->orWhere('name->en', 'like', $search)
                    ->orWhere('description->fr', 'like', $search)
                    ->orWhere('description->en', 'like', $search)
                    ->orWhere('origin->fr', 'like', $search)
                    ->orWhere('origin->en', 'like', $search);
            });
        }

        $perPage = max(5, min(100, $request->integer('per_page', 25)));

        return ProductAdminResource::collection($query->paginate($perPage));
    }

    public function store(StoreProductRequest $request, CatalogManagementService $catalog): JsonResponse
    {
        $product = $catalog->createProduct($request->validated(), $request->user(), $request);

        return response()->json([
            'data' => new ProductAdminResource($product),
        ], 201);
    }

    public function show(Product $product): ProductAdminResource
    {
        return new ProductAdminResource($product->load(['category', 'images', 'iconImage', 'variants']));
    }

    public function update(UpdateProductRequest $request, Product $product, CatalogManagementService $catalog): ProductAdminResource
    {
        return new ProductAdminResource(
            $catalog->updateProduct($product, $request->validated(), $request->user(), $request),
        );
    }

    public function publish(Request $request, Product $product, CatalogManagementService $catalog): ProductAdminResource
    {
        return new ProductAdminResource(
            $catalog->setProductPublication($product, true, $request->user(), $request),
        );
    }

    public function unpublish(Request $request, Product $product, CatalogManagementService $catalog): ProductAdminResource
    {
        return new ProductAdminResource(
            $catalog->setProductPublication($product, false, $request->user(), $request),
        );
    }
}
