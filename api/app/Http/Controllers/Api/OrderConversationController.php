<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderConversationResource;
use App\Models\Order;
use App\Models\OrderConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderConversationController extends Controller
{
    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorizeCustomerOrder($request, $order);

        $conversation = $order->conversation?->load('messages');

        return $this->respond($request, $conversation);
    }

    public function open(Request $request, Order $order): JsonResponse
    {
        $this->authorizeCustomerOrder($request, $order);

        $conversation = $this->conversation($order);

        if ($conversation->status === 'closed') {
            $conversation->forceFill([
                'status' => 'open',
                'closed_at' => null,
            ])->save();
        }

        return $this->respond($request, $conversation->refresh()->load('messages'));
    }

    public function storeMessage(Request $request, Order $order): JsonResponse
    {
        $this->authorizeCustomerOrder($request, $order);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $conversation = $this->conversation($order);

        if ($conversation->status === 'closed') {
            throw ValidationException::withMessages([
                'body' => __('This discussion is closed.'),
            ]);
        }

        $conversation->messages()->create([
            'customer_id' => $request->user()->id,
            'sender_type' => 'customer',
            'body' => trim($validated['body']),
        ]);

        $conversation->forceFill([
            'status' => 'open',
            'staff_unread_count' => $conversation->staff_unread_count + 1,
            'customer_last_read_at' => now(),
        ])->save();

        return $this->respond($request, $conversation->refresh()->load('messages'));
    }

    public function markRead(Request $request, Order $order): JsonResponse
    {
        $this->authorizeCustomerOrder($request, $order);

        $conversation = $this->conversation($order);
        $conversation->messages()
            ->where('sender_type', '!=', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->forceFill([
            'customer_unread_count' => 0,
            'customer_last_read_at' => now(),
        ])->save();

        return $this->respond($request, $conversation->refresh()->load('messages'));
    }

    public function close(Request $request, Order $order): JsonResponse
    {
        $this->authorizeCustomerOrder($request, $order);

        $conversation = $this->conversation($order);
        $conversation->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
        ])->save();

        return $this->respond($request, $conversation->refresh()->load('messages'));
    }

    private function conversation(Order $order): OrderConversation
    {
        return $order->conversation()->firstOrCreate(
            ['order_id' => $order->id],
            ['status' => 'open'],
        );
    }

    private function authorizeCustomerOrder(Request $request, Order $order): void
    {
        abort_unless($order->customer_id === $request->user()->id, 404);
    }

    private function respond(Request $request, ?OrderConversation $conversation): JsonResponse
    {
        return response()->json([
            'data' => (new OrderConversationResource($conversation))->resolve($request),
        ]);
    }
}
