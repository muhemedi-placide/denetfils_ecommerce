<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Services\Carts\CartRecoveryService;
use App\Support\ApiPresenter;
use App\Support\MoneyFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $locale = $this->locale($request);
        $query = Cart::query()
            ->with(['items.product.category', 'items.product.images', 'items.variant', 'customer', 'order'])
            ->withCount('recoveryLinks')
            ->latest('last_activity_at')
            ->latest('id');

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->query('q')).'%';
            $query->where(function (Builder $query) use ($search): void {
                $query->where('cart_token', 'like', $search)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer
                        ->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search));
            });
        }

        $status = (string) $request->query('status', '');
        if ($status === 'converted') {
            $query->whereHas('order');
        } elseif ($status === 'expired') {
            $query->whereDoesntHave('order')->where('expires_at', '<=', now());
        } elseif ($status === 'empty') {
            $query->whereDoesntHave('order')->whereDoesntHave('items');
        } elseif ($status === 'abandoned') {
            $query->whereDoesntHave('order')->whereHas('items')
                ->where(fn (Builder $q) => $q->where('last_activity_at', '<=', now()->subDay())->orWhereNull('last_activity_at'))
                ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
        } elseif ($status === 'active') {
            $query->whereDoesntHave('order')->whereHas('items')
                ->where('last_activity_at', '>', now()->subDay())
                ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', (string) $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', (string) $request->query('date_to'));
        }

        $summaryQuery = clone $query;
        $page = $query->paginate(max(5, min(100, $request->integer('per_page', 25))));
        $page->getCollection()->transform(fn (Cart $cart) => [
            'id' => $cart->id,
            ...ApiPresenter::cart($cart, $locale),
            'recovery_links_count' => $cart->recovery_links_count,
            'admin_status' => $this->status($cart),
        ]);

        return response()->json([
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
            'summary' => [
                'count' => $page->total(),
                'value_cents' => (int) $summaryQuery->sum('total_cents'),
                'formatted_value' => MoneyFormatter::format((int) $summaryQuery->sum('total_cents'), 'EUR', $locale),
                'abandoned_count' => Cart::query()->whereDoesntHave('order')->whereHas('items')
                    ->where(fn (Builder $q) => $q->where('last_activity_at', '<=', now()->subDay())->orWhereNull('last_activity_at'))
                    ->count(),
            ],
        ]);
    }

    public function show(Request $request, Cart $cart): JsonResponse
    {
        $cart->load(['items.product.category', 'items.product.images', 'items.variant', 'customer.addresses', 'order', 'recoveryLinks']);

        return response()->json(['data' => [
            'id' => $cart->id,
            ...ApiPresenter::cart($cart, $this->locale($request)),
            'admin_status' => $this->status($cart),
            'recovery_links' => $cart->recoveryLinks->map(fn ($link) => [
                'id' => $link->id,
                'expires_at' => $link->expires_at?->toIso8601String(),
                'last_used_at' => $link->last_used_at?->toIso8601String(),
                'uses_count' => $link->uses_count,
                'created_at' => $link->created_at?->toIso8601String(),
            ])->values(),
        ]]);
    }

    public function createRecoveryLink(
        Request $request,
        Cart $cart,
        CartRecoveryService $recovery,
    ): JsonResponse {
        return response()->json(['data' => $recovery->issue($cart)], 201);
    }

    private function locale(Request $request): string
    {
        return in_array($request->query('locale'), ['fr', 'en'], true) ? $request->query('locale') : 'fr';
    }

    private function status(Cart $cart): string
    {
        if ($cart->order) return 'converted';
        if ($cart->expires_at?->isPast()) return 'expired';
        if ($cart->items->isEmpty()) return 'empty';

        return ($cart->last_activity_at ?? $cart->updated_at)?->lte(now()->subDay()) ? 'abandoned' : 'active';
    }
}
