<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Catalog\ProductHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CatalogHealthController extends Controller
{
    public function index(Request $request, ProductHealthService $health): JsonResponse
    {
        $locale = in_array($request->query('locale'), ['fr', 'en'], true) ? $request->query('locale') : 'fr';
        $query = Product::query()->with(['category', 'images', 'iconImage'])->latest('id');

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->query('q')).'%';
            $query->where(fn ($query) => $query
                ->where('sku', 'like', $search)
                ->orWhere('name->fr', 'like', $search)
                ->orWhere('name->en', 'like', $search));
        }

        $diagnostics = $query->get()->map(function (Product $product) use ($health, $locale): array {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'is_active' => $product->is_active,
                'primary_image' => $product->images->first()?->url,
                'health' => $health->analyze($product, $locale),
            ];
        });

        if ($request->filled('status')) {
            $diagnostics = $diagnostics
                ->where('health.status', (string) $request->query('status'))
                ->values();
        }

        $perPage = max(5, min(100, $request->integer('per_page', 25)));
        $currentPage = max(1, $request->integer('page', 1));
        $page = new LengthAwarePaginator(
            $diagnostics->forPage($currentPage, $perPage)->values(),
            $diagnostics->count(),
            $perPage,
            $currentPage,
        );

        return response()->json([
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
            'summary' => [
                'products_count' => $diagnostics->count(),
                'average_score' => $diagnostics->count() ? (int) round($diagnostics->avg('health.score')) : 0,
                'missing_total' => (int) $diagnostics->sum('health.missing_count'),
                'excellent_count' => $diagnostics->where('health.status', 'excellent')->count(),
                'good_count' => $diagnostics->where('health.status', 'good')->count(),
                'incomplete_count' => $diagnostics->where('health.status', 'incomplete')->count(),
                'critical_count' => $diagnostics->where('health.status', 'critical')->count(),
                'scanned_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
