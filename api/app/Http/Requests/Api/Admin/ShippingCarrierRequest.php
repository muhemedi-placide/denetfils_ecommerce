<?php

namespace App\Http\Requests\Api\Admin;

use App\Support\ShippingCarrierCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ShippingCarrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('payments.manage') ?? false;
    }

    public function rules(): array
    {
        $shippingCarrier = $this->route('shippingCarrier');
        $provider = (string) $this->input('provider', $shippingCarrier?->provider ?? '');
        $schema = ShippingCarrierCatalog::provider($provider);
        $environments = $schema['environments'] ?? ['sandbox', 'live'];
        $deliveryModes = collect($schema['delivery_modes'] ?? [])->pluck('key')->all();

        return [
            'code' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:64',
                'regex:/^[a-z0-9][a-z0-9_-]*$/',
                Rule::unique('shipping_carriers', 'code')->ignore($shippingCarrier?->id),
            ],
            'provider' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                Rule::in(ShippingCarrierCatalog::providerKeys()),
            ],
            'display_name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'array'],
            'display_name.fr' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:120'],
            'display_name.en' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'array'],
            'description.fr' => ['nullable', 'string', 'max:500'],
            'description.en' => ['nullable', 'string', 'max:500'],
            'environment' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', Rule::in($environments)],
            'status' => ['sometimes', 'string', Rule::in(ShippingCarrierCatalog::STATUSES)],
            'is_enabled' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'delivery_modes' => ['nullable', 'array'],
            'delivery_modes.*' => ['string', Rule::in($deliveryModes)],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'size:2'],
            'max_weight_grams' => ['nullable', 'integer', 'min:1', 'max:70000'],
            'supports_relay_points' => ['sometimes', 'boolean'],
            'supports_home_delivery' => ['sometimes', 'boolean'],
            'public_config' => ['nullable', 'array'],
            'public_config.*' => ['nullable'],
            'credentials' => [$this->isMethod('post') ? 'required' : 'sometimes', 'array'],
            'credentials.*' => ['nullable'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $shippingCarrier = $this->route('shippingCarrier');
                $provider = (string) $this->input('provider', $shippingCarrier?->provider ?? '');
                $schema = ShippingCarrierCatalog::provider($provider);

                if (! $schema) {
                    return;
                }

                $credentials = array_replace(
                    $shippingCarrier?->credentials ?? [],
                    $this->input('credentials', []),
                );

                $allowedCredentialKeys = collect($schema['credential_fields'])->pluck('key')->all();
                $unknownKeys = array_diff(array_keys($credentials), $allowedCredentialKeys);

                foreach ($unknownKeys as $key) {
                    $validator->errors()->add("credentials.{$key}", 'This credential field is not supported by the selected carrier.');
                }

                foreach ($schema['credential_fields'] as $field) {
                    if (($field['required'] ?? false) && blank($credentials[$field['key']] ?? null)) {
                        $validator->errors()->add("credentials.{$field['key']}", 'This credential field is required for the selected carrier.');
                    }
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'countries' => $this->normalizeUpperList($this->input('countries')),
        ]);
    }

    private function normalizeUpperList(mixed $values): mixed
    {
        if (is_string($values)) {
            $values = preg_split('/[,;\s]+/', $values) ?: [];
        }

        if (! is_array($values)) {
            return $values;
        }

        return collect($values)
            ->map(fn ($value) => strtoupper(trim((string) $value)))
            ->filter()
            ->values()
            ->all();
    }
}
