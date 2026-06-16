<?php

namespace App\Services\Logistics;

use App\Models\ShippingCarrier;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Support\ShippingCarrierCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShippingCarrierManagementService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function create(array $data, User $actor, Request $request): ShippingCarrier
    {
        return DB::transaction(function () use ($data, $actor, $request) {
            $carrier = ShippingCarrier::create($this->attributes($data));
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
        $message = $missing === []
            ? 'La configuration Mondial Relay est structurellement complete. Aucun appel externe n est execute depuis ce test admin.'
            : 'Identifiants manquants: '.implode(', ', $missing).'.';

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
            'external_call_executed' => false,
            'signature_strategy' => 'Mondial Relay SOAP: MD5 majuscule des parametres ordonnes et de la cle privee.',
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
            ...Arr::except($data, ['credentials', 'public_config']),
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
}
