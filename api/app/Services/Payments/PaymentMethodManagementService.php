<?php

namespace App\Services\Payments;

use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Support\PaymentProviderCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PaymentMethodManagementService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function create(array $data, User $actor, Request $request): PaymentMethod
    {
        return DB::transaction(function () use ($data, $actor, $request) {
            $paymentMethod = PaymentMethod::create($this->attributes($data));

            $this->auditLogger->record($actor, 'payments.methods.created', $paymentMethod, $request, [
                'code' => $paymentMethod->code,
                'provider' => $paymentMethod->provider,
                'environment' => $paymentMethod->environment,
            ]);

            return $paymentMethod->refresh();
        });
    }

    public function update(PaymentMethod $paymentMethod, array $data, User $actor, Request $request): PaymentMethod
    {
        return DB::transaction(function () use ($paymentMethod, $data, $actor, $request) {
            $paymentMethod->fill($this->attributes($data, $paymentMethod));
            $changed = array_keys($paymentMethod->getDirty());
            $paymentMethod->save();

            $this->auditLogger->record($actor, 'payments.methods.updated', $paymentMethod, $request, [
                'code' => $paymentMethod->code,
                'provider' => $paymentMethod->provider,
                'changed' => $changed,
            ]);

            return $paymentMethod->refresh();
        });
    }

    public function setEnabled(PaymentMethod $paymentMethod, bool $enabled, User $actor, Request $request): PaymentMethod
    {
        return DB::transaction(function () use ($paymentMethod, $enabled, $actor, $request) {
            $paymentMethod->forceFill([
                'is_enabled' => $enabled,
                'status' => $enabled ? 'active' : 'inactive',
            ])->save();

            $this->auditLogger->record(
                $actor,
                $enabled ? 'payments.methods.activated' : 'payments.methods.deactivated',
                $paymentMethod,
                $request,
                [
                    'code' => $paymentMethod->code,
                    'provider' => $paymentMethod->provider,
                    'is_enabled' => $paymentMethod->is_enabled,
                ],
            );

            return $paymentMethod->refresh();
        });
    }

    public function testConfiguration(PaymentMethod $paymentMethod, User $actor, Request $request): array
    {
        $schema = PaymentProviderCatalog::provider($paymentMethod->provider);
        $missing = $this->missingRequiredCredentials($paymentMethod);
        $status = $missing === [] ? 'ready' : 'missing_credentials';
        $message = $missing === []
            ? 'Credentials structure is complete. External provider call is intentionally not executed yet.'
            : 'Missing required credentials: '.implode(', ', $missing).'.';

        $paymentMethod->forceFill([
            'last_tested_at' => now(),
            'last_test_status' => $status,
            'last_test_message' => $message,
        ])->save();

        $this->auditLogger->record($actor, 'payments.methods.tested', $paymentMethod, $request, [
            'code' => $paymentMethod->code,
            'provider' => $paymentMethod->provider,
            'status' => $status,
            'missing_credentials' => $missing,
        ]);

        return [
            'provider' => $paymentMethod->provider,
            'provider_name' => $schema['name'] ?? $paymentMethod->provider,
            'environment' => $paymentMethod->environment,
            'status' => $status,
            'message' => $message,
            'missing_credentials' => $missing,
            'external_call_executed' => false,
        ];
    }

    private function attributes(array $data, ?PaymentMethod $existing = null): array
    {
        $provider = $data['provider'] ?? $existing?->provider;
        $schema = PaymentProviderCatalog::provider((string) $provider);

        $credentials = array_key_exists('credentials', $data)
            ? array_replace($existing?->credentials ?? [], $data['credentials'] ?? [])
            : $existing?->credentials;

        $webhookConfig = array_key_exists('webhook_config', $data)
            ? array_replace($existing?->webhook_config ?? [], $data['webhook_config'] ?? [])
            : $existing?->webhook_config;

        return array_filter([
            ...Arr::except($data, ['credentials', 'webhook_config']),
            'capabilities' => $data['capabilities'] ?? $existing?->capabilities ?? ($schema['capabilities'] ?? []),
            'credentials' => $credentials,
            'webhook_config' => $webhookConfig,
            'is_enabled' => $data['is_enabled'] ?? $existing?->is_enabled ?? false,
            'status' => $data['status'] ?? $existing?->status ?? 'draft',
            'sort_order' => $data['sort_order'] ?? $existing?->sort_order ?? 0,
        ], fn ($value) => $value !== null);
    }

    private function missingRequiredCredentials(PaymentMethod $paymentMethod): array
    {
        $schema = PaymentProviderCatalog::provider($paymentMethod->provider);

        if (! $schema) {
            return [];
        }

        $credentials = $paymentMethod->credentials ?? [];
        $missing = collect($schema['credential_fields'])
            ->filter(fn (array $field) => ($field['required'] ?? false) && blank($credentials[$field['key']] ?? null))
            ->pluck('key')
            ->values()
            ->all();

        foreach ($schema['required_any'] ?? [] as $group) {
            $hasAny = collect($group)->contains(fn (string $key) => filled($credentials[$key] ?? null));

            if (! $hasAny) {
                $missing[] = implode('|', $group);
            }
        }

        return array_values(array_unique($missing));
    }
}
