<?php

namespace App\Jobs\Shipping;

use App\Models\OrderShipment;
use App\Services\Shipping\MondialRelay\MondialRelayRejectedException;
use App\Services\Shipping\ShippingManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CreateMondialRelayShipmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public int $shipmentId) {}

    public function handle(ShippingManager $shipping): void
    {
        $shipment = OrderShipment::query()->with(['order.items', 'order.addresses', 'carrier', 'method', 'pickupPoint'])->findOrFail($this->shipmentId);
        $provider = $shipping->provider($shipment->carrier->provider);
        try {
            $shipment->forceFill(['status' => 'creating', 'last_error' => null])->save();
            $created = $provider->createShipment($shipment);
            $shipment->forceFill(['external_shipment_id' => $created->externalId, 'tracking_number' => $created->trackingNumber, 'status' => $created->status, 'raw_payload' => $created->rawPayload, 'last_error' => null])->save();
        } catch (MondialRelayRejectedException $exception) {
            $shipment->forceFill([
                'status' => 'creation_failed',
                'last_error' => $this->safeMessage($exception),
                'raw_payload' => [
                    ...($shipment->raw_payload ?? []),
                    'creation_rejection' => [
                        'operation' => $exception->operation,
                        'status' => $exception->status,
                        'payload' => $exception->payload,
                    ],
                ],
            ])->save();
            Log::warning('Shipping creation rejected by carrier.', ['shipment_id' => $shipment->id, 'provider' => $shipment->carrier->provider, 'status' => $exception->status]);
            return;
        } catch (Throwable $exception) {
            $shipment->forceFill(['status' => 'creation_failed', 'last_error' => $this->safeMessage($exception)])->save();
            Log::warning('Shipping creation failed.', ['shipment_id' => $shipment->id, 'provider' => $shipment->carrier->provider, 'exception' => $exception::class]);
            throw $exception;
        }

        try {
            $contents = $provider->downloadLabel($shipment->refresh());
            $path = trim((string) config('shipping.label_directory'), '/').'/'.$shipment->order->order_number.'-'.$shipment->id.'.pdf';
            Storage::disk((string) config('shipping.label_disk'))->put($path, $contents);
            $shipment->forceFill(['label_path' => $path, 'status' => 'label_ready'])->save();
        } catch (Throwable $exception) {
            $shipment->forceFill(['status' => 'label_failed', 'last_error' => $this->safeMessage($exception)])->save();
            Log::warning('Shipping label generation failed.', ['shipment_id' => $shipment->id, 'provider' => $shipment->carrier->provider, 'exception' => $exception::class]);
        }
    }

    private function safeMessage(Throwable $exception): string { return mb_substr(preg_replace('/[A-Z0-9]{20,}/', '[redacted]', $exception->getMessage()), 0, 1000); }
}
