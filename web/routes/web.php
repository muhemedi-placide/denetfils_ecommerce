<?php

use App\Http\Controllers\Admin\BackOfficeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShopIndexController;
use App\Http\Controllers\VisitorPreferenceController;
use App\Services\AdminApiClient;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


Route::get('/robots.txt', [ShopController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [ShopController::class, 'sitemap'])->name('seo.sitemap');

Route::get('/', [VisitorPreferenceController::class, 'landing'])->name('home');
Route::post('/preferences/visitor', [VisitorPreferenceController::class, 'update'])->name('visitor.preferences.update');

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

Route::get('/{locale}/suivi-colis', [ShopController::class, 'tracking'])
    ->whereIn('locale', ['fr', 'en'])
    ->name('pages.tracking');

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
        Route::patch('/catalogue/produits/{product}/classe-tva', [BackOfficeController::class, 'updateProductTaxClass'])->name('admin.catalog.products.tax-class');
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
        Route::post('/commandes/{order}/discussion/open', [BackOfficeController::class, 'openOrderDiscussion'])->name('admin.orders.discussion.open');
        Route::post('/commandes/{order}/discussion/messages', [BackOfficeController::class, 'sendOrderDiscussionMessage'])->name('admin.orders.discussion.messages');
        Route::post('/commandes/{order}/discussion/read', [BackOfficeController::class, 'markOrderDiscussionRead'])->name('admin.orders.discussion.read');
        Route::post('/commandes/{order}/discussion/close', [BackOfficeController::class, 'closeOrderDiscussion'])->name('admin.orders.discussion.close');
        Route::post('/commandes/{order}/expedition', function (Request $request, string $locale, int $order) {
            $token = $request->session()->get('admin_api_token');
            if (! $token) return redirect()->route('admin.login', ['locale' => $locale]);

            $response = Http::baseUrl(rtrim((string) config('services.denetfils_api.base_url'), '/'))
                ->acceptJson()
                ->withToken($token)
                ->timeout(20)
                ->post("admin/orders/{$order}/shipment/create");

            if ($response->successful()) {
                return redirect()->route('admin.orders.show', ['locale' => $locale, 'order' => $order])
                    ->with('admin_success', $locale === 'en' ? 'Shipment creation queued.' : 'Creation expedition lancee.');
            }

            return back()->withErrors(['shipment' => $response->json('message', $locale === 'en' ? 'Shipment action failed.' : 'Action expedition impossible.')]);
        })->name('admin.orders.shipment.create');
        Route::get('/commandes/{order}/expeditions/{shipment}/etiquette', function (Request $request, string $locale, int $order, int $shipment) {
            $token = $request->session()->get('admin_api_token');
            if (! $token) return redirect()->route('admin.login', ['locale' => $locale]);

            $response = Http::baseUrl(rtrim((string) config('services.denetfils_api.base_url'), '/'))
                ->withToken($token)
                ->timeout(30)
                ->get("admin/orders/{$order}/shipments/{$shipment}/label");

            if (! $response->successful()) {
                return back()->withErrors(['shipment' => $response->json('message', $locale === 'en' ? 'Label is not available yet.' : 'Etiquette indisponible pour le moment.')]);
            }

            return response($response->body(), 200, [
                'Content-Type' => $response->header('Content-Type', 'application/pdf'),
                'Content-Disposition' => $response->header('Content-Disposition', 'attachment; filename="etiquette-'.$order.'.pdf"'),
            ]);
        })->name('admin.orders.shipment.label');
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
                'provider' => ['required', Rule::in(['mondial_relay', 'chronopost'])],
                'display_name.fr' => ['required', 'string', 'max:120'],
                'display_name.en' => ['nullable', 'string', 'max:120'],
                'environment' => ['required', Rule::in(['sandbox', 'live'])],
                'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],
                'countries' => ['nullable', 'string', 'max:255'],
                'delivery_modes' => ['nullable', 'array'],
                'delivery_modes.*' => ['string', 'max:16'],
                'max_weight_grams' => ['nullable', 'integer', 'min:1', 'max:70000'],
                'credentials.enseigne' => ['nullable', 'required_if:provider,mondial_relay', 'string', 'max:120'],
                'credentials.private_key' => ['nullable', 'required_if:provider,mondial_relay', 'string', 'max:255'],
                'credentials.brand_code' => ['nullable', 'string', 'max:120'],
                'credentials.account_number' => ['nullable', 'string', 'max:120'],
                'credentials.password' => ['nullable', 'string', 'max:255'],
                'credentials.api_endpoint' => ['nullable', 'url', 'max:2048'],
                'credentials.sender_name' => ['nullable', 'string', 'max:120'],
                'credentials.sender_address' => ['nullable', 'string', 'max:180'],
                'credentials.sender_address_2' => ['nullable', 'string', 'max:180'],
                'credentials.sender_postal_code' => ['nullable', 'string', 'max:32'],
                'credentials.sender_city' => ['nullable', 'string', 'max:120'],
                'credentials.sender_country' => ['nullable', 'string', 'size:2'],
                'credentials.sender_phone' => ['nullable', 'string', 'max:40'],
                'credentials.sender_email' => ['nullable', 'email', 'max:180'],
                'method.code' => ['required', 'string', 'max:96', 'regex:/^[a-z0-9][a-z0-9_-]*$/'],
                'method.name.fr' => ['required', 'string', 'max:120'],
                'method.name.en' => ['nullable', 'string', 'max:120'],
                'method.delivery_type' => ['required', Rule::in(['pickup_point', 'locker', 'home'])],
                'method.service_code' => ['required', 'string', 'max:16'],
                'method.price' => ['required', 'numeric', 'min:0', 'max:10000'],
                'method.currency' => ['required', 'string', 'size:3'],
                'method.min_delivery_days' => ['nullable', 'integer', 'min:0', 'max:60'],
                'method.max_delivery_days' => ['nullable', 'integer', 'gte:method.min_delivery_days', 'max:90'],
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
                'delivery_modes' => [strtoupper((string) data_get($validated, 'method.service_code'))],
                'countries' => $countries,
                'max_weight_grams' => isset($validated['max_weight_grams']) ? (int) $validated['max_weight_grams'] : null,
                'supports_relay_points' => in_array(data_get($validated, 'method.delivery_type'), ['pickup_point', 'locker'], true),
                'supports_home_delivery' => data_get($validated, 'method.delivery_type') === 'home',
                'public_config' => [
                    'api_endpoint' => data_get($validated, 'credentials.api_endpoint'),
                    'tracking_url' => $validated['provider'] === 'mondial_relay' ? 'https://www.mondialrelay.fr/suivi-de-colis/' : 'https://www.chronopost.fr/tracking-no-cms/suivi-page',
                ],
                'credentials' => array_filter(data_get($validated, 'credentials', []), fn($value) => filled($value)),
                'method' => [
                    'code' => data_get($validated, 'method.code'),
                    'name' => [
                        'fr' => data_get($validated, 'method.name.fr'),
                        'en' => data_get($validated, 'method.name.en') ?: data_get($validated, 'method.name.fr'),
                    ],
                    'delivery_type' => data_get($validated, 'method.delivery_type'),
                    'service_code' => data_get($validated, 'method.service_code'),
                    'price_cents' => (int) round(((float) data_get($validated, 'method.price')) * 100),
                    'currency' => strtoupper((string) data_get($validated, 'method.currency')),
                    'min_delivery_days' => data_get($validated, 'method.min_delivery_days'),
                    'max_delivery_days' => data_get($validated, 'method.max_delivery_days'),
                    'requires_pickup_point' => in_array(data_get($validated, 'method.delivery_type'), ['pickup_point', 'locker'], true),
                ],
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

        Route::patch('/modules/livraison/transporteurs/{carrier}', function (Request $request, string $locale, int $carrier) {
            $token = $request->session()->get('admin_api_token');
            if (! $token) return redirect()->route('admin.login', ['locale' => $locale]);

            $validated = $request->validate([
                'credentials.sender_name' => ['nullable', 'string', 'max:120'],
                'credentials.sender_address' => ['nullable', 'string', 'max:180'],
                'credentials.sender_address_2' => ['nullable', 'string', 'max:180'],
                'credentials.sender_postal_code' => ['nullable', 'string', 'max:32'],
                'credentials.sender_city' => ['nullable', 'string', 'max:120'],
                'credentials.sender_country' => ['nullable', 'string', 'size:2'],
                'credentials.sender_phone' => ['nullable', 'string', 'max:40'],
                'credentials.sender_email' => ['nullable', 'email', 'max:180'],
            ]);

            $credentials = collect(data_get($validated, 'credentials', []))
                ->map(fn ($value, string $key) => $key === 'sender_country' ? strtoupper((string) $value) : $value)
                ->filter(fn ($value) => filled($value))
                ->all();

            $response = Http::baseUrl(rtrim((string) config('services.denetfils_api.base_url'), '/'))
                ->acceptJson()
                ->withToken($token)
                ->timeout(10)
                ->patch("admin/shipping-carriers/{$carrier}", ['credentials' => $credentials]);

            if ($response->successful()) {
                return redirect()->route('admin.delivery', ['locale' => $locale])
                    ->with('admin_success', $locale === 'en' ? 'Sender configuration updated.' : 'Configuration expediteur mise a jour.');
            }

            return back()->withErrors($response->json('errors', ['carrier' => $response->json('message', $locale === 'en' ? 'Update failed.' : 'Mise a jour impossible.')]));
        })->name('admin.delivery.carriers.update');

        Route::post('/modules/livraison/transporteurs/{carrier}/{action}', function (Request $request, string $locale, int $carrier, string $action) {
            $token = $request->session()->get('admin_api_token');
            if (! $token) return redirect()->route('admin.login', ['locale' => $locale]);
            abort_unless(in_array($action, ['activate', 'deactivate', 'test-connection'], true), 404);
            $response = Http::baseUrl(rtrim((string) config('services.denetfils_api.base_url'), '/'))
                ->acceptJson()->withToken($token)->timeout(20)->post("admin/shipping-carriers/{$carrier}/{$action}");
            if ($response->successful()) {
                $message = $locale === 'en' ? 'Carrier updated.' : 'Transporteur mis à jour.';
                if ($action === 'test-connection') {
                    $message = (string) $response->json('data.message', $message);
                    $count = $response->json('data.result.points_found');
                    if (is_numeric($count)) $message .= ' '.($locale === 'en' ? "({$count} pickup point(s) returned)" : "({$count} point(s) relais retourné(s))");
                }
                return redirect()->route('admin.delivery', ['locale' => $locale])->with('admin_success', $message);
            }
            return back()->withErrors(['carrier' => $response->json('message', $locale === 'en' ? 'Action failed.' : 'Échec de l’action.')]);
        })->whereIn('action', ['activate', 'deactivate', 'test-connection'])->name('admin.delivery.carriers.action');

        Route::get('/modules/paiement', function (Request $request, AdminApiClient $admin, string $locale) {
            $token = $request->session()->get('admin_api_token');

            if (! $token) {
                return redirect()->route('admin.login', ['locale' => $locale]);
            }

            $filters = $request->only(['q', 'provider', 'environment', 'status', 'is_enabled']);

            return view('admin.payment', [
                'locale' => $locale,
                'adminUser' => $request->session()->get('admin_user', []),
                'activeAdmin' => 'customize.payment',
                'filters' => $filters,
                'paymentMethods' => $admin->paymentMethods($token, [...$filters, 'per_page' => 50]),
                'paymentSchemas' => $admin->paymentMethodSchemas($token),
            ]);
        })->name('admin.payment');

        Route::post('/modules/paiement/methodes', function (Request $request, AdminApiClient $admin, string $locale) {
            $token = $request->session()->get('admin_api_token');

            if (! $token) {
                return redirect()->route('admin.login', ['locale' => $locale]);
            }

            $validated = $request->validate([
                'code' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9][a-z0-9_-]*$/'],
                'provider' => ['required', Rule::in(['stripe', 'paypal', 'bank_transfer', 'cash_on_delivery'])],
                'display_name.fr' => ['required', 'string', 'max:120'],
                'display_name.en' => ['nullable', 'string', 'max:120'],
                'description.fr' => ['nullable', 'string', 'max:500'],
                'description.en' => ['nullable', 'string', 'max:500'],
                'environment' => ['required', Rule::in(['sandbox', 'live', 'manual'])],
                'countries' => ['nullable', 'string', 'max:255'],
                'currencies' => ['nullable', 'string', 'max:255'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
                'credentials' => ['nullable', 'array'],
                'credentials.*' => ['nullable', 'string', 'max:4096'],
            ]);

            $splitList = fn (?string $value) => collect(preg_split('/[,;\s]+/', (string) $value) ?: [])
                ->map(fn ($item) => strtoupper(trim((string) $item)))
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
                'description' => array_filter([
                    'fr' => data_get($validated, 'description.fr'),
                    'en' => data_get($validated, 'description.en'),
                ], fn ($value) => filled($value)),
                'environment' => $validated['environment'],
                'status' => 'draft',
                'is_enabled' => false,
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'countries' => $splitList($validated['countries'] ?? 'FR'),
                'currencies' => $splitList($validated['currencies'] ?? 'EUR'),
                'credentials' => collect($validated['credentials'] ?? [])
                    ->filter(fn ($value) => filled($value))
                    ->all(),
            ];

            $response = $admin->createPaymentMethod($token, $payload);

            if ($response['ok']) {
                return redirect()->route('admin.payment', ['locale' => $locale])
                    ->with('admin_success', $locale === 'en' ? 'Payment method created.' : 'Mode de paiement cree.');
            }

            return back()
                ->withErrors($response['errors'] ?: ['payment' => $response['message'] ?: ($locale === 'en' ? 'Payment method creation failed.' : 'Creation du mode de paiement impossible.')])
                ->withInput();
        })->name('admin.payment.methods.store');

        Route::post('/modules/paiement/methodes/{paymentMethod}/{action}', function (Request $request, AdminApiClient $admin, string $locale, int $paymentMethod, string $action) {
            $token = $request->session()->get('admin_api_token');

            if (! $token) {
                return redirect()->route('admin.login', ['locale' => $locale]);
            }

            abort_unless(in_array($action, ['activate', 'deactivate', 'test-connection'], true), 404);

            $response = match ($action) {
                'activate' => $admin->activatePaymentMethod($token, $paymentMethod),
                'deactivate' => $admin->deactivatePaymentMethod($token, $paymentMethod),
                'test-connection' => $admin->testPaymentMethod($token, $paymentMethod),
            };

            if ($response['ok']) {
                $message = match ($action) {
                    'activate' => $locale === 'en' ? 'Payment method enabled.' : 'Mode de paiement active.',
                    'deactivate' => $locale === 'en' ? 'Payment method disabled.' : 'Mode de paiement desactive.',
                    default => data_get($response, 'data.message') ?: ($locale === 'en' ? 'Configuration tested.' : 'Configuration testee.'),
                };

                return redirect()->route('admin.payment', ['locale' => $locale])
                    ->with('admin_success', $message);
            }

            return back()->withErrors($response['errors'] ?: ['payment' => $response['message'] ?: ($locale === 'en' ? 'Payment action failed.' : 'Action paiement impossible.')]);
        })->whereIn('action', ['activate', 'deactivate', 'test-connection'])->name('admin.payment.methods.action');

        Route::get('/modules/{module}', [BackOfficeController::class, 'modulePage'])->name('admin.modules.show');
    });

Route::get('/{locale}/connexion', [CustomerAccountController::class, 'loginForm'])->whereIn('locale', ['fr', 'en'])->name('account.login');
Route::post('/{locale}/connexion', [CustomerAccountController::class, 'login'])->whereIn('locale', ['fr', 'en'])->name('account.login.store');
Route::get('/{locale}/inscription', [CustomerAccountController::class, 'registerForm'])->whereIn('locale', ['fr', 'en'])->name('account.register');
Route::post('/{locale}/inscription', [CustomerAccountController::class, 'register'])->whereIn('locale', ['fr', 'en'])->name('account.register.store');
Route::post('/{locale}/deconnexion', [CustomerAccountController::class, 'logout'])->whereIn('locale', ['fr', 'en'])->name('account.logout');
Route::get('/{locale}/mon-compte', [CustomerAccountController::class, 'show'])->whereIn('locale', ['fr', 'en'])->name('account.show');
Route::patch('/{locale}/mon-compte', [CustomerAccountController::class, 'updateProfile'])->whereIn('locale', ['fr', 'en'])->name('account.update');
Route::get('/{locale}/mon-compte/commandes/{order}', [CustomerAccountController::class, 'showOrder'])->whereIn('locale', ['fr', 'en'])->name('account.orders.show');
Route::post('/{locale}/mon-compte/commandes/{order}/discussion/open', [CustomerAccountController::class, 'openOrderDiscussion'])->whereIn('locale', ['fr', 'en'])->name('account.orders.discussion.open');
Route::post('/{locale}/mon-compte/commandes/{order}/discussion/messages', [CustomerAccountController::class, 'sendOrderDiscussionMessage'])->whereIn('locale', ['fr', 'en'])->name('account.orders.discussion.messages');
Route::post('/{locale}/mon-compte/commandes/{order}/discussion/read', [CustomerAccountController::class, 'markOrderDiscussionRead'])->whereIn('locale', ['fr', 'en'])->name('account.orders.discussion.read');
Route::post('/{locale}/mon-compte/commandes/{order}/discussion/close', [CustomerAccountController::class, 'closeOrderDiscussion'])->whereIn('locale', ['fr', 'en'])->name('account.orders.discussion.close');
Route::post('/{locale}/mon-compte/adresses', [CustomerAccountController::class, 'storeAddress'])->whereIn('locale', ['fr', 'en'])->name('account.addresses.store');
Route::patch('/{locale}/mon-compte/adresses/{address}', [CustomerAccountController::class, 'updateAddress'])->whereIn('locale', ['fr', 'en'])->name('account.addresses.update');
Route::delete('/{locale}/mon-compte/adresses/{address}', [CustomerAccountController::class, 'deleteAddress'])->whereIn('locale', ['fr', 'en'])->name('account.addresses.delete');
Route::get('/{locale}/panier', [ShopController::class, 'cart'])->whereIn('locale', ['fr', 'en'])->name('cart.show');
Route::get('/checkout/paypal/return', [ShopController::class, 'paypalReturn'])->name('checkout.paypal.return');
Route::get('/checkout/paypal/cancel', [ShopController::class, 'paypalCancel'])->name('checkout.paypal.cancel');
Route::get('/{locale}/commande', [ShopController::class, 'checkout'])->whereIn('locale', ['fr', 'en'])->name('checkout.show');
Route::get('/{locale}/products/{slug}', [ShopController::class, 'show'])->whereIn('locale', ['fr', 'en'])->name('products.show');
Route::get('/{locale}', [ShopController::class, 'home'])->whereIn('locale', ['fr', 'en'])->name('home.localized');
