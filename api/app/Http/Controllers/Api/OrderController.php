<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Orders\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Orders\OrderCreationService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(5, min(50, $request->integer('per_page', 15)));

        return OrderResource::collection(
            $request->user()
                ->orders()
                ->with(['items', 'addresses', 'shipments.method', 'shipments.pickupPoint'])
                ->latest('id')
                ->paginate($perPage),
        );
    }

    public function store(CreateOrderRequest $request, OrderCreationService $orders)
    {
        $order = $orders->createFromCart($request->user(), $request->validated());

        return response()->json([
            'data' => new OrderResource($order),
        ], 201);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        return new OrderResource($order->load(['items', 'addresses', 'shipments.method', 'shipments.pickupPoint']));
    }
}
