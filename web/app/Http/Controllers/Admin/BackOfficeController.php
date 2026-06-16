<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AccountApiClient;
use App\Services\AdminApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackOfficeController extends Controller
{
    public function loginForm(Request $request, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);

        if ($this->token($request)) {
            return redirect()->route('admin.dashboard', ['locale' => $locale]);
        }

        return view('admin.login', [
            'locale' => $locale,
            'activeAdmin' => 'login',
        ]);
    }

    public function login(Request $request, AccountApiClient $accounts, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $response = $accounts->login([
            ...$validated,
            'device_name' => 'denetfils-admin-web',
        ]);

        if (! $response['ok']) {
            return back()
                ->withErrors($this->responseErrors($response, 'email'))
                ->withInput($request->except('password'));
        }

        $user = $response['data']['user'] ?? [];
        $roles = collect($user['roles'] ?? []);

        if ($roles->isEmpty() || ($roles->count() === 1 && $roles->contains('customer'))) {
            return back()
                ->withErrors(['email' => 'Ce compte ne dispose pas d un acces back-office.'])
                ->withInput($request->except('password'));
        }

        $request->session()->regenerate();
        $request->session()->put('admin_api_token', $response['data']['token'] ?? null);
        $request->session()->put('admin_user', $user);

        return redirect()->route('admin.dashboard', ['locale' => $locale]);
    }

    public function logout(Request $request, AccountApiClient $accounts, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $token = $this->token($request);

        if ($token) {
            $accounts->logout($token);
        }

        $request->session()->forget(['admin_api_token', 'admin_user']);

        return redirect()->route('admin.login', ['locale' => $locale]);
    }

    public function dashboard(Request $request, AdminApiClient $admin, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $threshold = $request->integer('threshold', 5);
        $dashboard = $admin->dashboard($context['token'], $locale, ['threshold' => $threshold]);

        return view('admin.dashboard', $this->payload($context, [
            'activeAdmin' => 'dashboard',
            'dashboard' => $dashboard,
            'threshold' => $threshold,
        ]));
    }

    public function catalog(Request $request, AdminApiClient $admin, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $filters = $request->only(['q', 'category_id', 'publication_status', 'stock_status', 'is_active']);
        $filters['threshold'] = $request->integer('threshold', 5);

        $products = $admin->products($context['token'], $filters);
        $categories = $admin->categories($context['token'], $request->only(['q', 'is_active']));

        return view('admin.catalog', $this->payload($context, [
            'activeAdmin' => 'catalog',
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
        ]));
    }

    public function inventory(Request $request, AdminApiClient $admin, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $filters = $request->only(['q', 'category_id', 'status', 'sort']);
        $filters['threshold'] = $request->integer('threshold', 5);
        $inventory = $admin->inventory($context['token'], $filters);
        $categories = $admin->categories($context['token']);

        return view('admin.inventory', $this->payload($context, [
            'activeAdmin' => 'inventory',
            'inventory' => $inventory,
            'categories' => $categories,
            'filters' => $filters,
        ]));
    }

    public function users(Request $request, AdminApiClient $admin, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $filters = $request->only(['q', 'status', 'role', 'country_code']);
        $users = $admin->users($context['token'], $filters);
        $roles = $admin->roles($context['token']);

        return view('admin.users', $this->payload($context, [
            'activeAdmin' => 'users',
            'users' => $users,
            'roles' => $roles,
            'filters' => $filters,
        ]));
    }

    public function access(Request $request, AdminApiClient $admin, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return view('admin.access', $this->payload($context, [
            'activeAdmin' => 'access',
            'roles' => $admin->roles($context['token']),
            'permissions' => $admin->permissions($context['token']),
        ]));
    }

    public function audit(Request $request, AdminApiClient $admin, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $filters = $request->only(['action', 'actor_id', 'auditable_type']);

        return view('admin.audit', $this->payload($context, [
            'activeAdmin' => 'audit',
            'auditLogs' => $admin->auditLogs($context['token'], $filters),
            'filters' => $filters,
        ]));
    }

    private function context(Request $request, AdminApiClient $admin, string $locale): array|RedirectResponse
    {
        $token = $this->token($request);

        if (! $token) {
            return redirect()->route('admin.login', ['locale' => $locale]);
        }

        $user = $request->session()->get('admin_user', []);

        if (empty($user)) {
            $me = $admin->me($token);

            if (! $me['ok']) {
                $request->session()->forget(['admin_api_token', 'admin_user']);

                return redirect()->route('admin.login', ['locale' => $locale]);
            }

            $user = $me['data'];
            $request->session()->put('admin_user', $user);
        }

        return compact('token', 'user', 'locale');
    }

    private function payload(array $context, array $data): array
    {
        return [
            ...$data,
            'locale' => $context['locale'],
            'adminUser' => $context['user'],
        ];
    }

    private function token(Request $request): ?string
    {
        return $request->session()->get('admin_api_token');
    }

    private function responseErrors(array $response, string $fallbackField): array
    {
        if (! empty($response['errors']) && is_array($response['errors'])) {
            return $response['errors'];
        }

        return [$fallbackField => $response['message'] ?: 'Connexion impossible.'];
    }

    private function setLocale(?string $locale): string
    {
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : config('app.locale', 'fr');

        app()->setLocale($locale);

        return $locale;
    }
}
