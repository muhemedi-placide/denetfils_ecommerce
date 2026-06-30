<?php

namespace App\Http\Controllers;

use App\Services\AccountApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function loginForm(Request $request, AccountApiClient $api, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);

        if ($request->session()->has('customer_api_token')) {
            return redirect()->route('account.show', ['locale' => $locale]);
        }

        return view('auth.login', [
            'locale' => $locale,
            'activeMenu' => 'account',
        ]);
    }

    public function login(Request $request, AccountApiClient $api, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $response = $api->login([
            ...$validated,
            'device_name' => \Illuminate\Support\Str::slug(config('shop.name')).'-web',
        ]);

        if (! $response['ok']) {
            return back()
                ->withErrors($this->responseErrors($response, 'email'))
                ->withInput($request->except('password'));
        }

        $this->storeCustomerSession($request, $response['data']);

        return redirect()
            ->route('account.show', ['locale' => $locale])
            ->with('status', __('home.account.auth.login_success'));
    }

    public function registerForm(AccountApiClient $api, string $locale): View
    {
        $locale = $this->setLocale($locale);

        return view('auth.register', [
            'locale' => $locale,
            'countries' => $api->supportedCountries($locale)['data'],
            'consents' => $api->currentConsents()['data'],
            'activeMenu' => 'account',
        ]);
    }

    public function register(Request $request, AccountApiClient $api, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
            'country_code' => ['required', 'string', 'size:2'],
            'privacy_policy_consent' => ['accepted'],
            'terms_consent' => ['accepted'],
            'marketing_consent' => ['nullable', 'boolean'],
        ]);

        $response = $api->register([
            ...$validated,
            'preferred_locale' => $locale,
            'timezone' => 'Europe/Paris',
            'marketing_consent' => $request->boolean('marketing_consent'),
        ]);

        if (! $response['ok']) {
            return back()
                ->withErrors($this->responseErrors($response, 'email'))
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $this->storeCustomerSession($request, $response['data']);

        return redirect()
            ->route('account.show', ['locale' => $locale])
            ->with('status', __('home.account.auth.register_success'));
    }

    public function logout(Request $request, AccountApiClient $api, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $token = $this->token($request);

        if ($token) {
            $api->logout($token);
        }

        $request->session()->forget(['customer_api_token', 'customer_user', 'customer_shipping_country']);

        return redirect()
            ->route('home.localized', ['locale' => $locale])
            ->with('status', __('home.account.auth.logout_success'));
    }

    public function show(Request $request, AccountApiClient $api, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $token = $this->token($request);

        if (! $token) {
            return $this->loginRedirect($locale);
        }

        $me = $api->me($token);

        if (! $me['ok']) {
            return $this->expiredSessionRedirect($request, $locale);
        }

        $request->session()->put('customer_user', $me['data']);

        $addresses = $api->addresses($token)['data'];
        $shipping = collect($addresses)->where('type', 'shipping');
        $shippingCountry = data_get($shipping->firstWhere('is_default', true) ?? $shipping->first(), 'country_code');
        if ($shippingCountry) {
            $request->session()->put('customer_shipping_country', strtoupper((string) $shippingCountry));
        }

        return view('account.show', [
            'locale' => $locale,
            'user' => $me['data'],
            'addresses' => $addresses,
            'orders' => $api->orders($token, $locale, 5)['data'],
            'countries' => $api->supportedCountries($locale)['data'],
            'activeMenu' => 'account',
        ]);
    }

    public function showOrder(Request $request, AccountApiClient $api, string $locale, int $order): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $token = $this->token($request);

        if (! $token) {
            return $this->loginRedirect($locale);
        }

        $me = $api->me($token);

        if (! $me['ok']) {
            return $this->expiredSessionRedirect($request, $locale);
        }

        $orderResponse = $api->order($token, $order, $locale);

        if (! $orderResponse['ok']) {
            abort(404);
        }

        $conversation = $api->orderConversation($token, $order);

        return view('account.order-show', [
            'locale' => $locale,
            'user' => $me['data'],
            'order' => $orderResponse['data'],
            'conversation' => $conversation['ok'] ? $conversation['data'] : [
                'status' => 'not_started',
                'messages' => [],
                'customer_unread_count' => 0,
            ],
            'activeMenu' => 'account',
        ]);
    }

    public function openOrderDiscussion(Request $request, AccountApiClient $api, string $locale, int $order): RedirectResponse
    {
        return $this->discussionAction($request, $api, $locale, $order, fn (string $token) => $api->openOrderConversation($token, $order));
    }

    public function sendOrderDiscussionMessage(Request $request, AccountApiClient $api, string $locale, int $order): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        return $this->discussionAction($request, $api, $locale, $order, fn (string $token) => $api->sendOrderMessage($token, $order, $validated['body']));
    }

    public function markOrderDiscussionRead(Request $request, AccountApiClient $api, string $locale, int $order): RedirectResponse
    {
        return $this->discussionAction($request, $api, $locale, $order, fn (string $token) => $api->markOrderConversationRead($token, $order));
    }

    public function closeOrderDiscussion(Request $request, AccountApiClient $api, string $locale, int $order): RedirectResponse
    {
        return $this->discussionAction($request, $api, $locale, $order, fn (string $token) => $api->closeOrderConversation($token, $order));
    }

    public function updateProfile(Request $request, AccountApiClient $api, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $token = $this->token($request);

        if (! $token) {
            return $this->loginRedirect($locale);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'preferred_locale' => ['required', Rule::in(['fr', 'en'])],
            'country_code' => ['required', 'string', 'size:2'],
            'timezone' => ['required', 'string', 'max:64'],
        ]);

        $response = $api->updateMe($token, $validated);

        if (! $response['ok']) {
            return back()
                ->withErrors($this->responseErrors($response, 'first_name'))
                ->withInput();
        }

        $request->session()->put('customer_user', $response['data']);

        return back()->with('status', __('home.account.profile.updated'));
    }

    public function storeAddress(Request $request, AccountApiClient $api, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $token = $this->token($request);

        if (! $token) {
            return $this->loginRedirect($locale);
        }

        $payload = $this->validateAddress($request);
        $response = $api->createAddress($token, $payload);

        if (! $response['ok']) {
            return back()
                ->withErrors($this->responseErrors($response, 'recipient_name'))
                ->withInput();
        }

        return back()->with('status', __('home.account.addresses.created'));
    }

    public function updateAddress(Request $request, AccountApiClient $api, string $locale, int $address): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $token = $this->token($request);

        if (! $token) {
            return $this->loginRedirect($locale);
        }

        $payload = $this->validateAddress($request);
        $response = $api->updateAddress($token, $address, $payload);

        if (! $response['ok']) {
            return back()
                ->withErrors($this->responseErrors($response, 'recipient_name'))
                ->withInput();
        }

        return back()->with('status', __('home.account.addresses.updated'));
    }

    public function deleteAddress(Request $request, AccountApiClient $api, string $locale, int $address): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $token = $this->token($request);

        if (! $token) {
            return $this->loginRedirect($locale);
        }

        $response = $api->deleteAddress($token, $address);

        if (! $response['ok']) {
            return back()->withErrors($this->responseErrors($response, 'recipient_name'));
        }

        return back()->with('status', __('home.account.addresses.deleted'));
    }

    private function validateAddress(Request $request): array
    {
        $validated = $request->validate([
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
            'is_default' => ['nullable', 'boolean'],
        ]);

        return [
            ...$validated,
            'is_default' => $request->boolean('is_default'),
        ];
    }

    private function discussionAction(Request $request, AccountApiClient $api, string $locale, int $order, callable $callback): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $token = $this->token($request);

        if (! $token) {
            return $this->loginRedirect($locale);
        }

        $response = $callback($token);

        if (! $response['ok']) {
            return back()
                ->withErrors($this->responseErrors($response, 'body'))
                ->withInput();
        }

        return redirect()
            ->route('account.orders.show', ['locale' => $locale, 'order' => $order])
            ->with('status', $locale === 'en' ? 'Discussion updated.' : 'Discussion mise a jour.');
    }

    private function storeCustomerSession(Request $request, array $data): void
    {
        $request->session()->regenerate();
        $request->session()->put('customer_api_token', $data['token'] ?? null);
        $request->session()->put('customer_user', $data['user'] ?? []);
    }

    private function responseErrors(array $response, string $fallbackField): array
    {
        if (! empty($response['errors']) && is_array($response['errors'])) {
            return $response['errors'];
        }

        return [
            $fallbackField => $response['message'] ?: __('home.account.api_error'),
        ];
    }

    private function token(Request $request): ?string
    {
        return $request->session()->get('customer_api_token');
    }

    private function loginRedirect(string $locale): RedirectResponse
    {
        return redirect()
            ->route('account.login', ['locale' => $locale])
            ->withErrors(['email' => __('home.account.auth.required')]);
    }

    private function expiredSessionRedirect(Request $request, string $locale): RedirectResponse
    {
        $request->session()->forget(['customer_api_token', 'customer_user']);

        return redirect()
            ->route('account.login', ['locale' => $locale])
            ->withErrors(['email' => __('home.account.auth.expired')]);
    }

    private function setLocale(?string $locale): string
    {
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : config('app.locale', 'fr');

        app()->setLocale($locale);

        return $locale;
    }
}
