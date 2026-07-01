<?php

namespace App\Http\Controllers\Api\Admin;

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
        return $this->respond($request, $order->conversation?->load('messages'));
    }

    public function open(Request $request, Order $order): JsonResponse
    {
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
            'user_id' => $request->user()->id,
            'sender_type' => 'staff',
            'body' => trim($validated['body']),
        ]);

        $conversation->forceFill([
            'status' => 'open',
            'customer_unread_count' => $conversation->customer_unread_count + 1,
            'staff_last_read_at' => now(),
        ])->save();

        return $this->respond($request, $conversation->refresh()->load('messages'));
    }

    public function markRead(Request $request, Order $order): JsonResponse
    {
        $conversation = $this->conversation($order);
        $conversation->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->forceFill([
            'staff_unread_count' => 0,
            'staff_last_read_at' => now(),
        ])->save();

        return $this->respond($request, $conversation->refresh()->load('messages'));
    }

    public function close(Request $request, Order $order): JsonResponse
    {
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

    private function respond(Request $request, ?OrderConversation $conversation): JsonResponse
    {
        return response()->json([
            'data' => (new OrderConversationResource($conversation))->resolve($request),
        ]);
    }
}
