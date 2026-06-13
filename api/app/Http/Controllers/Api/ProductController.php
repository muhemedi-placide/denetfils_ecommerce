<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\ApiPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $locale = $this->locale($request);
        $category = $request->query('category');
        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'default');

        $query = Product::query()
            ->with(['category', 'images', 'variants'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true));

        if ($category) {
            $query->whereHas('category', fn ($query) => $query->where('slug', $category));
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $like = "%{$search}%";

                $query
                    ->where('sku', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('name->fr', 'like', $like)
                    ->orWhere('name->en', 'like', $like)
                    ->orWhere('description->fr', 'like', $like)
                    ->orWhere('description->en', 'like', $like)
                    ->orWhere('origin->fr', 'like', $like)
                    ->orWhere('origin->en', 'like', $like);
            });
        }

        match ($sort) {
            'price_asc' => $query->orderBy('price_cents')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price_cents')->orderBy('id'),
            'latest' => $query->latest('id'),
            default => $query->orderBy('id'),
        };

        $products = $query
            ->get()
            ->map(fn (Product $product) => ApiPresenter::product($product, $locale));

        return response()->json([
            'data' => $products,
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $locale = $this->locale($request);

        $product = Product::query()
            ->with(['category', 'images', 'variants'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->firstOrFail();

        return response()->json([
            'data' => ApiPresenter::product($product, $locale),
        ]);
    }

    private function locale(Request $request): string
    {
        $locale = $request->query('locale', 'fr');

        return in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
    }
}
