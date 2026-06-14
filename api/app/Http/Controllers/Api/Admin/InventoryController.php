<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\BackOffice\InventoryProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $threshold = max(0, min(100, $request->integer('threshold', 5)));
        $query = Product::query()
            ->with(['category', 'variants'])
            ->latest('updated_at');

        if ($request->filled('q')) {
            $search = '%' . trim((string) $request->query('q')) . '%';

            $query->where(function ($query) use ($search) {
                $query
                    ->where('sku', 'like', $search)
                    ->orWhere('slug', 'like', $search)
                    ->orWhere('name->fr', 'like', $search)
                    ->orWhere('name->en', 'like', $search);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        match ($request->query('status')) {
            'inactive' => $query->where('is_active', false),
            'out_of_stock' => $query->where('is_active', true)->where('stock_quantity', 0),
            'low_stock' => $query->where('is_active', true)
                ->where('stock_quantity', '>', 0)
                ->where('stock_quantity', '<=', $threshold),
            'in_stock' => $query->where('is_active', true)->where('stock_quantity', '>', $threshold),
            default => null,
        };

        match ($request->query('sort')) {
            'stock_asc' => $query->orderBy('stock_quantity')->orderBy('id'),
            'stock_desc' => $query->orderByDesc('stock_quantity')->orderByDesc('id'),
            'updated_asc' => $query->oldest('updated_at'),
            default => null,
        };

        $perPage = max(5, min(100, $request->integer('per_page', 25)));

        return InventoryProductResource::collection($query->paginate($perPage))
            ->additional([
                'summary' => [
                    'low_stock_threshold' => $threshold,
                    'status' => $request->query('status', 'all'),
                ],
            ]);
    }
}
