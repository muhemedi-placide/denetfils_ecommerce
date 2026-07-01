<?php

namespace App\Services\Logistics;

use App\Models\ShippingCarrier;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Support\ShippingCarrierCatalog;
use App\Services\Shipping\ShippingManager;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShippingCarrierManagementService
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly ShippingManager $shipping) {}

    public function create(array $data, User $actor, Request $request): ShippingCarrier
    {
        return DB::transaction(function () use ($data, $actor, $request) {
            $carrier = ShippingCarrier::create($this->attributes($data));
            if (! empty($data['method'])) {
                $this->createMethod($carrier, $data['method'], $data['countries'] ?? []);
            }
            $this->auditLogger->record($actor, 'shipping.carriers.created', $carrier, $request, [
                'code' => $carrier->code,
                'provider' => $carrier->provider,
                'environment' => $carrier->environment,
            ]);

            return $carrier->refresh();
        });
    }

    public function update(ShippingCarrier $carrier, array $data, User $actor, Request $request): ShippingCarrier
    {
        return DB::transaction(function () use ($carrier, $data, $actor, $request) {
            $carrier->fill($this->attributes($data, $carrier));
            $changed = array_keys($carrier->getDirty());
            $carrier->save();
            $this->auditLogger->record($actor, 'shipping.carriers.updated', $carrier, $request, [
                'code' => $carrier->code,
                'provider' => $carrier->provider,
                'changed' => $changed,
            ]);

            return $carrier->refresh();
        });
    }

    public function setEnabled(ShippingCarrier $carrier, bool $enabled, User $actor, Request $request): ShippingCarrier
    {
        return DB::transaction(function () use ($carrier, $enabled, $actor, $request) {
            $carrier->forceFill([
                'is_enabled' => $enabled,
                'status' => $enabled ? 'active' : 'inactive',
            ])->save();

            $this->auditLogger->record(
                $actor,
                $enabled ? 'shipping.carriers.activated' : 'shipping.carriers.deactivated',
                $carrier,
                $request,
                ['code' => $carrier->code, 'provider' => $carrier->provider, 'is_enabled' => $carrier->is_enabled],
            );

            return $carrier->refresh();
        });
    }

    public function testConfiguration(ShippingCarrier $carrier, User $actor, Request $request): array
    {
        $schema = ShippingCarrierCatalog::provider($carrier->provider);
        $missing = $this->missingRequiredCredentials($carrier);
        $status = $missing === [] ? 'ready' : 'missing_credentials';
        $message = $missing === [] ? 'Configuration prête.' : 'Identifiants manquants: '.implode(', ', $missing).'.';
        $result = null;
        if ($missing === []) {
            try {
                $result = $this->shipping->provider($carrier->provider)->test($carrier);
                $status = 'success';
                $message = $result['message'];
            } catch (\Throwable $exception) {
                $status = 'failed';
                $message = mb_substr($exception->getMessage(), 0, 500);
            }
        }

        $carrier->forceFill([
            'last_tested_at' => now(),
            'last_test_status' => $status,
            'last_test_message' => $message,
        ])->save();

        $this->auditLogger->record($actor, 'shipping.carriers.tested', $carrier, $request, [
            'code' => $carrier->code,
            'provider' => $carrier->provider,
            'status' => $status,
            'missing_credentials' => $missing,
        ]);

        return [
            'provider' => $carrier->provider,
            'provider_name' => $schema['name'] ?? $carrier->provider,
            'environment' => $carrier->environment,
            'status' => $status,
            'message' => $message,
            'missing_credentials' => $missing,
            'external_call_executed' => $missing === [],
            'result' => $result,
            'signature_strategy' => $carrier->provider === 'mondial_relay' ? 'Mondial Relay SOAP: MD5 majuscule des paramètres ordonnés et de la clé privée.' : null,
            'test_reference' => Str::upper(Str::random(10)),
        ];
    }

    private function attributes(array $data, ?ShippingCarrier $existing = null): array
    {
        $provider = $data['provider'] ?? $existing?->provider;
        $schema = ShippingCarrierCatalog::provider((string) $provider);
        $credentials = array_key_exists('credentials', $data)
            ? array_replace($existing?->credentials ?? [], array_filter($data['credentials'] ?? [], fn ($value) => filled($value)))
            : $existing?->credentials;
        $publicConfig = array_key_exists('public_config', $data)
            ? array_replace($existing?->public_config ?? [], array_filter($data['public_config'] ?? [], fn ($value) => $value !== null && $value !== ''))
            : $existing?->public_config ?? ($schema['default_public_config'] ?? []);

        return array_filter([
            ...Arr::except($data, ['credentials', 'public_config', 'method']),
            'delivery_modes' => $data['delivery_modes'] ?? $existing?->delivery_modes ?? collect($schema['delivery_modes'] ?? [])->pluck('key')->all(),
            'credentials' => $credentials,
            'public_config' => $publicConfig,
            'is_enabled' => $data['is_enabled'] ?? $existing?->is_enabled ?? false,
            'status' => $data['status'] ?? $existing?->status ?? 'draft',
            'sort_order' => $data['sort_order'] ?? $existing?->sort_order ?? 0,
            'supports_relay_points' => $data['supports_relay_points'] ?? $existing?->supports_relay_points ?? true,
            'supports_home_delivery' => $data['supports_home_delivery'] ?? $existing?->supports_home_delivery ?? false,
        ], fn ($value) => $value !== null);
    }

    private function missingRequiredCredentials(ShippingCarrier $carrier): array
    {
        $schema = ShippingCarrierCatalog::provider($carrier->provider);

        if (! $schema) {
            return [];
        }

        $credentials = $carrier->credentials ?? [];

        return collect($schema['credential_fields'])
            ->filter(fn (array $field) => ($field['required'] ?? false) && blank($credentials[$field['key']] ?? null))
            ->pluck('key')
            ->values()
            ->all();
    }

    private function createMethod(ShippingCarrier $carrier, array $data, array $countries): void
    {
        $deliveryType = (string) $data['delivery_type'];
        $method = ShippingMethod::query()->create([
            'shipping_carrier_id' => $carrier->id,
            'code' => $data['code'],
            'name' => ['fr' => data_get($data, 'name.fr'), 'en' => data_get($data, 'name.en') ?: data_get($data, 'name.fr')],
            'description' => null,
            'delivery_type' => $deliveryType,
            'service_code' => strtoupper((string) $data['service_code']),
            'is_active' => true,
            'requires_pickup_point' => (bool) ($data['requires_pickup_point'] ?? in_array($deliveryType, ['pickup_point', 'locker'], true)),
            'requires_phone' => true,
            'max_weight_grams' => $carrier->max_weight_grams,
            'min_delivery_days' => $data['min_delivery_days'] ?? null,
            'max_delivery_days' => $data['max_delivery_days'] ?? null,
        ]);

        $zone = ShippingZone::query()->create([
            'name' => $carrier->display_name['fr'].' - '.implode(', ', $countries),
            'countries' => array_values($countries),
            'is_active' => true,
        ]);
        ShippingRate::query()->create([
            'shipping_method_id' => $method->id,
            'shipping_zone_id' => $zone->id,
            'min_weight_grams' => 0,
            'max_weight_grams' => $carrier->max_weight_grams ?: 70000,
            'price_cents' => (int) $data['price_cents'],
            'currency' => strtoupper((string) ($data['currency'] ?? 'EUR')),
            'is_active' => true,
        ]);
    }
}
