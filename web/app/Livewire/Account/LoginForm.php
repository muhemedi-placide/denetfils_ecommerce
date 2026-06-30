<?php

namespace App\Livewire\Account;

use App\Services\AccountApiClient;
use Livewire\Component;

class LoginForm extends Component
{
    public string $locale = 'fr';

    public string $email = '';

    public string $password = '';

    public function login(AccountApiClient $api)
    {
        $validated = $this->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $response = $api->login([
            ...$validated,
            'device_name' => \Illuminate\Support\Str::slug(config('shop.name')).'-web',
        ]);

        if (! $response['ok']) {
            $this->applyApiErrors($response, 'email');

            return null;
        }

        session()->regenerate();
        session()->put('customer_api_token', $response['data']['token'] ?? null);
        session()->put('customer_user', $response['data']['user'] ?? []);
        session()->flash('status', __('home.account.auth.login_success'));

        return $this->redirectRoute('account.show', ['locale' => $this->locale], navigate: true);
    }

    public function render()
    {
        return view('livewire.account.login-form');
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
