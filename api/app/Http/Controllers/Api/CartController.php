<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\ApiPresenter;
use App\Services\Carts\CartRecoveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $cart = Cart::create([
            'cart_token' => Str::random(48),
            'currency' => 'EUR',
            'subtotal_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 0,
            'expires_at' => now()->addDays(30),
            'last_activity_at' => now(),
        ]);

        return response()->json([
            'data' => ApiPresenter::cart($cart, $this->locale($request)),
        ], 201);
    }

    public function show(Request $request, string $cartToken): JsonResponse
    {
        $cart = $this->findCart($cartToken);

        return response()->json([
            'data' => ApiPresenter::cart($cart, $this->locale($request)),
        ]);
    }

    public function createRecoveryLink(
        Request $request,
        string $cartToken,
        CartRecoveryService $recovery,
    ): JsonResponse {
        $link = $recovery->issue($this->findCart($cartToken));

        return response()->json(['data' => $link], 201);
    }

    public function recover(
        Request $request,
        string $recoveryToken,
        CartRecoveryService $recovery,
    ): JsonResponse {
        return response()->json([
            'data' => ApiPresenter::cart($recovery->recover($recoveryToken), $this->locale($request)),
        ]);
    }

    public function addItem(Request $request, string $cartToken): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'product_variant_id' => ['nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->findCart($cartToken);
        $product = $this->findActiveProduct((int) $data['product_id']);
        $variant = $this->findActiveVariant($product, $data['product_variant_id'] ?? null);
        $quantity = (int) $data['quantity'];

        return DB::transaction(function () use ($cart, $product, $variant, $quantity, $request) {
            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)
                ->first();

            $nextQuantity = $quantity + ($item?->quantity ?? 0);
            $this->ensureStock($product, $variant, $nextQuantity);

            $unitPrice = $this->unitPrice($product, $variant);

            if ($item) {
                $item->update([
                    'quantity' => $nextQuantity,
                    'unit_price_cents' => $unitPrice,
                    'line_total_cents' => $unitPrice * $nextQuantity,
                ]);
            } else {
                $item = $cart->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'quantity' => $quantity,
                    'unit_price_cents' => $unitPrice,
                    'line_total_cents' => $unitPrice * $quantity,
                ]);
            }

            $cart->recalculateTotals();

            return response()->json([
                'data' => ApiPresenter::cart($cart->fresh(), $this->locale($request)),
            ], $item->wasRecentlyCreated ? 201 : 200);
        });
    }

    public function updateItem(Request $request, string $cartToken, int $item): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->findCart($cartToken);
        $cartItem = $this->findCartItem($cart, $item);
        $product = $this->findActiveProduct($cartItem->product_id);
        $variant = $cartItem->product_variant_id
            ? $this->findActiveVariant($product, $cartItem->product_variant_id)
            : null;
        $quantity = (int) $data['quantity'];

        $this->ensureStock($product, $variant, $quantity);

        $unitPrice = $this->unitPrice($product, $variant);

        $cartItem->update([
            'quantity' => $quantity,
            'unit_price_cents' => $unitPrice,
            'line_total_cents' => $unitPrice * $quantity,
        ]);

        $cart->recalculateTotals();

        return response()->json([
            'data' => ApiPresenter::cart($cart->fresh(), $this->locale($request)),
        ]);
    }

    public function destroyItem(Request $request, string $cartToken, int $item): JsonResponse
    {
        $cart = $this->findCart($cartToken);
        $cartItem = $this->findCartItem($cart, $item);

        $cartItem->delete();
        $cart->recalculateTotals();

        return response()->json([
            'data' => ApiPresenter::cart($cart->fresh(), $this->locale($request)),
        ]);
    }

    private function findCart(string $cartToken): Cart
    {
        return Cart::query()
            ->where('cart_token', $cartToken)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->firstOrFail();
    }

    private function findCartItem(Cart $cart, int $itemId): CartItem
    {
        return $cart->items()
            ->whereKey($itemId)
            ->firstOrFail();
    }

    private function findActiveProduct(int $productId): Product
    {
        $product = Product::query()
            ->whereKey($productId)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => __('validation.exists', ['attribute' => 'product_id']),
            ]);
        }

        return $product;
    }

    private function findActiveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        if (! $variantId) {
            return null;
        }

        $variant = $product->variants()
            ->whereKey($variantId)
            ->where('is_active', true)
            ->first();

        if (! $variant) {
            throw ValidationException::withMessages([
                'product_variant_id' => __('validation.exists', ['attribute' => 'product_variant_id']),
            ]);
        }

        return $variant;
    }

    private function ensureStock(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        $available = $variant?->stock_quantity ?? $product->stock_quantity;

        if ($quantity > $available) {
            throw ValidationException::withMessages([
                'quantity' => __('validation.max.numeric', [
                    'attribute' => 'quantity',
                    'max' => $available,
                ]),
            ]);
        }
    }

    private function unitPrice(Product $product, ?ProductVariant $variant): int
    {
        return max(0, $product->price_cents + ($variant?->price_adjustment_cents ?? 0));
    }

    private function locale(Request $request): string
    {
        $locale = $request->query('locale', 'fr');

        return in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
    }
}
