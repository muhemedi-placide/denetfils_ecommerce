<?php

namespace App\Livewire\Account;

use App\Services\AccountApiClient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Dashboard extends Component
{
    public string $locale = 'fr';

    public array $user = [];

    public array $addresses = [];

    public array $countries = [];

    public array $profile = [];

    public array $newAddress = [];

    public array $addressForms = [];

    public ?string $statusMessage = null;

    public function mount(string $locale, array $user, array $addresses, array $countries): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->user = $user;
        $this->addresses = $addresses;
        $this->countries = $countries;
        $this->profile = $this->profileFromUser($user);
        $this->newAddress = $this->blankAddress();
        $this->syncAddressForms();
    }

    public function updateProfile(AccountApiClient $api): void
    {
        $validated = $this->validate([
            'profile.first_name' => ['required', 'string', 'max:120'],
            'profile.last_name' => ['required', 'string', 'max:120'],
            'profile.phone' => ['nullable', 'string', 'max:32'],
            'profile.preferred_locale' => ['required', Rule::in(['fr', 'en'])],
            'profile.country_code' => ['required', 'string', 'size:2'],
            'profile.timezone' => ['required', 'string', 'max:64'],
        ])['profile'];

        $response = $api->updateMe($this->token(), $validated);

        if (! $response['ok']) {
            $this->applyApiErrors($response, 'profile.first_name');

            return;
        }

        $this->user = $response['data'];
        $this->profile = $this->profileFromUser($this->user);
        session()->put('customer_user', $this->user);
        $this->success(__('home.account.profile.updated'));
    }

    public function createAddress(AccountApiClient $api): void
    {
        $payload = $this->validateAddressPayload($this->newAddress, 'newAddress');
        $response = $api->createAddress($this->token(), $payload);

        if (! $response['ok']) {
            $this->applyApiErrors($response, 'newAddress.recipient_name');

            return;
        }

        $this->refreshAddresses($api);
        $this->newAddress = $this->blankAddress();
        $this->success(__('home.account.addresses.created'));
    }

    public function updateAddress(AccountApiClient $api, int $addressId): void
    {
        $payload = $this->validateAddressPayload($this->addressForms[$addressId] ?? [], "addressForms.{$addressId}");
        $response = $api->updateAddress($this->token(), $addressId, $payload);

        if (! $response['ok']) {
            $this->applyApiErrors($response, "addressForms.{$addressId}.recipient_name");

            return;
        }

        $this->refreshAddresses($api);
        $this->success(__('home.account.addresses.updated'));
    }

    public function deleteAddress(AccountApiClient $api, int $addressId): void
    {
        $response = $api->deleteAddress($this->token(), $addressId);

        if (! $response['ok']) {
            $this->applyApiErrors($response, "addressForms.{$addressId}.recipient_name");

            return;
        }

        $this->refreshAddresses($api);
        $this->success(__('home.account.addresses.deleted'));
    }

    public function logout(AccountApiClient $api)
    {
        $token = $this->token();

        if ($token) {
            $api->logout($token);
        }

        session()->forget(['customer_api_token', 'customer_user']);
        session()->flash('status', __('home.account.auth.logout_success'));

        return $this->redirectRoute('home.localized', ['locale' => $this->locale], navigate: true);
    }

    public function render()
    {
        return view('livewire.account.dashboard', [
            'roles' => collect($this->user['roles'] ?? [])->implode(', '),
            'timezones' => [
                'Europe/Paris',
                'Europe/Brussels',
                'Europe/Berlin',
                'Europe/Amsterdam',
                'Europe/Madrid',
                'Europe/Rome',
                'Europe/Luxembourg',
                'Europe/Lisbon',
            ],
        ]);
    }

    private function refreshAddresses(AccountApiClient $api): void
    {
        $response = $api->addresses($this->token());

        if ($response['ok']) {
            $this->addresses = $response['data'];
            $this->syncAddressForms();
        }
    }

    private function validateAddressPayload(array $payload, string $errorPrefix): array
    {
        $validator = Validator::make($payload, [
            'type' => ['required', Rule::in(['billing', 'shipping'])],
            'label' => ['nullable', 'string', 'max:120'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'street_line_1' => ['required', 'string', 'max:255'],
            'street_line_2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:32'],
            'city' => ['required', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:32'],
            'is_default' => ['boolean'],
        ]);

        if ($validator->fails()) {
            $errors = [];

            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors["{$errorPrefix}.{$field}"][] = $message;
                }
            }

            throw ValidationException::withMessages($errors);
        }

        return [
            ...$validator->validated(),
            'is_default' => (bool) ($payload['is_default'] ?? false),
        ];
    }

    private function profileFromUser(array $user): array
    {
        return [
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'phone' => $user['phone'] ?? '',
            'preferred_locale' => $user['preferred_locale'] ?? $this->locale,
            'country_code' => $user['country_code'] ?? 'FR',
            'timezone' => $user['timezone'] ?? 'Europe/Paris',
        ];
    }

    private function blankAddress(): array
    {
        return [
            'type' => 'shipping',
            'label' => '',
            'recipient_name' => trim(($this->user['first_name'] ?? '') . ' ' . ($this->user['last_name'] ?? '')),
            'company' => '',
            'street_line_1' => '',
            'street_line_2' => '',
            'postal_code' => '',
            'city' => '',
            'region' => '',
            'country_code' => $this->user['country_code'] ?? 'FR',
            'phone' => $this->user['phone'] ?? '',
            'is_default' => false,
        ];
    }

    private function syncAddressForms(): void
    {
        $this->addressForms = collect($this->addresses)
            ->mapWithKeys(fn (array $address) => [
                $address['id'] => [
                    'type' => $address['type'] ?? 'shipping',
                    'label' => $address['label'] ?? '',
                    'recipient_name' => $address['recipient_name'] ?? '',
                    'company' => $address['company'] ?? '',
                    'street_line_1' => $address['street_line_1'] ?? '',
                    'street_line_2' => $address['street_line_2'] ?? '',
                    'postal_code' => $address['postal_code'] ?? '',
                    'city' => $address['city'] ?? '',
                    'region' => $address['region'] ?? '',
                    'country_code' => $address['country_code'] ?? 'FR',
                    'phone' => $address['phone'] ?? '',
                    'is_default' => (bool) ($address['is_default'] ?? false),
                ],
            ])
            ->all();
    }

    private function success(string $message): void
    {
        $this->resetErrorBag();
        $this->statusMessage = $message;
    }

    private function token(): ?string
    {
        return session()->get('customer_api_token');
    }

    private function applyApiErrors(array $response, string $fallbackField): void
    {
        if (! empty($response['errors']) && is_array($response['errors'])) {
            foreach ($response['errors'] as $field => $messages) {
                foreach ((array) $messages as $message) {
                    $this->addError($field, $message);
                }
            }

            return;
        }

        $this->addError($fallbackField, $response['message'] ?: __('home.account.api_error'));
    }
}
