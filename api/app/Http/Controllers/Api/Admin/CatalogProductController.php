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
            ->with(['category', 'images', 'variants'])
            ->latest('id');

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
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

        return ProductAdminResource::collection($query->paginate(25));
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
        return new ProductAdminResource($product->load(['category', 'images', 'variants']));
    }

    public function update(UpdateProductRequest $request, Product $product, CatalogManagementService $catalog): ProductAdminResource
    {
        return new ProductAdminResource(
            $catalog->updateProduct($product, $request->validated(), $request->user(), $request),
        );
    }
}
