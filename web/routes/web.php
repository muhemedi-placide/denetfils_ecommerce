<?php

use App\Http\Controllers\Admin\BackOfficeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShopIndexController;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


Route::get('/robots.txt', [ShopController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [ShopController::class, 'sitemap'])->name('seo.sitemap');

Route::get('/', [ShopController::class, 'home'])->name('home');

Route::get('/{locale}/boutique', ShopIndexController::class)
    ->whereIn('locale', ['fr', 'en'])
    ->name('shop.index');

Route::get('/{locale}/about', [ShopController::class, 'about'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.about');

Route::get('/{locale}/contact', ContactController::class)
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.contact');

Route::get('/{locale}/blog', [ShopController::class, 'blog'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('blog.index');

Route::get('/{locale}/blog/{slug}', [ShopController::class, 'blogShow'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('blog.show');

Route::get('/{locale}/livraison', [ShopController::class, 'delivery'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.delivery');

Route::get('/{locale}/mentions-legales', [ShopController::class, 'legalNotice'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.legal');

Route::get('/{locale}/conditions-utilisation', [ShopController::class, 'terms'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.terms');

Route::get('/{locale}/paiement-securise', [ShopController::class, 'securePayment'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.payment');

Route::prefix('/{locale}/admin')
    ->whereIn('locale', ['fr', 'en'])
    ->group(function () {
        Route::get('/connexion', [BackOfficeController::class, 'loginForm'])->name('admin.login');
        Route::post('/connexion', [BackOfficeController::class, 'login'])->name('admin.login.store');
        Route::post('/deconnexion', [BackOfficeController::class, 'logout'])->name('admin.logout');
        Route::get('/', [BackOfficeController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/catalogue', [BackOfficeController::class, 'catalog'])->name('admin.catalog');
        Route::get('/catalogue/produits', [BackOfficeController::class, 'catalogProducts'])->name('admin.catalog.products');
        Route::get('/catalogue/categories', [BackOfficeController::class, 'catalogCategories'])->name('admin.catalog.categories');
        Route::post('/catalogue/produits', [BackOfficeController::class, 'storeProduct'])->name('admin.catalog.products.store');
        Route::patch('/catalogue/produits/{product}/stock', [BackOfficeController::class, 'updateProductStock'])->name('admin.catalog.products.stock');
        Route::post('/catalogue/produits/{product}/publication', [BackOfficeController::class, 'setProductPublication'])->name('admin.catalog.products.publication');
        Route::post('/catalogue/categories', [BackOfficeController::class, 'storeCategory'])->name('admin.catalog.categories.store');
        Route::post('/catalogue/categories/{category}/activation', [BackOfficeController::class, 'setCategoryActivation'])->name('admin.catalog.categories.activation');
        Route::get('/commandes', [BackOfficeController::class, 'orders'])->name('admin.orders');
        Route::post('/commandes', [BackOfficeController::class, 'storeOrder'])->name('admin.orders.store');
        Route::get('/commandes/{order}', [BackOfficeController::class, 'showOrder'])->name('admin.orders.show');
        Route::get('/commandes/{order}/impression', [BackOfficeController::class, 'printOrder'])->name('admin.orders.print');
        Route::get('/commandes/{order}/facture', [BackOfficeController::class, 'downloadOrderInvoice'])->name('admin.orders.invoice');
        Route::get('/commandes/{order}/bon-livraison', [BackOfficeController::class, 'downloadOrderDeliveryNote'])->name('admin.orders.delivery-note');
        Route::patch('/commandes/{order}', [BackOfficeController::class, 'updateOrder'])->name('admin.orders.update');
        Route::get('/stock', [BackOfficeController::class, 'inventory'])->name('admin.inventory');
        Route::get('/utilisateurs', [BackOfficeController::class, 'users'])->name('admin.users');
        Route::post('/utilisateurs', [BackOfficeController::class, 'storeUser'])->name('admin.users.store');
        Route::post('/utilisateurs/{user}/roles', [BackOfficeController::class, 'assignUserRoles'])->name('admin.users.roles');
        Route::post('/utilisateurs/{user}/suspension', [BackOfficeController::class, 'suspendUser'])->name('admin.users.suspend');
        Route::get('/acces', [BackOfficeController::class, 'access'])->name('admin.access');
        Route::get('/audit', [BackOfficeController::class, 'audit'])->name('admin.audit');

        Route::get('/modules/livraison', function (Request $request, string $locale) {
            $token = $request->session()->get('admin_api_token');

            if (! $token) {
                return redirect()->route('admin.login', ['locale' => $locale]);
            }

            $response = Http::baseUrl(rtrim((string) config('services.denetfils_api.base_url'), '/'))
                ->acceptJson()
                ->withToken($token)
                ->timeout(10)
                ->get('admin/shipping-carriers', ['per_page' => 50]);

            return view('admin.delivery', [
                'locale' => $locale,
                'adminUser' => $request->session()->get('admin_user', []),
                'activeAdmin' => 'customize.delivery',
                'filters' => $request->only(['q', 'provider', 'environment', 'status']),
                'carriers' => [
                    'ok' => $response->successful(),
                    'data' => $response->json('data', []),
                    'meta' => $response->json('meta', []),
                ],
                'carrierSchemas' => ['ok' => true, 'data' => []],
            ]);
        })->name('admin.delivery');

        Route::post('/modules/livraison/transporteurs', function (Request $request, string $locale) {
            $token = $request->session()->get('admin_api_token');

            if (! $token) {
                return redirect()->route('admin.login', ['locale' => $locale]);
            }

            $validated = $request->validate([
                'code' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9][a-z0-9_-]*$/'],
                'provider' => ['required', Rule::in(['mondial_relay'])],
                'display_name.fr' => ['required', 'string', 'max:120'],
                'display_name.en' => ['nullable', 'string', 'max:120'],
                'environment' => ['required', Rule::in(['sandbox', 'live'])],
                'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],
                'countries' => ['nullable', 'string', 'max:255'],
                'delivery_modes' => ['nullable', 'array'],
                'delivery_modes.*' => ['string', Rule::in(['24R', '24L', 'HOM'])],
                'max_weight_grams' => ['nullable', 'integer', 'min:1', 'max:70000'],
                'credentials.enseigne' => ['required', 'string', 'max:120'],
                'credentials.private_key' => ['required', 'string', 'max:255'],
                'credentials.brand_code' => ['nullable', 'string', 'max:120'],
                'credentials.account_number' => ['nullable', 'string', 'max:120'],
                'credentials.api_endpoint' => ['nullable', 'url', 'max:2048'],
            ]);

            $countries = collect(preg_split('/[,;\s]+/', (string) ($validated['countries'] ?? '')) ?: [])
                ->map(fn($country) => strtoupper(trim((string) $country)))
                ->filter()
                ->values()
                ->all();

            $payload = [
                'code' => $validated['code'],
                'provider' => $validated['provider'],
                'display_name' => [
                    'fr' => data_get($validated, 'display_name.fr'),
                    'en' => data_get($validated, 'display_name.en') ?: data_get($validated, 'display_name.fr'),
                ],
                'environment' => $validated['environment'],
                'status' => $validated['status'],
                'is_enabled' => $request->boolean('is_enabled'),
                'delivery_modes' => array_values($validated['delivery_modes'] ?? ['24R']),
                'countries' => $countries,
                'max_weight_grams' => isset($validated['max_weight_grams']) ? (int) $validated['max_weight_grams'] : null,
                'supports_relay_points' => $request->boolean('supports_relay_points', true),
                'supports_home_delivery' => $request->boolean('supports_home_delivery'),
                'public_config' => [
                    'api_endpoint' => data_get($validated, 'credentials.api_endpoint'),
                    'tracking_url' => 'https://www.mondialrelay.fr/suivi-de-colis/',
                ],
                'credentials' => array_filter(data_get($validated, 'credentials', []), fn($value) => filled($value)),
            ];

            $response = Http::baseUrl(rtrim((string) config('services.denetfils_api.base_url'), '/'))
                ->acceptJson()
                ->withToken($token)
                ->timeout(10)
                ->post('admin/shipping-carriers', $payload);

            if ($response->successful()) {
                return redirect()->route('admin.delivery', ['locale' => $locale])
                    ->with('admin_success', $locale === 'en' ? 'Carrier added.' : 'Transporteur ajouté.');
            }

            return back()
                ->withErrors($response->json('errors', ['carrier' => $response->json('message', 'Action impossible.')]))
                ->withInput($request->except('credentials.private_key'));
        })->name('admin.delivery.carriers.store');
        Route::get('/modules/livraison', function (Request $request, string $locale) {
            $token = $request->session()->get('admin_api_token');

            if (! $token) {
                return redirect()->route('admin.login', ['locale' => $locale]);
            }

            $response = Http::baseUrl(rtrim((string) config('services.denetfils_api.base_url'), '/'))
                ->acceptJson()
                ->withToken($token)
                ->timeout(10)
                ->get('admin/shipping-carriers', ['per_page' => 50]);

            return view('admin.delivery', [
                'locale' => $locale,
                'adminUser' => $request->session()->get('admin_user', []),
                'activeAdmin' => 'customize.delivery',
                'filters' => $request->only(['q', 'provider', 'environment', 'status']),
                'carriers' => [
                    'ok' => $response->successful(),
                    'data' => $response->json('data', []),
                    'meta' => $response->json('meta', []),
                ],
                'carrierSchemas' => ['ok' => true, 'data' => []],
            ]);
        })->name('admin.delivery');

        Route::post('/modules/livraison/transporteurs', function (Request $request, string $locale) {
            $token = $request->session()->get('admin_api_token');

            if (! $token) {
                return redirect()->route('admin.login', ['locale' => $locale]);
            }

            $validated = $request->validate([
                'code' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9][a-z0-9_-]*$/'],
                'provider' => ['required', Rule::in(['mondial_relay'])],
                'display_name.fr' => ['required', 'string', 'max:120'],
                'display_name.en' => ['nullable', 'string', 'max:120'],
                'environment' => ['required', Rule::in(['sandbox', 'live'])],
                'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],
                'countries' => ['nullable', 'string', 'max:255'],
                'delivery_modes' => ['nullable', 'array'],
                'delivery_modes.*' => ['string', Rule::in(['24R', '24L', 'HOM'])],
                'max_weight_grams' => ['nullable', 'integer', 'min:1', 'max:70000'],
                'credentials.enseigne' => ['required', 'string', 'max:120'],
                'credentials.private_key' => ['required', 'string', 'max:255'],
                'credentials.brand_code' => ['nullable', 'string', 'max:120'],
                'credentials.account_number' => ['nullable', 'string', 'max:120'],
                'credentials.api_endpoint' => ['nullable', 'url', 'max:2048'],
            ]);

            $countries = collect(preg_split('/[,;\s]+/', (string) ($validated['countries'] ?? '')) ?: [])
                ->map(fn($country) => strtoupper(trim((string) $country)))
                ->filter()
                ->values()
                ->all();

            $payload = [
                'code' => $validated['code'],
                'provider' => $validated['provider'],
                'display_name' => [
                    'fr' => data_get($validated, 'display_name.fr'),
                    'en' => data_get($validated, 'display_name.en') ?: data_get($validated, 'display_name.fr'),
                ],
                'environment' => $validated['environment'],
                'status' => $validated['status'],
                'is_enabled' => $request->boolean('is_enabled'),
                'delivery_modes' => array_values($validated['delivery_modes'] ?? ['24R']),
                'countries' => $countries,
                'max_weight_grams' => isset($validated['max_weight_grams']) ? (int) $validated['max_weight_grams'] : null,
                'supports_relay_points' => $request->boolean('supports_relay_points', true),
                'supports_home_delivery' => $request->boolean('supports_home_delivery'),
                'public_config' => [
                    'api_endpoint' => data_get($validated, 'credentials.api_endpoint'),
                    'tracking_url' => 'https://www.mondialrelay.fr/suivi-de-colis/',
                ],
                'credentials' => array_filter(data_get($validated, 'credentials', []), fn($value) => filled($value)),
            ];

            $response = Http::baseUrl(rtrim((string) config('services.denetfils_api.base_url'), '/'))
                ->acceptJson()
                ->withToken($token)
                ->timeout(10)
                ->post('admin/shipping-carriers', $payload);

            if ($response->successful()) {
                return redirect()->route('admin.delivery', ['locale' => $locale])
                    ->with('admin_success', $locale === 'en' ? 'Carrier added.' : 'Transporteur ajouté.');
            }

            return back()
                ->withErrors($response->json('errors', ['carrier' => $response->json('message', 'Action impossible.')]))
                ->withInput($request->except('credentials.private_key'));
        })->name('admin.delivery.carriers.store');
        Route::get('/modules/{module}', [BackOfficeController::class, 'modulePage'])->name('admin.modules.show');
    });

Route::get('/{locale}/connexion', [CustomerAccountController::class, 'loginForm'])->whereIn('locale', ['fr', 'en'])->name('account.login');
Route::post('/{locale}/connexion', [CustomerAccountController::class, 'login'])->whereIn('locale', ['fr', 'en'])->name('account.login.store');
Route::get('/{locale}/inscription', [CustomerAccountController::class, 'registerForm'])->whereIn('locale', ['fr', 'en'])->name('account.register');
Route::post('/{locale}/inscription', [CustomerAccountController::class, 'register'])->whereIn('locale', ['fr', 'en'])->name('account.register.store');
Route::post('/{locale}/deconnexion', [CustomerAccountController::class, 'logout'])->whereIn('locale', ['fr', 'en'])->name('account.logout');
Route::get('/{locale}/mon-compte', [CustomerAccountController::class, 'show'])->whereIn('locale', ['fr', 'en'])->name('account.show');
Route::patch('/{locale}/mon-compte', [CustomerAccountController::class, 'updateProfile'])->whereIn('locale', ['fr', 'en'])->name('account.update');
Route::post('/{locale}/mon-compte/adresses', [CustomerAccountController::class, 'storeAddress'])->whereIn('locale', ['fr', 'en'])->name('account.addresses.store');
Route::patch('/{locale}/mon-compte/adresses/{address}', [CustomerAccountController::class, 'updateAddress'])->whereIn('locale', ['fr', 'en'])->name('account.addresses.update');
Route::delete('/{locale}/mon-compte/adresses/{address}', [CustomerAccountController::class, 'deleteAddress'])->whereIn('locale', ['fr', 'en'])->name('account.addresses.delete');
Route::get('/{locale}/panier', [ShopController::class, 'cart'])->whereIn('locale', ['fr', 'en'])->name('cart.show');
Route::get('/{locale}/commande', [ShopController::class, 'checkout'])->whereIn('locale', ['fr', 'en'])->name('checkout.show');
Route::get('/{locale}/products/{slug}', [ShopController::class, 'show'])->whereIn('locale', ['fr', 'en'])->name('products.show');
Route::get('/{locale}', [ShopController::class, 'home'])->whereIn('locale', ['fr', 'en'])->name('home.localized');
