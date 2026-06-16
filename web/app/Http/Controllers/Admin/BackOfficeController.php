<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AccountApiClient;
use App\Services\AdminApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

    public function catalog(string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);

        return redirect()->route('admin.catalog.products', ['locale' => $locale]);
    }

    public function catalogProducts(Request $request, AdminApiClient $admin, string $locale): View|RedirectResponse
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
            'activeAdmin' => 'catalog.products',
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
        ]));
    }

    public function catalogCategories(Request $request, AdminApiClient $admin, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $filters = $request->only(['q', 'is_active']);
        $categories = $admin->categories($context['token'], $filters);

        return view('admin.categories', $this->payload($context, [
            'activeAdmin' => 'catalog.categories',
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

    public function orders(Request $request, AdminApiClient $admin, string $locale): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $filters = $request->only([
            'id',
            'q',
            'customer',
            'new_customer',
            'total',
            'status',
            'payment_status',
            'fulfillment_status',
            'carrier',
            'date_from',
            'date_to',
        ]);
        $orders = $admin->orders($context['token'], $locale, $filters);

        return view('admin.orders', $this->payload($context, [
            'activeAdmin' => 'sales.orders',
            'orders' => $orders,
            'filters' => $filters,
        ]));
    }

    public function showOrder(Request $request, AdminApiClient $admin, string $locale, int $order): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $response = $admin->order($context['token'], $order, $locale);

        if (! ($response['ok'] ?? false)) {
            return redirect()
                ->route('admin.orders', ['locale' => $locale])
                ->withErrors($this->responseErrors($response, 'admin_action'));
        }

        return view('admin.order-show', $this->payload($context, [
            'activeAdmin' => 'sales.orders',
            'order' => $response['data'],
        ]));
    }

    public function downloadOrderInvoice(Request $request, AdminApiClient $admin, string $locale, int $order)
    {
        return $this->downloadOrderDocument($request, $admin, $locale, $order, 'invoice');
    }

    public function downloadOrderDeliveryNote(Request $request, AdminApiClient $admin, string $locale, int $order)
    {
        return $this->downloadOrderDocument($request, $admin, $locale, $order, 'delivery-note');
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

    public function modulePage(Request $request, AdminApiClient $admin, string $locale, string $module): View|RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        if ($module === 'commandes') {
            return redirect()->route('admin.orders', ['locale' => $locale]);
        }

        $definition = $this->moduleDefinitions()[$module] ?? null;

        if (! $definition) {
            abort(404);
        }

        return view('admin.module', $this->payload($context, [
            'activeAdmin' => $definition['active'],
            'module' => [
                ...$definition,
                'slug' => $module,
            ],
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

    public function storeProduct(Request $request, AdminApiClient $admin, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $validated = $this->validateAdminAction($request, [
            'category_id' => ['required', 'integer'],
            'name_fr' => ['required', 'string', 'max:180'],
            'name_en' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:220', 'alpha_dash:ascii'],
            'description_fr' => ['required', 'string', 'max:5000'],
            'description_en' => ['required', 'string', 'max:5000'],
            'sku' => ['required', 'string', 'max:80'],
            'price_eur' => ['required', 'numeric', 'min:0.01'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'weight_grams' => ['nullable', 'integer', 'min:1'],
        ], 'product-create');

        $response = $admin->createProduct($context['token'], [
            'category_id' => (int) $validated['category_id'],
            'name' => [
                'fr' => $validated['name_fr'],
                'en' => $validated['name_en'],
            ],
            'slug' => $validated['slug'],
            'description' => [
                'fr' => $validated['description_fr'],
                'en' => $validated['description_en'],
            ],
            'sku' => $validated['sku'],
            'price_cents' => $this->priceCents($validated['price_eur']),
            'currency' => 'EUR',
            'stock_quantity' => (int) $validated['stock_quantity'],
            'weight_grams' => $validated['weight_grams'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->redirectAfterAdminAction(
            $request,
            $response,
            'Produit cree dans le catalogue.',
            'product-create',
        );
    }

    public function updateProductStock(Request $request, AdminApiClient $admin, string $locale, int $product): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $validated = $this->validateAdminAction($request, [
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ], "product-stock-{$product}");

        $response = $admin->updateProduct($context['token'], $product, [
            'stock_quantity' => (int) $validated['stock_quantity'],
        ]);

        return $this->redirectAfterAdminAction(
            $request,
            $response,
            'Stock produit mis a jour.',
            "product-stock-{$product}",
        );
    }

    public function setProductPublication(Request $request, AdminApiClient $admin, string $locale, int $product): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $validated = $this->validateAdminAction($request, [
            'action' => ['required', 'in:publish,unpublish'],
        ], "product-publication-{$product}");

        $response = $validated['action'] === 'publish'
            ? $admin->publishProduct($context['token'], $product)
            : $admin->unpublishProduct($context['token'], $product);

        return $this->redirectAfterAdminAction(
            $request,
            $response,
            $validated['action'] === 'publish' ? 'Produit publie.' : 'Produit repasse en brouillon.',
            "product-publication-{$product}",
        );
    }

    public function storeCategory(Request $request, AdminApiClient $admin, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $validated = $this->validateAdminAction($request, [
            'name_fr' => ['required', 'string', 'max:160'],
            'name_en' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash:ascii'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], 'category-create');

        $response = $admin->createCategory($context['token'], [
            'name' => [
                'fr' => $validated['name_fr'],
                'en' => $validated['name_en'],
            ],
            'slug' => $validated['slug'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->redirectAfterAdminAction(
            $request,
            $response,
            'Categorie creee.',
            'category-create',
        );
    }

    public function setCategoryActivation(Request $request, AdminApiClient $admin, string $locale, int $category): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $validated = $this->validateAdminAction($request, [
            'action' => ['required', 'in:activate,deactivate'],
        ], "category-activation-{$category}");

        $response = $validated['action'] === 'activate'
            ? $admin->activateCategory($context['token'], $category)
            : $admin->deactivateCategory($context['token'], $category);

        return $this->redirectAfterAdminAction(
            $request,
            $response,
            $validated['action'] === 'activate' ? 'Categorie activee.' : 'Categorie desactivee.',
            "category-activation-{$category}",
        );
    }

    public function updateOrder(Request $request, AdminApiClient $admin, string $locale, int $order): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $validated = $this->validateAdminAction($request, [
            'status' => ['required', Rule::in($this->orderStatuses())],
            'payment_status' => ['required', Rule::in($this->paymentStatuses())],
            'fulfillment_status' => ['required', Rule::in($this->fulfillmentStatuses())],
            'carrier' => ['nullable', 'string', 'max:64'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'tracking_url' => ['nullable', 'url', 'max:2048'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'notify_customer' => ['nullable', 'boolean'],
        ], "order-update-{$order}");

        $response = $admin->updateOrder($context['token'], $order, [
            ...$validated,
            'notify_customer' => $request->boolean('notify_customer'),
        ]);

        return $this->redirectAfterAdminAction(
            $request,
            $response,
            'Commande mise a jour.',
            "order-update-{$order}",
        );
    }

    public function storeOrder(Request $request, AdminApiClient $admin, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $validated = $this->validateAdminAction($request, [
            'user_id' => ['required', 'integer', 'min:1'],
            'cart_token' => ['required', 'string', 'max:64'],
            'shipping_address_id' => ['required', 'integer', 'min:1'],
            'billing_address_id' => ['nullable', 'integer', 'min:1'],
            'delivery_method' => ['nullable', Rule::in(['standard', 'relay'])],
            'carrier' => ['nullable', 'string', 'max:64'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ], 'order-create');

        $metadata = [];

        if (! empty($validated['admin_note'])) {
            $metadata['admin_note'] = $validated['admin_note'];
        }

        $response = $admin->createOrder($context['token'], [
            'user_id' => (int) $validated['user_id'],
            'cart_token' => $validated['cart_token'],
            'shipping_address_id' => (int) $validated['shipping_address_id'],
            'billing_address_id' => isset($validated['billing_address_id']) ? (int) $validated['billing_address_id'] : null,
            'locale' => $locale,
            'delivery_method' => $validated['delivery_method'] ?? 'standard',
            'carrier' => $validated['carrier'] ?? null,
            'metadata' => $metadata,
        ]);

        return $this->redirectAfterAdminAction(
            $request,
            $response,
            'Commande creee depuis le panier client.',
            'order-create',
        );
    }

    public function storeUser(Request $request, AdminApiClient $admin, string $locale): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $validated = $this->validateAdminAction($request, [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
            'preferred_locale' => ['nullable', 'in:fr,en'],
            'country_code' => ['required', 'string', 'size:2'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', 'in:active,pending,suspended'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['nullable', 'string'],
            'position' => ['nullable', 'string', 'max:120'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ], 'user-create');

        unset($validated['password_confirmation']);
        $validated['country_code'] = strtoupper($validated['country_code']);
        $roles = array_values(array_filter($validated['roles'] ?? []));

        if ($roles === []) {
            unset($validated['roles']);
        } else {
            $validated['roles'] = $roles;
        }

        $response = $admin->createUser($context['token'], $validated);

        return $this->redirectAfterAdminAction(
            $request,
            $response,
            'Compte utilisateur cree.',
            'user-create',
        );
    }

    public function assignUserRoles(Request $request, AdminApiClient $admin, string $locale, int $user): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $validated = $this->validateAdminAction($request, [
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string'],
        ], "user-roles-{$user}");

        $response = $admin->assignUserRoles($context['token'], $user, array_values($validated['roles']));

        return $this->redirectAfterAdminAction(
            $request,
            $response,
            'Roles utilisateur mis a jour.',
            "user-roles-{$user}",
        );
    }

    public function suspendUser(Request $request, AdminApiClient $admin, string $locale, int $user): RedirectResponse
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $response = $admin->suspendUser($context['token'], $user);

        return $this->redirectAfterAdminAction(
            $request,
            $response,
            'Utilisateur suspendu.',
            "user-suspend-{$user}",
        );
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

    private function moduleDefinitions(): array
    {
        return [
            'commandes' => [
                'active' => 'sales.orders',
                'title' => 'Commandes',
                'section' => 'Vendre',
                'description' => 'Suivi des commandes web, statuts, preparation et priorites de traitement.',
                'status' => 'Branche API',
                'metrics' => ['A traiter', 'En preparation', 'Expediees', 'Litiges'],
                'workflows' => ['Importer les commandes API', 'Assigner un statut', 'Declencher la preparation', 'Informer le client'],
            ],
            'factures' => [
                'active' => 'sales.invoices',
                'title' => 'Factures',
                'section' => 'Vendre',
                'description' => 'Generation, recherche et suivi des factures client.',
                'status' => 'Structure prete',
                'metrics' => ['A generer', 'Envoyees', 'Impayees', 'Archives'],
                'workflows' => ['Relier aux commandes', 'Generer le PDF', 'Envoyer au client', 'Exporter comptabilite'],
            ],
            'avoirs' => [
                'active' => 'sales.credits',
                'title' => 'Avoirs',
                'section' => 'Vendre',
                'description' => 'Avoirs commerciaux, remboursements partiels et corrections de commande.',
                'status' => 'Structure prete',
                'metrics' => ['Demandes', 'Valides', 'Montant', 'A traiter'],
                'workflows' => ['Verifier commande', 'Saisir motif', 'Valider montant', 'Notifier client'],
            ],
            'bons-livraison' => [
                'active' => 'sales.delivery-notes',
                'title' => 'Bons de livraison',
                'section' => 'Vendre',
                'description' => 'Documents logistiques pour preparation, colisage et expedition.',
                'status' => 'Structure prete',
                'metrics' => ['A imprimer', 'En picking', 'Colises', 'Expedies'],
                'workflows' => ['Selection commandes', 'Generer bon', 'Controler colis', 'Marquer expedie'],
            ],
            'paniers' => [
                'active' => 'sales.carts',
                'title' => 'Paniers',
                'section' => 'Vendre',
                'description' => 'Paniers actifs et abandonnes pour relance commerciale.',
                'status' => 'API lecture a brancher',
                'metrics' => ['Actifs', 'Abandonnes', 'Valeur', 'Relances'],
                'workflows' => ['Lister paniers', 'Segmenter valeur', 'Relancer', 'Mesurer conversion'],
            ],
            'suivi-catalogue' => [
                'active' => 'catalog.monitoring',
                'title' => 'Suivi catalogue',
                'section' => 'Catalogue',
                'description' => 'Controle qualite des fiches produit, SEO, visuels et disponibilite.',
                'status' => 'Partiellement couvert dashboard',
                'metrics' => ['Sans image', 'SEO incomplet', 'Sans stock', 'A valider'],
                'workflows' => ['Scanner catalogue', 'Prioriser anomalies', 'Assigner corrections', 'Publier'],
            ],
            'attributs-caracteristiques' => [
                'active' => 'catalog.attributes',
                'title' => 'Attributs & caracteristiques',
                'section' => 'Catalogue',
                'description' => 'Tailles, formats, poids, variantes et caracteristiques visibles sur les fiches.',
                'status' => 'Structure prete',
                'metrics' => ['Attributs', 'Groupes', 'Variantes', 'Incomplets'],
                'workflows' => ['Creer attribut', 'Associer produits', 'Verifier variantes', 'Publier'],
            ],
            'marques-fournisseurs' => [
                'active' => 'catalog.brands',
                'title' => 'Marques et fournisseurs',
                'section' => 'Catalogue',
                'description' => 'Referentiel fournisseurs, marques, origines et contacts d approvisionnement.',
                'status' => 'Structure prete',
                'metrics' => ['Fournisseurs', 'Marques', 'Produits lies', 'A revoir'],
                'workflows' => ['Creer fournisseur', 'Associer produits', 'Suivre origine', 'Maintenir contacts'],
            ],
            'fichiers' => [
                'active' => 'catalog.files',
                'title' => 'Fichiers',
                'section' => 'Catalogue',
                'description' => 'Mediatheque produit: images, fiches techniques et documents publics.',
                'status' => 'Structure prete',
                'metrics' => ['Images', 'Documents', 'Non utilises', 'A optimiser'],
                'workflows' => ['Importer fichier', 'Renseigner alt', 'Associer produit', 'Optimiser poids'],
            ],
            'reductions' => [
                'active' => 'catalog.discounts',
                'title' => 'Reductions',
                'section' => 'Catalogue',
                'description' => 'Promotions, codes avantage, prix barres et campagnes tarifaires.',
                'status' => 'Structure prete',
                'metrics' => ['Actives', 'Planifiees', 'Expirees', 'Conversions'],
                'workflows' => ['Creer campagne', 'Limiter conditions', 'Publier', 'Analyser impact'],
            ],
            'collections-produits' => [
                'active' => 'catalog.collections',
                'title' => 'Collections de produits',
                'section' => 'Catalogue',
                'description' => 'Collections editoriales et selections merchandising.',
                'status' => 'Structure prete',
                'metrics' => ['Collections', 'Produits lies', 'Publiees', 'Brouillons'],
                'workflows' => ['Creer collection', 'Ordonner produits', 'Associer au front', 'Mesurer clics'],
            ],
            'adresses-clients' => [
                'active' => 'customers.addresses',
                'title' => 'Adresses clients',
                'section' => 'Clients',
                'description' => 'Carnets d adresses, pays actifs et controles de livraison.',
                'status' => 'API lecture a brancher',
                'metrics' => ['Adresses', 'Pays actifs', 'Incompletes', 'Defauts'],
                'workflows' => ['Rechercher client', 'Verifier adresse', 'Corriger format', 'Confirmer livraison'],
            ],
            'sav' => [
                'active' => 'support.tickets',
                'title' => 'SAV',
                'section' => 'SAV',
                'description' => 'Tickets client, demandes post-achat et suivi de resolution.',
                'status' => 'Structure prete',
                'metrics' => ['Ouverts', 'Urgents', 'En attente', 'Resolus'],
                'workflows' => ['Recevoir demande', 'Qualifier motif', 'Assigner', 'Cloturer'],
            ],
            'messages-predefinis' => [
                'active' => 'support.templates',
                'title' => 'Messages predefinis',
                'section' => 'SAV',
                'description' => 'Modeles de reponse pour demandes courantes.',
                'status' => 'Structure prete',
                'metrics' => ['Modeles', 'Langues', 'Utilises', 'A revoir'],
                'workflows' => ['Creer modele', 'Traduire', 'Valider ton', 'Utiliser dans ticket'],
            ],
            'retours-produits' => [
                'active' => 'support.returns',
                'title' => 'Retours produits',
                'section' => 'SAV',
                'description' => 'Retours, remboursements, motifs et reintegration stock.',
                'status' => 'Structure prete',
                'metrics' => ['Demandes', 'Autorises', 'Reintegrations', 'Refus'],
                'workflows' => ['Valider retour', 'Recevoir produit', 'Controler etat', 'Cloturer'],
            ],
            'modules' => [
                'active' => 'customize.modules',
                'title' => 'Modules',
                'section' => 'Personnaliser',
                'description' => 'Extensions, connecteurs et automatisations du back-office.',
                'status' => 'Structure prete',
                'metrics' => ['Actifs', 'Disponibles', 'A configurer', 'Alertes'],
                'workflows' => ['Installer module', 'Configurer', 'Tester', 'Activer'],
            ],
            'apparence' => [
                'active' => 'customize.appearance',
                'title' => 'Apparence',
                'section' => 'Personnaliser',
                'description' => 'Theme, sections front-office, bannieres et mise en avant.',
                'status' => 'Structure prete',
                'metrics' => ['Themes', 'Bannieres', 'Pages', 'Brouillons'],
                'workflows' => ['Modifier theme', 'Previsualiser', 'Valider responsive', 'Publier'],
            ],
            'livraison' => [
                'active' => 'customize.delivery',
                'title' => 'Livraison',
                'section' => 'Personnaliser',
                'description' => 'Transporteurs, zones, tarifs et points relais.',
                'status' => 'Structure prete',
                'metrics' => ['Transporteurs', 'Zones', 'Tarifs', 'Exceptions'],
                'workflows' => ['Definir zone', 'Associer transporteur', 'Fixer tarif', 'Tester checkout'],
            ],
            'paiement' => [
                'active' => 'customize.payment',
                'title' => 'Paiement',
                'section' => 'Personnaliser',
                'description' => 'Moyens de paiement, capture, remboursements et statut checkout.',
                'status' => 'Structure prete',
                'metrics' => ['Moyens', 'Actifs', 'Echecs', 'Remboursements'],
                'workflows' => ['Configurer prestataire', 'Tester sandbox', 'Activer', 'Surveiller echecs'],
            ],
            'international' => [
                'active' => 'customize.international',
                'title' => 'International',
                'section' => 'Personnaliser',
                'description' => 'Langues, pays, devises et contenus localises.',
                'status' => 'Partiellement branche',
                'metrics' => ['Langues', 'Pays', 'Traductions', 'Manquants'],
                'workflows' => ['Activer pays', 'Traduire contenus', 'Verifier SEO', 'Publier'],
            ],
            'marketing' => [
                'active' => 'customize.marketing',
                'title' => 'Marketing',
                'section' => 'Personnaliser',
                'description' => 'Campagnes, segments clients et relances CRM.',
                'status' => 'Structure prete',
                'metrics' => ['Campagnes', 'Segments', 'Relances', 'Conversions'],
                'workflows' => ['Creer segment', 'Composer message', 'Planifier', 'Analyser'],
            ],
            'parametres-boutique' => [
                'active' => 'settings.shop',
                'title' => 'Parametres de la boutique',
                'section' => 'Configurer',
                'description' => 'Identite boutique, contacts, taxes et preferences operationnelles.',
                'status' => 'Structure prete',
                'metrics' => ['Profil', 'Taxes', 'Contacts', 'SEO'],
                'workflows' => ['Verifier identite', 'Configurer taxes', 'Mettre contacts', 'Sauvegarder'],
            ],
            'parametres-avances' => [
                'active' => 'settings.advanced',
                'title' => 'Parametres avances',
                'section' => 'Configurer',
                'description' => 'Options techniques, cache, integrations et garde-fous systeme.',
                'status' => 'Structure prete',
                'metrics' => ['Integrations', 'Jobs', 'Cache', 'Securite'],
                'workflows' => ['Verifier config', 'Tester integration', 'Surveiller logs', 'Documenter'],
            ],
            'assistance' => [
                'active' => 'settings.assistance',
                'title' => 'Assistance',
                'section' => 'Configurer',
                'description' => 'Centre d aide interne, procedures et contacts support.',
                'status' => 'Structure prete',
                'metrics' => ['Guides', 'Contacts', 'Incidents', 'SLA'],
                'workflows' => ['Chercher procedure', 'Ouvrir incident', 'Documenter reponse', 'Cloturer'],
            ],
            'update-assistant' => [
                'active' => 'settings.update',
                'title' => 'Update assistant',
                'section' => 'Configurer',
                'description' => 'Suivi de version, migrations et controles avant mise a jour.',
                'status' => 'Structure prete',
                'metrics' => ['Version', 'Migrations', 'Checks', 'Risques'],
                'workflows' => ['Lire changelog', 'Verifier sauvegarde', 'Tester staging', 'Executer update'],
            ],
        ];
    }

    private function redirectAfterAdminAction(Request $request, array $response, string $success, string $modal): RedirectResponse
    {
        if (! ($response['ok'] ?? false)) {
            return back()
                ->withErrors($this->responseErrors($response, 'admin_action'))
                ->withInput()
                ->with('admin_modal', $modal);
        }

        return back()->with('status', $success);
    }

    /**
     * @throws ValidationException
     */
    private function validateAdminAction(Request $request, array $rules, string $modal): array
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException(
                $validator,
                back()->withErrors($validator)->withInput()->with('admin_modal', $modal),
            );
        }

        return $validator->validated();
    }

    private function priceCents(int|float|string $value): int
    {
        return (int) round((float) $value * 100);
    }

    private function downloadOrderDocument(Request $request, AdminApiClient $admin, string $locale, int $order, string $type)
    {
        $locale = $this->setLocale($locale);
        $context = $this->context($request, $admin, $locale);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $response = $admin->order($context['token'], $order, $locale);

        if (! ($response['ok'] ?? false)) {
            return redirect()
                ->route('admin.orders', ['locale' => $locale])
                ->withErrors($this->responseErrors($response, 'admin_action'));
        }

        $orderData = $response['data'];
        $reference = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) ($orderData['order_number'] ?? $order));
        $title = $type === 'invoice' ? 'Facture' : 'Bon de livraison';
        $filename = ($type === 'invoice' ? 'facture-' : 'bon-livraison-').$reference.'.pdf';

        return response($this->orderPdf($title, $orderData), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function orderPdf(string $title, array $order): string
    {
        $lines = [
            $title.' - '.($order['order_number'] ?? 'Commande'),
            'DEN & FILS',
            'Date: '.(string) ($order['placed_at'] ?? $order['created_at'] ?? '-'),
            'Client: '.(string) data_get($order, 'customer.name', '-'),
            'Email: '.(string) data_get($order, 'customer.email', '-'),
            'Total: '.(string) ($order['formatted_total'] ?? '-'),
            'Livraison: '.(string) ($order['carrier'] ?? '-'),
            '',
            'Articles',
        ];

        foreach (($order['items'] ?? []) as $item) {
            $lines[] = sprintf(
                '- %s x%s - %s',
                (string) data_get($item, 'product.name', 'Produit'),
                (string) ($item['quantity'] ?? 0),
                (string) ($item['formatted_line_total'] ?? '-'),
            );
        }

        $lines[] = '';
        $lines[] = 'Sous-total: '.(string) ($order['formatted_subtotal'] ?? '-');
        $lines[] = 'Livraison: '.(string) ($order['formatted_shipping'] ?? '-');
        $lines[] = 'TVA: '.(string) ($order['formatted_tax'] ?? '-');
        $lines[] = 'Total: '.(string) ($order['formatted_total'] ?? '-');

        return $this->simplePdf($lines);
    }

    private function simplePdf(array $lines): string
    {
        $content = "BT\n/F1 12 Tf\n50 790 Td\n";

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "0 -18 Td\n";
            }

            $content .= '('.$this->pdfText((string) $line).") Tj\n";
        }

        $content .= "ET\n";
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length ".strlen($content)." >>\nstream\n{$content}endstream\nendobj\n",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function pdfText(string $value): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value) ?: $value;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
    }

    private function setLocale(?string $locale): string
    {
        $locale = in_array($locale, ['fr', 'en'], true) ? $locale : config('app.locale', 'fr');

        app()->setLocale($locale);

        return $locale;
    }

    private function orderStatuses(): array
    {
        return ['pending_payment', 'confirmed', 'processing', 'completed', 'cancelled', 'refunded'];
    }

    private function paymentStatuses(): array
    {
        return ['unpaid', 'authorized', 'paid', 'failed', 'partially_refunded', 'refunded'];
    }

    private function fulfillmentStatuses(): array
    {
        return ['unfulfilled', 'preparing', 'ready_to_ship', 'shipped', 'delivered', 'returned', 'cancelled'];
    }
}
