<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\User;
use App\Jobs\Shipping\CreateMondialRelayShipmentJob;
use App\Services\Core\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OrderManagementService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function update(Order $order, array $data, User $actor, Request $request): Order
    {
        return DB::transaction(function () use ($order, $data, $actor, $request) {
            $before = Arr::only($order->getAttributes(), [
                'status',
                'payment_status',
                'fulfillment_status',
                'carrier',
            ]);

            $order->fill(Arr::only($data, [
                'status',
                'payment_status',
                'fulfillment_status',
                'carrier',
            ]));

            $metadata = $this->metadata($order, $data, $actor);
            $order->metadata = $metadata === [] ? null : $metadata;

            $changed = array_keys($order->getDirty());
            $order->save();

            if (in_array('payment_status', $changed, true) && $order->payment_status === 'paid') {
                $shipment = $order->shipments()->where('status', 'pending')->first();
                if ($shipment && $shipment->carrier()->where('provider', 'mondial_relay')->exists()) {
                    DB::afterCommit(fn () => CreateMondialRelayShipmentJob::dispatch($shipment->id));
                }
            }

            $this->auditLogger->record(
                $actor,
                $this->auditAction($changed, $order),
                $order,
                $request,
                [
                    'order_number' => $order->order_number,
                    'before' => $before,
                    'after' => Arr::only($order->getAttributes(), [
                        'status',
                        'payment_status',
                        'fulfillment_status',
                        'carrier',
                    ]),
                    'changed' => $changed,
                    'notify_customer' => (bool) ($data['notify_customer'] ?? false),
                ],
            );

            return $order->refresh()->load(['items', 'addresses', 'user', 'shipments.method', 'shipments.pickupPoint']);
        });
    }

    private function metadata(Order $order, array $data, User $actor): array
    {
        $metadata = is_array($order->metadata) ? $order->metadata : [];

        if (array_key_exists('tracking_number', $data) || array_key_exists('tracking_url', $data)) {
            $tracking = is_array($metadata['tracking'] ?? null) ? $metadata['tracking'] : [];

            if (array_key_exists('tracking_number', $data)) {
                $this->setOrForget($tracking, 'number', $data['tracking_number'] ?? null);
            }

            if (array_key_exists('tracking_url', $data)) {
                $this->setOrForget($tracking, 'url', $data['tracking_url'] ?? null);
            }

            if ($tracking !== []) {
                $tracking['updated_at'] = now()->toIso8601String();
                $metadata['tracking'] = $tracking;
            } else {
                unset($metadata['tracking']);
            }
        }

        if (filled($data['admin_note'] ?? null)) {
            $notes = is_array($metadata['admin_notes'] ?? null) ? $metadata['admin_notes'] : [];
            $notes[] = [
                'body' => trim((string) $data['admin_note']),
                'actor_id' => $actor->id,
                'actor_name' => $actor->name,
                'created_at' => now()->toIso8601String(),
            ];

            $metadata['admin_notes'] = $notes;
        }

        if (array_key_exists('order_state', $data)) {
            $this->setOrForget($metadata, 'order_state', $data['order_state'] ?? null);
        } elseif (array_intersect(['status', 'payment_status', 'fulfillment_status'], array_keys($data)) !== []) {
            unset($metadata['order_state']);
        }

        return $metadata;
    }

    private function setOrForget(array &$payload, string $key, mixed $value): void
    {
        if (filled($value)) {
            $payload[$key] = $value;

            return;
        }

        unset($payload[$key]);
    }

    private function auditAction(array $changed, Order $order): string
    {
        if (in_array('status', $changed, true)) {
            return $order->status === 'cancelled' ? 'orders.cancelled' : 'orders.status_updated';
        }

        if (in_array('payment_status', $changed, true)) {
            return 'orders.payment_updated';
        }

        if (in_array('fulfillment_status', $changed, true)) {
            return 'orders.fulfillment_updated';
        }

        return 'orders.updated';
    }
}
