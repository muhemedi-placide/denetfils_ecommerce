<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Shipping\CreateMondialRelayShipmentJob;
use App\Models\Order;
use App\Models\OrderShipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ShipmentController extends Controller
{
    public function create(Order $order): JsonResponse
    {
        $shipment = $order->shipments()->firstOrFail();
        if (in_array($shipment->status, ['queued', 'creating'], true)) {
            return response()->json(['data' => ['shipment_id' => $shipment->id, 'status' => $shipment->status]], 202);
        }

        abort_unless(in_array($shipment->status, ['pending', 'creation_failed', 'label_failed'], true), 409, 'This shipment cannot be generated again.');
        $shipment->forceFill(['status' => 'queued', 'last_error' => null])->save();
        CreateMondialRelayShipmentJob::dispatch($shipment->id);
        return response()->json(['data' => ['shipment_id' => $shipment->id, 'status' => 'queued']], 202);
    }

    public function label(Order $order, OrderShipment $shipment)
    {
        abort_unless($shipment->order_id === $order->id, 404);
        abort_unless($shipment->label_path && Storage::disk((string) config('shipping.label_disk'))->exists($shipment->label_path), 404);
        return Storage::disk((string) config('shipping.label_disk'))->download($shipment->label_path, 'etiquette-'.$order->order_number.'.pdf', ['Content-Type' => 'application/pdf']);
    }
}
