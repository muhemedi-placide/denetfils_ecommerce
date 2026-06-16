<?php

namespace App\Http\Requests\Api\Admin;

use App\Support\PaymentProviderCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('payments.manage') ?? false;
    }

    public function rules(): array
    {
        $paymentMethod = $this->route('paymentMethod');
        $provider = (string) $this->input('provider', $paymentMethod?->provider ?? '');
        $schema = PaymentProviderCatalog::provider($provider);
        $environments = $schema['environments'] ?? ['sandbox', 'live', 'manual'];

        return [
            'code' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:64',
                'regex:/^[a-z0-9][a-z0-9_-]*$/',
                Rule::unique('payment_methods', 'code')->ignore($paymentMethod?->id),
            ],
            'provider' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                Rule::in(PaymentProviderCatalog::providerKeys()),
            ],
            'display_name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'array'],
            'display_name.fr' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:120'],
            'display_name.en' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'array'],
            'description.fr' => ['nullable', 'string', 'max:500'],
            'description.en' => ['nullable', 'string', 'max:500'],
            'environment' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', Rule::in($environments)],
            'status' => ['sometimes', 'string', Rule::in(PaymentProviderCatalog::STATUSES)],
            'is_enabled' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'size:2'],
            'currencies' => ['nullable', 'array'],
            'currencies.*' => ['string', 'size:3'],
            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['string', 'max:64'],
            'public_config' => ['nullable', 'array'],
            'credentials' => [$this->isMethod('post') ? 'required' : 'sometimes', 'array'],
            'credentials.*' => ['nullable'],
            'webhook_config' => ['nullable', 'array'],
            'webhook_config.*' => ['nullable'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $paymentMethod = $this->route('paymentMethod');
                $provider = (string) $this->input('provider', $paymentMethod?->provider ?? '');
                $schema = PaymentProviderCatalog::provider($provider);

                if (! $schema) {
                    return;
                }

                $credentials = array_replace(
                    $paymentMethod?->credentials ?? [],
                    $this->input('credentials', []),
                );

                $allowedCredentialKeys = collect($schema['credential_fields'])->pluck('key')->all();
                $unknownKeys = array_diff(array_keys($credentials), $allowedCredentialKeys);

                foreach ($unknownKeys as $key) {
                    $validator->errors()->add("credentials.{$key}", 'This credential field is not supported by the selected provider.');
                }

                foreach ($schema['credential_fields'] as $field) {
                    if (($field['required'] ?? false) && blank($credentials[$field['key']] ?? null)) {
                        $validator->errors()->add("credentials.{$field['key']}", 'This credential field is required for the selected provider.');
                    }
                }

                foreach ($schema['required_any'] ?? [] as $group) {
                    $hasAny = collect($group)->contains(fn (string $key) => filled($credentials[$key] ?? null));

                    if (! $hasAny) {
                        $validator->errors()->add('credentials', 'At least one of these credential fields is required: '.implode(', ', $group).'.');
                    }
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'countries' => $this->normalizeUpperList($this->input('countries')),
            'currencies' => $this->normalizeUpperList($this->input('currencies')),
        ]);
    }

    private function normalizeUpperList(mixed $values): mixed
    {
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
