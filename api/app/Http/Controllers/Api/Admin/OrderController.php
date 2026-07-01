<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Orders\StoreAdminOrderRequest;
use App\Http\Requests\Api\Admin\Orders\UpdateOrderRequest;
use App\Http\Resources\Admin\OrderAdminResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Customer;
use App\Services\Orders\OrderCreationService;
use App\Services\Orders\OrderManagementService;
use App\Support\MoneyFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->query($request);
        $summary = $this->summary(clone $query, $request);
        $perPage = max(5, min(100, $request->integer('per_page', 25)));

        return OrderAdminResource::collection($query->paginate($perPage))
            ->additional([
                'summary' => $summary,
            ]);
    }

    public function store(StoreAdminOrderRequest $request, OrderCreationService $orders)
    {
        $customer = Customer::query()->findOrFail($request->integer('customer_id'));
        $payload = $request->safe()->except('customer_id');
        $requestedMetadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];
        $adminNote = $requestedMetadata['admin_note'] ?? null;
        unset($requestedMetadata['admin_note']);

        $payload['metadata'] = [
            ...$requestedMetadata,
            'created_from' => 'admin',
            'created_by_admin_id' => $request->user()?->id,
        ];

        if (filled($adminNote)) {
            $payload['metadata']['admin_notes'] = [[
                'body' => trim((string) $adminNote),
                'actor_id' => $request->user()?->id,
                'actor_name' => $request->user()?->name,
                'created_at' => now()->toIso8601String(),
            ]];
        }

        $order = $orders->createFromCart($customer, $payload);

        return response()->json([
            'data' => new OrderAdminResource($order->load(['items', 'addresses', 'customer'])),
        ], 201);
    }

    public function show(Order $order): OrderAdminResource
    {
        return new OrderAdminResource($order->load(['items', 'addresses', 'customer', 'shipments.method', 'shipments.pickupPoint']));
    }

    public function update(UpdateOrderRequest $request, Order $order, OrderManagementService $orders): OrderAdminResource
    {
        return new OrderAdminResource(
            $orders->update($order, $request->validated(), $request->user(), $request),
        );
    }

    private function query(Request $request)
    {
        $query = Order::query()
            ->with(['items', 'addresses', 'customer'])
            ->latest('id');

        if ($request->filled('id')) {
            $query->whereKey($request->integer('id'));
        }

        if ($request->filled('q')) {
            $search = '%' . trim((string) $request->query('q')) . '%';

            $query->where(function ($query) use ($search) {
                $query
                    ->where('order_number', 'like', $search)
                    ->orWhere('customer_email', 'like', $search)
                    ->orWhere('customer_name', 'like', $search)
                    ->orWhere('customer_phone', 'like', $search);
            });
        }

        if ($request->filled('customer')) {
            $search = '%' . trim((string) $request->query('customer')) . '%';

            $query->where(function ($query) use ($search) {
                $query
                    ->where('customer_email', 'like', $search)
                    ->orWhere('customer_name', 'like', $search)
                    ->orWhere('customer_phone', 'like', $search);
            });
        }

        if ($request->filled('total')) {
            $total = $this->moneyToCents((string) $request->query('total'));

            if ($total !== null) {
                $query->where('total_cents', $total);
            }
        }

        foreach (['status', 'payment_status', 'fulfillment_status', 'carrier'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, (string) $request->query($field));
            }
        }

        if ($request->filled('new_customer')) {
            $newCustomer = filter_var($request->query('new_customer'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $previousOrderQuery = function ($query): void {
                $query
                    ->select(DB::raw(1))
                    ->from('orders as previous_orders')
                    ->whereColumn('previous_orders.customer_id', 'orders.customer_id')
                    ->whereColumn('previous_orders.id', '<', 'orders.id');
            };

            if ($newCustomer === true) {
                $query->whereNotExists($previousOrderQuery);
            } elseif ($newCustomer === false) {
                $query->whereExists($previousOrderQuery);
            }
        }

        if ($request->filled('date_from')) {
            $query->where('placed_at', '>=', (string) $request->query('date_from').' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('placed_at', '<=', (string) $request->query('date_to').' 23:59:59');
        }

        return $query;
    }

    private function moneyToCents(string $value): ?int
    {
        $normalized = trim(str_replace(['EUR', '€', ' '], '', $value));
        $normalized = str_replace(',', '.', $normalized);

        if (! is_numeric($normalized)) {
            return null;
        }

        return (int) round(((float) $normalized) * 100);
    }

    private function summary($query, Request $request): array
    {
        $locale = in_array($request->query('locale'), ['fr', 'en'], true) ? $request->query('locale') : 'fr';
        $totalValue = (int) (clone $query)->sum('total_cents');
        $totalOrders = (clone $query)->count();
        $averageOrderCents = $totalOrders > 0 ? (int) round($totalValue / $totalOrders) : 0;
        $abandonedCarts = Cart::query()
            ->whereNotIn('id', Order::query()->whereNotNull('cart_id')->select('cart_id'))
            ->count();
        $conversionDenominator = $totalOrders + $abandonedCarts;
        $conversionRate = $conversionDenominator > 0 ? round(($totalOrders / $conversionDenominator) * 100, 1) : 0.0;

        return [
            'total_orders' => $totalOrders,
            'pending_orders' => (clone $query)
                ->whereIn('status', ['pending_payment', 'confirmed', 'processing'])
                ->count(),
            'paid_orders' => (clone $query)->where('payment_status', 'paid')->count(),
            'to_prepare_orders' => (clone $query)
                ->whereIn('fulfillment_status', ['unfulfilled', 'preparing', 'ready_to_ship'])
                ->count(),
            'shipped_orders' => (clone $query)
                ->whereIn('fulfillment_status', ['shipped', 'delivered'])
                ->count(),
            'total_cents' => $totalValue,
            'formatted_total' => MoneyFormatter::format($totalValue, 'EUR', $locale),
            'conversion_rate_percent' => $conversionRate,
            'abandoned_carts' => $abandonedCarts,
            'average_order_cents' => $averageOrderCents,
            'formatted_average_order' => MoneyFormatter::format($averageOrderCents, 'EUR', $locale),
            'net_margin_per_visitor_cents' => 0,
            'formatted_net_margin_per_visitor' => MoneyFormatter::format(0, 'EUR', $locale),
        ];
    }
}
