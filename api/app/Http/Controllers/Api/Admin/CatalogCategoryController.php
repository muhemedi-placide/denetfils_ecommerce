<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Catalog\StoreCategoryRequest;
use App\Http\Requests\Api\Admin\Catalog\UpdateCategoryRequest;
use App\Http\Resources\Admin\CategoryAdminResource;
use App\Models\Category;
use App\Services\Catalog\CatalogManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('q')) {
            $search = '%' . trim((string) $request->query('q')) . '%';

            $query->where(function ($query) use ($search) {
                $query
                    ->where('slug', 'like', $search)
                    ->orWhere('name->fr', 'like', $search)
                    ->orWhere('name->en', 'like', $search);
            });
        }

        return CategoryAdminResource::collection($query->paginate(25));
    }

    public function store(StoreCategoryRequest $request, CatalogManagementService $catalog): JsonResponse
    {
        $category = $catalog->createCategory($request->validated(), $request->user(), $request);

        return response()->json([
            'data' => new CategoryAdminResource($category),
        ], 201);
    }

    public function show(Category $category): CategoryAdminResource
    {
        return new CategoryAdminResource($category->loadCount('products'));
    }

    public function update(UpdateCategoryRequest $request, Category $category, CatalogManagementService $catalog): CategoryAdminResource
    {
        return new CategoryAdminResource(
            $catalog->updateCategory($category, $request->validated(), $request->user(), $request),
        );
    }
}
