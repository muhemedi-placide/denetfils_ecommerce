<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ApiPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $locale = $this->locale($request);

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount([
                'products as products_count' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Category $category) => ApiPresenter::category($category, $locale));

        return response()->json([
            'data' => $categories,
        ]);
    }

    private function locale(Request $request): string
    {
        $locale = $request->query('locale', 'fr');

        return in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
    }
}
