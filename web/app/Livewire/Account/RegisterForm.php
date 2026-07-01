<?php

namespace App\Livewire\Account;

use App\Services\AccountApiClient;
use Livewire\Component;

class RegisterForm extends Component
{
    public string $locale = 'fr';

    public array $countries = [];

    public array $consents = [];

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public string $country_code = 'FR';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $privacy_policy_consent = false;

    public bool $terms_consent = false;

    public bool $marketing_consent = false;

    public function mount(AccountApiClient $api, string $locale): void
    {
        $this->locale = in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
        $this->countries = $api->supportedCountries($this->locale)['data'];
        $this->consents = $api->currentConsents()['data'];
        $detectedCountry = strtoupper((string) data_get(request()->attributes->get('visitor_context'), 'country_code', 'FR'));
        if (collect($this->countries)->contains('code', $detectedCountry)) {
            $this->country_code = $detectedCountry;
        }
    }

    public function register(AccountApiClient $api)
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
            'country_code' => ['required', 'string', 'size:2'],
            'privacy_policy_consent' => ['accepted'],
            'terms_consent' => ['accepted'],
            'marketing_consent' => ['boolean'],
        ]);

        $response = $api->register([
            ...$validated,
            'preferred_locale' => $this->locale,
            'timezone' => 'Europe/Paris',
        ]);

        if (! $response['ok']) {
            $this->applyApiErrors($response, 'email');

            return null;
        }

        session()->regenerate();
        session()->put('customer_api_token', $response['data']['token'] ?? null);
        session()->put('customer_user', $response['data']['user'] ?? []);
        session()->flash('status', __('home.account.auth.register_success'));

        return $this->redirectRoute('account.show', ['locale' => $this->locale], navigate: true);
    }

    public function render()
    {
        return view('livewire.account.register-form');
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
