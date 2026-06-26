<?php

namespace App\Http\Resources;

use App\Models\OrderMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdminViewer = str_contains($request->path(), 'admin/orders/');

        if (! $this->resource) {
            return [
                'id' => null,
                'order_id' => null,
                'status' => 'not_started',
                'customer_unread_count' => 0,
                'staff_unread_count' => 0,
                'customer_last_read_at' => null,
                'staff_last_read_at' => null,
                'closed_at' => null,
                'messages' => [],
                'status_options' => ['not_started', 'open', 'closed'],
            ];
        }

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'status' => $this->status,
            'customer_unread_count' => $this->customer_unread_count,
            'staff_unread_count' => $this->staff_unread_count,
            'customer_last_read_at' => $this->customer_last_read_at?->toIso8601String(),
            'staff_last_read_at' => $this->staff_last_read_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'messages' => $this->whenLoaded('messages', fn () => $this->messages
                ->sortBy('created_at')
                ->map(function (OrderMessage $message) use ($request, $isAdminViewer) {
                    $unreadForCustomer = $message->sender_type !== 'customer' && $message->read_at === null;
                    $unreadForStaff = $message->sender_type === 'customer' && $message->read_at === null;

                    $isOwn = $message->user_id === $request->user()?->id || ($isAdminViewer && $message->sender_type === 'staff');
                    $statusForCustomer = $message->sender_type === 'customer' || ! $unreadForCustomer ? 'read' : 'unread';
                    $statusForStaff = $message->sender_type === 'staff' || ! $unreadForStaff ? 'read' : 'unread';

                    return [
                        'id' => $message->id,
                        'sender_type' => $message->sender_type,
                        'body' => $message->body,
                        'read_at' => $message->read_at?->toIso8601String(),
                        'created_at' => $message->created_at?->toIso8601String(),
                        'is_own' => $isOwn,
                        'is_unread_for_customer' => $unreadForCustomer,
                        'is_unread_for_staff' => $unreadForStaff,
                        'status_for_customer' => $statusForCustomer,
                        'status_for_staff' => $statusForStaff,
                        'status' => $isAdminViewer ? $statusForStaff : $statusForCustomer,
                    ];
                })
                ->values()
                ->all()),
            'status_options' => ['not_started', 'open', 'closed'],
        ];
    }
}
