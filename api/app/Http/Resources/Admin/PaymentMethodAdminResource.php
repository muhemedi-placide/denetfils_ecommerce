<?php

namespace App\Http\Resources\Admin;

use App\Support\PaymentProviderCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $provider = PaymentProviderCatalog::provider($this->provider) ?? [];

        return [
            'id' => $this->id,
            'code' => $this->code,
            'provider' => $this->provider,
            'provider_name' => $provider['name'] ?? $this->provider,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'environment' => $this->environment,
            'status' => $this->status,
            'is_enabled' => $this->is_enabled,
            'sort_order' => $this->sort_order,
            'countries' => $this->countries ?? [],
            'currencies' => $this->currencies ?? [],
            'capabilities' => $this->capabilities ?? ($provider['capabilities'] ?? []),
            'public_config' => $this->public_config ?? [],
            'credentials' => [
                'configured' => $this->configuredCredentialKeys(),
                'missing_required' => $this->missingRequiredCredentialKeys(),
                'masked' => $this->maskedCredentials(),
            ],
            'webhook_config' => [
                'configured' => array_keys(array_filter($this->webhook_config ?? [], fn ($value) => filled($value))),
                'masked' => $this->maskValues($this->webhook_config ?? []),
            ],
            'last_tested_at' => $this->last_tested_at?->toIso8601String(),
            'last_test_status' => $this->last_test_status,
            'last_test_message' => $this->last_test_message,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function configuredCredentialKeys(): array
    {
        return array_values(array_keys(array_filter(
            $this->credentials ?? [],
            fn ($value) => filled($value),
        )));
    }

    private function missingRequiredCredentialKeys(): array
    {
        $schema = PaymentProviderCatalog::provider($this->provider);

        if (! $schema) {
            return [];
        }

        $credentials = $this->credentials ?? [];
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

    private function maskedCredentials(): array
    {
        $credentials = $this->credentials ?? [];
        $schema = PaymentProviderCatalog::provider($this->provider);
        $secretKeys = collect($schema['credential_fields'] ?? [])
            ->filter(fn (array $field) => $field['secret'] ?? false)
            ->pluck('key')
            ->all();

        return collect($credentials)
            ->map(fn ($value, string $key) => in_array($key, $secretKeys, true) ? $this->mask((string) $value) : $value)
            ->all();
    }

    private function maskValues(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => filled($value) ? $this->mask((string) $value) : $value)
            ->all();
    }

    private function mask(string $value): string
    {
        if ($value === '') {
            return '';
        }

        return str_repeat('*', max(8, min(16, strlen($value))));
    }
}
