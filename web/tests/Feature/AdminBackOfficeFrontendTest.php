<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminBackOfficeFrontendTest extends TestCase
{
    public function test_admin_can_open_and_fully_update_product_details(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());
        $session = [
            'admin_api_token' => 'admin-token',
            'admin_user' => ['name' => 'Admin Test', 'email' => 'admin@example.test', 'roles' => ['admin']],
        ];

        $this->withSession($session)
            ->get('/fr/admin/catalogue/produits/10')
            ->assertOk()
            ->assertSee('Référencement du produit')
            ->assertSee('Santé du produit')
            ->assertSee('Enregistrer toutes les modifications');

        $this->withSession($session)
            ->patch('/fr/admin/catalogue/produits/10', [
                'category_id' => 3,
                'name_fr' => 'Miel doux optimisé',
                'name_en' => 'Optimized sweet honey',
                'slug' => 'miel-doux-optimise',
                'sku' => 'MIEL-001',
                'brand' => 'Marché Peyi',
                'purchase_price_eur' => '5.00',
                'sale_price_ttc_eur' => '9.90',
                'tax_class' => 'food',
                'stock_quantity' => 20,
                'description_fr' => 'Description complète optimisée.',
                'description_en' => 'Optimized full description.',
                'seo_title_fr' => 'Miel doux naturel | Marché Peyi',
                'seo_title_en' => 'Natural sweet honey | Marché Peyi',
                'seo_description_fr' => 'Achetez un miel doux naturel soigneusement sélectionné.',
                'seo_description_en' => 'Shop carefully selected natural sweet honey.',
                'seo_keywords_fr' => 'miel, naturel',
                'seo_keywords_en' => 'honey, natural',
                'primary_existing_id' => 44,
            ])
            ->assertRedirect('/fr/admin/catalogue/produits/10')
            ->assertSessionHas('status', 'Produit mis à jour.');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/admin/products/10')
            && $request->method() === 'PATCH'
            && $request['price_cents'] === 990
            && $request['purchase_price_cents'] === 500
            && data_get($request->data(), 'seo_title.fr') === 'Miel doux naturel | Marché Peyi'
            && data_get($request->data(), 'images.0.is_primary') === true);
    }

    public function test_admin_can_start_catalog_health_scan_and_see_missing_elements(): void
    {
        $diagnostic = [
            'id' => 10,
            'name' => ['fr' => 'Miel premium', 'en' => 'Premium honey'],
            'slug' => 'miel-premium',
            'sku' => 'MP-MIEL-001',
            'is_active' => true,
            'primary_image' => null,
            'health' => [
                'score' => 63,
                'status' => 'incomplete',
                'visibility' => 'limited',
                'checks_count' => 27,
                'completed_count' => 17,
                'missing_count' => 10,
                'critical_count' => 1,
                'missing' => [
                    ['key' => 'primary_image', 'section' => 'media', 'type' => 'missing', 'critical' => true, 'label' => 'Image principale'],
                    ['key' => 'seo_title_fr', 'section' => 'seo', 'type' => 'missing', 'critical' => false, 'label' => 'Titre SEO FR'],
                ],
            ],
        ];

        Http::fake([
            '*/admin/catalog-health*' => Http::response([
                'data' => [$diagnostic],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
                'summary' => [
                    'products_count' => 1,
                    'average_score' => 63,
                    'missing_total' => 10,
                    'excellent_count' => 0,
                    'critical_count' => 0,
                    'scanned_at' => now()->toIso8601String(),
                ],
            ]),
        ]);

        $session = [
            'admin_api_token' => 'admin-token',
            'admin_user' => ['name' => 'Admin Test', 'email' => 'admin@example.test', 'roles' => ['admin']],
        ];

        $this->withSession($session)
            ->get('/fr/admin/catalogue/suivi')
            ->assertOk()
            ->assertSee('Démarrer le scan du catalogue')
            ->assertDontSee('Miel premium');

        $this->withSession($session)
            ->get('/fr/admin/catalogue/suivi?scan=1')
            ->assertOk()
            ->assertSee('Miel premium')
            ->assertSee('10 élément(s)')
            ->assertSee('Image principale')
            ->assertSee('Visibilité limitée');
    }

    public function test_admin_can_create_complete_product_with_prices_gallery_and_icon(): void
    {
        Storage::fake('public');
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->post('/fr/admin/catalogue/produits', [
            'category_id' => 1,
            'name_fr' => 'Miel premium',
            'name_en' => 'Premium honey',
            'slug' => 'miel-premium',
            'sku' => 'MP-MIEL-001',
            'barcode' => '3760123456789',
            'brand' => 'Marché Peyi',
            'supplier_reference' => 'FOUR-001',
            'origin_fr' => 'France',
            'origin_en' => 'France',
            'purchase_price_eur' => '5.20',
            'sale_price_ttc_eur' => '9.90',
            'compare_at_price_eur' => '11.90',
            'tax_class' => 'food',
            'stock_quantity' => 24,
            'max_order_quantity' => 6,
            'weight_grams' => 250,
            'unit_label' => 'pot',
            'short_description_fr' => 'Miel floral.',
            'short_description_en' => 'Floral honey.',
            'description_fr' => 'Description complète du miel.',
            'description_en' => 'Complete honey description.',
            'product_images' => [
                UploadedFile::fake()->image('face.png', 800, 800),
                UploadedFile::fake()->image('dos.png', 800, 800),
            ],
            'primary_image_index' => 1,
            'product_icon' => UploadedFile::fake()->image('icone.png', 256, 256),
            'is_active' => '1',
        ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Produit créé avec sa fiche commerciale et ses médias.');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/admin/products')
            && $request->method() === 'POST'
            && $request['purchase_price_cents'] === 520
            && $request['price_cents'] === 990
            && $request['compare_at_price_cents'] === 1190
            && count($request['images']) === 3
            && data_get($request->data(), 'images.1.is_primary') === true
            && data_get($request->data(), 'images.2.role') === 'icon');

        $this->assertCount(3, Storage::disk('public')->allFiles('products'));
    }

    public function test_admin_can_open_cart_list_and_cart_details(): void
    {
        $cart = [
            'id' => 7,
            'reference' => 'CRT-ABC123',
            'admin_status' => 'abandoned',
            'items_count' => 2,
            'distinct_items_count' => 1,
            'total_weight_grams' => 500,
            'formatted_total' => '17,80 EUR',
            'created_at' => now()->subDays(2)->toIso8601String(),
            'last_activity_at' => now()->subDays(2)->toIso8601String(),
            'expires_at' => now()->addDays(28)->toIso8601String(),
            'customer' => ['name' => 'Client Test', 'email' => 'client@example.test'],
            'recovery_links' => [],
            'items' => [[
                'quantity' => 2,
                'formatted_unit_price' => '8,90 EUR',
                'formatted_line_total' => '17,80 EUR',
                'product' => ['name' => 'Miel', 'sku' => 'MIEL-1', 'origin' => 'France'],
                'variant' => null,
            ]],
        ];

        Http::fake([
            '*/admin/carts/7*' => Http::response(['data' => $cart]),
            '*/admin/carts*' => Http::response([
                'data' => [$cart],
                'meta' => ['total' => 1],
                'summary' => ['formatted_value' => '17,80 EUR', 'abandoned_count' => 1],
            ]),
        ]);

        $session = [
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
                'permissions' => ['carts.view', 'carts.manage'],
            ],
        ];

        $this->withSession($session)
            ->get('/fr/admin/paniers')
            ->assertOk()
            ->assertSee('CRT-ABC123')
            ->assertSee('Client Test');

        $this->withSession($session)
            ->get('/fr/admin/paniers/7')
            ->assertOk()
            ->assertSee('Détail du panier')
            ->assertSee('Créer un lien de récupération');
    }

    public function test_admin_pages_render_with_shell_and_action_modals(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $session = [
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ];

        $dashboardResponse = $this->withSession($session)
            ->get('/fr/admin')
            ->assertOk()
            ->assertSee('Back-office '.config('shop.name'))
            ->assertSee('adminShell', false)
            ->assertSee('Objectifs rapides');
        $this->assertSame(1, substr_count($dashboardResponse->getContent(), 'x-on:click="logoutOpen = true"'));

        $this->withSession($session)
            ->get('/fr/admin/catalogue/produits')
            ->assertOk()
            ->assertSee('Nouveau produit')
            ->assertSee('product-create-wizard', false)
            ->assertSee('/fr/admin/catalogue/produits/10', false)
            ->assertSee('name="stock_quantity"', false)
            ->assertDontSee('product-publication-10', false);

        $this->withSession($session)
            ->get('/fr/admin/catalogue/categories')
            ->assertOk()
            ->assertSee('Nouvelle categorie')
            ->assertSee('category-create-modal', false);

        $this->withSession($session)
            ->get('/fr/admin/stock')
            ->assertOk()
            ->assertSee('inventory-stock-10', false);

        $this->withSession($session)
            ->get('/fr/admin/commandes')
            ->assertOk()
            ->assertSee('Commandes (1)')
            ->assertSee('Actions groupées')
            ->assertSee('Ajouter une commande')
            ->assertSee('order-create-modal', false)
            ->assertSee('order-state-form-42', false)
            ->assertSee('data-submit-on-change', false)
            ->assertSee('name="order_state"', false)
            ->assertDontSee('order-show-42', false)
            ->assertDontSee('order-update-42', false)
            ->assertSee('DF-20260616-ABC123')
            ->assertSee('En attente de paiement')
            ->assertSee('/fr/admin/commandes/42/facture', false)
            ->assertSee('/fr/admin/commandes/42/bon-livraison', false)
            ->assertSee('/fr/admin/commandes/42', false);

        $this->withSession($session)
            ->get('/en/admin/commandes')
            ->assertOk()
            ->assertSee('Track online orders, payments, preparation and shipping.')
            ->assertSee('Add order')
            ->assertSee('New customer')
            ->assertDontSee('Ajouter une commande');

        $this->withSession($session)
            ->get('/fr/admin/factures')
            ->assertOk()
            ->assertSee('Recherchez, contrôlez et téléchargez les factures clients.')
            ->assertSee('FAC-DF-20260616-ABC123')
            ->assertSee('Date d’émission');

        $this->withSession($session)
            ->get('/en/admin/factures')
            ->assertOk()
            ->assertSee('Search, review and download customer invoices.')
            ->assertSee('Issue date')
            ->assertDontSee('Date d’émission');

        $this->withSession($session)
            ->get('/fr/admin/factures/5')
            ->assertOk()
            ->assertSee('Détail de la facture')
            ->assertSee('Articles facturés')
            ->assertSee('/fr/admin/commandes/42/facture', false);

        $this->withSession($session)
            ->get('/fr/admin/utilisateurs')
            ->assertOk()
            ->assertSee('Equipe')
            ->assertSee('Inviter un membre')
            ->assertSee('user-create-modal', false)
            ->assertSee('user-roles-7', false);

        $this->withSession($session)
            ->get('/fr/admin/clients')
            ->assertOk()
            ->assertSee('Liste des clients')
            ->assertSee('client@example.test');

        $this->withSession($session)
            ->get('/fr/admin/clients/9')
            ->assertOk()
            ->assertSee('Adresses du client')
            ->assertSee('DF-20260616-ABC123')
            ->assertSee('STRIPE')
            ->assertSee('Question ouverte');

        $this->withSession($session)
            ->get('/fr/admin/acces?role=support_agent')
            ->assertOk()
            ->assertSee('Matrice d autorisation')
            ->assertSee('Enregistrement automatique')
            ->assertDontSee('Enregistrer les permissions')
            ->assertSee('value="catalog.view"', false)
            ->assertSee('window.permissionMatrix', false);

        $this->withSession($session)
            ->get('/fr/admin/audit')
            ->assertOk()
            ->assertSee('audit-show-0', false);

        $this->withSession($session)
            ->get('/fr/admin/modules/paiement')
            ->assertOk()
            ->assertSee('Modes de paiement')
            ->assertSee('payment-create-modal', false)
            ->assertSee('Stripe')
            ->assertSee('Tester');

        $this->withSession($session)
            ->get('/fr/admin/modules/commandes')
            ->assertRedirect('/fr/admin/commandes');
    }

    public function test_admin_can_open_order_detail_page(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->get('/fr/admin/commandes/42')
            ->assertOk()
            ->assertSee('Commande #42 DF-20260616-ABC123')
            ->assertSee('Basic information')
            ->assertSee('Produits (1)')
            ->assertSee('Client')
            ->assertSee('Documents')
            ->assertSee('Transporteur')
            ->assertSee('Discussion client')
            ->assertSee('Reponse au client')
            ->assertSee('Merci pour votre message.')
            ->assertSee('Sources')
            ->assertSee('/fr/admin/commandes/42/impression', false);
    }

    public function test_admin_can_update_customer_status_through_customer_api(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->patch('/fr/admin/clients/9', [
            'status' => 'suspended',
        ])
            ->assertRedirect('/fr/admin/clients/9')
            ->assertSessionHas('admin_success');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/admin/customers/9')
            && $request->method() === 'PATCH'
            && $request['status'] === 'suspended');
    }

    public function test_admin_can_assign_permissions_from_access_matrix(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->patchJson('/fr/admin/acces/roles/2/permissions', [
            'role_name' => 'support_agent',
            'permissions' => ['catalog.view', 'orders.view'],
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Permission mise a jour automatiquement.');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/admin/roles/2/permissions')
            && $request->method() === 'PATCH'
            && $request['permissions'] === ['catalog.view', 'orders.view']);
    }

    public function test_admin_can_send_order_discussion_message(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->post('/fr/admin/commandes/42/discussion/messages', [
            'body' => 'Bonjour, votre colis part aujourd hui.',
        ])
            ->assertRedirect(route('admin.orders.show', ['locale' => 'fr', 'order' => 42]))
            ->assertSessionHas('status', 'Discussion commande mise a jour.');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/admin/orders/42/conversation/messages')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer admin-token')
            && $request['body'] === 'Bonjour, votre colis part aujourd hui.');
    }

    public function test_admin_can_open_printable_order_page(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->get('/fr/admin/commandes/42/impression')
            ->assertOk()
            ->assertSee(config('shop.name'))
            ->assertSee('COMMANDE')
            ->assertSee('DF-20260616-ABC123')
            ->assertSee('document-footer', false)
            ->assertSee('window.print()', false);
    }

    public function test_admin_can_download_order_invoice_and_delivery_note(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $session = [
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ];

        $invoice = $this->withSession($session)
            ->get('/fr/admin/commandes/42/facture')
            ->assertOk()
            ->assertDownload('facture-DF-20260616-ABC123.pdf');

        $this->assertStringStartsWith('%PDF-1.4', $invoice->baseResponse->getContent());
        $this->assertStringContainsString('March', $invoice->baseResponse->getContent());
        $this->assertStringContainsString('FACTURE', $invoice->baseResponse->getContent());
        $this->assertStringContainsString('Page 1 / 1', $invoice->baseResponse->getContent());

        $englishInvoice = $this->withSession($session)
            ->get('/en/admin/commandes/42/facture')
            ->assertOk()
            ->assertDownload('invoice-DF-20260616-ABC123.pdf');

        $this->assertStringContainsString('INVOICE', $englishInvoice->baseResponse->getContent());
        $this->assertStringContainsString('Order date', $englishInvoice->baseResponse->getContent());

        $deliveryNote = $this->withSession($session)
            ->get('/fr/admin/commandes/42/bon-livraison')
            ->assertOk()
            ->assertDownload('bon-livraison-DF-20260616-ABC123.pdf');

        $this->assertStringStartsWith('%PDF-1.4', $deliveryNote->baseResponse->getContent());
        $this->assertStringContainsString('BON DE LIVRAISON', $deliveryNote->baseResponse->getContent());
    }

    public function test_admin_can_submit_order_status_update_to_api(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->patch('/fr/admin/commandes/42', [
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'fulfillment_status' => 'preparing',
            'carrier' => 'chrono_relais_pickup',
            'tracking_number' => 'CR123456789FR',
            'tracking_url' => 'https://tracking.example.test/CR123456789FR',
            'admin_note' => 'Preparation prioritaire.',
            'notify_customer' => '1',
        ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Commande mise a jour.');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/admin/orders/42')
            && $request->method() === 'PATCH'
            && $request->hasHeader('Authorization', 'Bearer admin-token')
            && $request['status'] === 'confirmed'
            && $request['payment_status'] === 'paid'
            && $request['tracking_number'] === 'CR123456789FR');
    }

    public function test_admin_can_update_order_state_from_list_shortcut(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->patch('/fr/admin/commandes/42', [
            'order_state' => 'delivered',
            'status' => 'pending_payment',
            'payment_status' => 'unpaid',
            'fulfillment_status' => 'unfulfilled',
        ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Commande mise a jour.');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/admin/orders/42')
            && $request->method() === 'PATCH'
            && $request->hasHeader('Authorization', 'Bearer admin-token')
            && $request['status'] === 'completed'
            && $request['payment_status'] === 'paid'
            && $request['fulfillment_status'] === 'delivered'
            && $request['order_state'] === 'delivered');
    }

    public function test_admin_can_submit_manual_order_creation_to_api(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->post('/fr/admin/commandes', [
            'customer_id' => 7,
            'cart_token' => 'cart-token-123',
            'shipping_address_id' => 11,
            'billing_address_id' => 12,
            'delivery_method' => 'relay',
            'carrier' => 'mondial_relay_pickup',
            'admin_note' => 'Commande ajoutee par telephone.',
        ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Commande creee depuis le panier client.');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/admin/orders')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer admin-token')
            && $request['customer_id'] === 7
            && $request['cart_token'] === 'cart-token-123'
            && data_get($request->data(), 'metadata.admin_note') === 'Commande ajoutee par telephone.');
    }

    public function test_admin_can_create_stripe_payment_method(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->post('/fr/admin/modules/paiement/methodes', [
            'code' => 'stripe_cards_fr',
            'provider' => 'stripe',
            'display_name' => [
                'fr' => 'Carte bancaire',
                'en' => 'Card',
            ],
            'environment' => 'sandbox',
            'countries' => 'FR, BE',
            'currencies' => 'EUR',
            'credentials' => [
                'publishable_key' => 'pk_test_123',
                'secret_key' => 'sk_test_123',
                'webhook_signing_secret' => 'whsec_123',
            ],
        ])
            ->assertRedirect('/fr/admin/modules/paiement')
            ->assertSessionHas('admin_success', 'Mode de paiement cree.');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/admin/payment-methods')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer admin-token')
            && $request['provider'] === 'stripe'
            && $request['code'] === 'stripe_cards_fr'
            && $request['countries'] === ['FR', 'BE']
            && $request['currencies'] === ['EUR']
            && $request['credentials']['publishable_key'] === 'pk_test_123'
            && $request['credentials']['secret_key'] === 'sk_test_123');
    }

    public function test_admin_can_activate_payment_method(): void
    {
        $this->withoutVite();
        Http::fake($this->adminApiFakes());

        $this->withSession([
            'admin_api_token' => 'admin-token',
            'admin_user' => [
                'name' => 'Admin Test',
                'email' => 'admin@example.test',
                'roles' => ['admin'],
            ],
        ])->post('/fr/admin/modules/paiement/methodes/5/activate')
            ->assertRedirect('/fr/admin/modules/paiement')
            ->assertSessionHas('admin_success', 'Mode de paiement active.');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), '/admin/payment-methods/5/activate')
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer admin-token'));
    }

    private function adminApiFakes(): array
    {
        return [
            '*/admin/dashboard*' => Http::response([
                'data' => [
                    'kpis' => [
                        'catalog' => ['products_active' => 1, 'products_total' => 1],
                        'inventory' => ['total_units_available' => 12, 'low_stock_products' => 0],
                        'carts' => ['active_count' => 2, 'formatted_active_value' => '18,00 EUR'],
                        'identity' => ['users_total' => 1, 'customers_total' => 1],
                    ],
                    'catalog_health' => [
                        'products_missing_images' => 0,
                        'products_missing_variants' => 0,
                        'products_missing_seo' => 0,
                        'inactive_categories_with_active_products' => 0,
                    ],
                    'stock_alerts' => [],
                    'recent_activity' => [$this->auditLog()],
                ],
            ]),
            '*/admin/products/10*' => Http::response([
                'data' => $this->product(),
            ]),
            '*/admin/products*' => Http::response([
                'data' => [$this->product()],
                'meta' => ['total' => 1],
            ]),
            '*/admin/categories*' => Http::response([
                'data' => [$this->category()],
                'meta' => ['total' => 1],
            ]),
            '*/admin/inventory*' => Http::response([
                'data' => [$this->inventoryProduct()],
                'meta' => ['total' => 1],
            ]),
            '*/admin/orders/42/conversation*' => Http::response([
                'data' => $this->orderConversation(),
            ]),
            '*/admin/orders/42*' => Http::response([
                'data' => $this->order(),
            ]),
            '*/admin/orders*' => Http::response([
                'data' => [$this->order()],
                'meta' => ['total' => 1],
                'summary' => [
                    'total_orders' => 1,
                    'pending_orders' => 1,
                    'paid_orders' => 0,
                    'to_prepare_orders' => 1,
                    'shipped_orders' => 0,
                    'formatted_total' => '25,86 EUR',
                    'conversion_rate_percent' => 50,
                    'abandoned_carts' => 1,
                    'formatted_average_order' => '25,86 EUR',
                    'formatted_net_margin_per_visitor' => '0,00 EUR',
                ],
            ]),
            '*/admin/invoices/5*' => Http::response([
                'data' => $this->invoice(),
            ]),
            '*/admin/invoices*' => Http::response([
                'data' => [$this->invoice()],
                'meta' => ['total' => 1, 'current_page' => 1, 'last_page' => 1],
                'summary' => [
                    'total_invoices' => 1,
                    'draft_invoices' => 0,
                    'issued_invoices' => 0,
                    'paid_invoices' => 1,
                    'total_cents' => 2586,
                    'formatted_total' => '25,86 EUR',
                ],
            ]),
            '*/admin/customers/9*' => Http::response([
                'data' => $this->customer(),
            ]),
            '*/admin/customers*' => Http::response([
                'data' => [$this->customer()],
                'meta' => ['total' => 1],
            ]),
            '*/admin/users*' => Http::response([
                'data' => [$this->user()],
                'meta' => ['total' => 1],
            ]),
            '*/admin/roles/2/permissions' => Http::response([
                'data' => ['id' => 2, 'name' => 'support_agent', 'permissions' => ['catalog.view', 'orders.view']],
            ]),
            '*/admin/roles*' => Http::response([
                'data' => [
                    ['id' => 1, 'name' => 'admin', 'permissions' => ['catalog.view', 'users.view']],
                    ['id' => 2, 'name' => 'support_agent', 'permissions' => ['catalog.view', 'orders.view']],
                ],
            ]),
            '*/admin/permissions*' => Http::response([
                'data' => ['catalog.view', 'users.view', 'audit.view'],
            ]),
            '*/admin/audit-logs*' => Http::response([
                'data' => [$this->auditLog()],
                'meta' => ['total' => 1],
            ]),
            '*/admin/payment-methods/schemas' => Http::response([
                'data' => [
                    'stripe' => ['name' => 'Stripe'],
                    'paypal' => ['name' => 'PayPal'],
                    'bank_transfer' => ['name' => 'Bank transfer'],
                    'cash_on_delivery' => ['name' => 'Cash on delivery'],
                ],
            ]),
            '*/admin/payment-methods*' => Http::response([
                'data' => [$this->paymentMethod()],
                'meta' => ['total' => 1],
            ]),
        ];
    }

    private function product(): array
    {
        return [
            'id' => 10,
            'category_id' => 3,
            'category' => $this->category(),
            'name' => ['fr' => 'Miel doux', 'en' => 'Sweet honey'],
            'slug' => 'miel-doux',
            'description' => ['fr' => 'Description produit', 'en' => 'Product description'],
            'short_description' => ['fr' => 'Miel doux.', 'en' => 'Sweet honey.'],
            'origin' => ['fr' => 'France', 'en' => 'France'],
            'sku' => 'MIEL-001',
            'barcode' => '3760123456789',
            'brand' => 'Marché Peyi',
            'supplier_reference' => 'SUP-001',
            'purchase_price_cents' => 500,
            'price_cents' => 890,
            'compare_at_price_cents' => null,
            'currency' => 'EUR',
            'tax_class' => 'food',
            'weight_grams' => 250,
            'unit_label' => 'pot',
            'stock_quantity' => 12,
            'max_order_quantity' => 6,
            'seo_title' => ['fr' => 'Miel doux', 'en' => 'Sweet honey'],
            'seo_description' => ['fr' => 'Description SEO', 'en' => 'SEO description'],
            'seo_keywords' => ['fr' => ['miel'], 'en' => ['honey']],
            'canonical_path' => '/{locale}/products/miel-doux',
            'is_active' => true,
            'primary_image' => ['id' => 44, 'url' => 'https://example.test/miel.jpg', 'alt_text' => ['fr' => 'Miel', 'en' => 'Honey'], 'is_primary' => true],
            'images' => [['id' => 44, 'url' => 'https://example.test/miel.jpg', 'alt_text' => ['fr' => 'Miel', 'en' => 'Honey'], 'is_primary' => true, 'sort_order' => 1]],
            'icon_image' => null,
            'health' => ['score' => 82, 'status' => 'good', 'visibility' => 'high', 'missing_count' => 4, 'missing' => []],
            'variants' => [],
        ];
    }

    private function inventoryProduct(): array
    {
        return [
            ...$this->product(),
            'preview_name' => ['fr' => 'Miel doux', 'en' => 'Sweet honey'],
            'stock_status' => 'in_stock',
            'low_stock_threshold' => 5,
            'updated_at' => '2026-06-16T10:00:00Z',
        ];
    }

    private function order(): array
    {
        return [
            'id' => 42,
            'cart_id' => 78,
            'order_number' => 'DF-20260616-ABC123',
            'status' => 'pending_payment',
            'payment_status' => 'unpaid',
            'fulfillment_status' => 'unfulfilled',
            'currency' => 'EUR',
            'formatted_total' => '25,86 EUR',
            'formatted_subtotal' => '17,80 EUR',
            'formatted_shipping' => '5,90 EUR',
            'formatted_tax' => '2,16 EUR',
            'customer' => [
                'name' => 'Jean Martin',
                'email' => 'jean@example.test',
                'phone' => '+33600000000',
                'country_code' => 'FR',
            ],
            'delivery_method' => 'relay',
            'carrier' => 'mondial_relay_pickup',
            'tracking' => [
                'number' => null,
                'url' => null,
            ],
            'payment_method' => 'PayPal',
            'is_new_customer' => true,
            'metadata' => [
                'pickup_point' => [
                    'name' => 'Commerce partenaire',
                    'address' => '12 rue Oberkampf, 75011 Paris',
                ],
                'source_events' => [
                    [
                        'date' => '2026-06-16T09:50:00Z',
                        'from' => 'Boutique web',
                        'to' => 'Checkout DEN & FILS',
                    ],
                ],
            ],
            'admin_notes' => [],
            'placed_at' => '2026-06-16T10:00:00Z',
            'items' => [
                [
                    'id' => 1,
                    'product' => ['name' => 'Miel doux', 'sku' => 'MIEL-001'],
                    'quantity' => 2,
                    'available_quantity' => 12,
                    'formatted_unit_price' => '8,90 EUR',
                    'formatted_line_total' => '17,80 EUR',
                ],
            ],
            'addresses' => [
                [
                    'type' => 'shipping',
                    'recipient_name' => 'Jean Martin',
                    'street_line_1' => '12 Rue du Test',
                    'postal_code' => '75001',
                    'city' => 'Paris',
                    'country_code' => 'FR',
                ],
                [
                    'type' => 'billing',
                    'recipient_name' => 'Jean Martin',
                    'street_line_1' => '12 Rue du Test',
                    'postal_code' => '75001',
                    'city' => 'Paris',
                    'country_code' => 'FR',
                ],
            ],
        ];
    }

    private function orderConversation(): array
    {
        return [
            'id' => 3,
            'order_id' => 42,
            'status' => 'open',
            'customer_unread_count' => 0,
            'staff_unread_count' => 1,
            'messages' => [
                [
                    'id' => 1,
                    'sender_type' => 'customer',
                    'body' => 'Merci pour votre message.',
                    'status' => 'unread',
                    'status_for_staff' => 'unread',
                    'is_own' => false,
                    'created_at' => '2026-06-16T10:15:00Z',
                ],
                [
                    'id' => 2,
                    'sender_type' => 'staff',
                    'body' => 'Nous preparons votre commande.',
                    'status' => 'read',
                    'status_for_staff' => 'read',
                    'is_own' => true,
                    'created_at' => '2026-06-16T10:20:00Z',
                ],
            ],
        ];
    }

    private function category(): array
    {
        return [
            'id' => 3,
            'name' => ['fr' => 'Epicerie', 'en' => 'Grocery'],
            'slug' => 'epicerie',
            'sort_order' => 1,
            'is_active' => true,
            'products_count' => 1,
        ];
    }

    private function user(): array
    {
        return [
            'id' => 7,
            'name' => 'Admin Test',
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin@example.test',
            'phone' => '+33600000000',
            'preferred_locale' => 'fr',
            'country_code' => 'FR',
            'timezone' => 'Europe/Paris',
            'status' => 'active',
            'roles' => ['admin'],
        ];
    }

    private function invoice(): array
    {
        return [
            'id' => 5,
            'invoice_number' => 'FAC-DF-20260616-ABC123',
            'status' => 'paid',
            'status_label' => 'Payée',
            'currency' => 'EUR',
            'total_cents' => 2586,
            'formatted_total' => '25,86 EUR',
            'issued_at' => '2026-06-16T10:00:00Z',
            'due_at' => '2026-07-16T10:00:00Z',
            'paid_at' => '2026-06-16T10:05:00Z',
            'order' => [
                'id' => 42,
                'order_number' => 'DF-20260616-ABC123',
                'payment_status' => 'paid',
                'customer' => [
                    'name' => 'Jean Martin',
                    'email' => 'jean@example.test',
                    'phone' => '+33600000000',
                ],
            ],
            'order_detail' => $this->order(),
        ];
    }

    private function customer(): array
    {
        return [
            'id' => 9,
            'role' => 'customer',
            'name' => 'Client Test',
            'first_name' => 'Client',
            'last_name' => 'Test',
            'email' => 'client@example.test',
            'phone' => '+33700000000',
            'preferred_locale' => 'fr',
            'country_code' => 'FR',
            'timezone' => 'Europe/Paris',
            'status' => 'active',
            'summary' => [
                'orders_count' => 1,
                'addresses_count' => 1,
                'total_spent_cents' => 2586,
                'open_conversations_count' => 1,
            ],
            'addresses' => [[
                'id' => 4,
                'type' => 'shipping',
                'label' => 'Maison',
                'recipient_name' => 'Client Test',
                'street_line_1' => '12 rue Test',
                'postal_code' => '75001',
                'city' => 'Paris',
                'country_code' => 'FR',
                'is_default' => true,
            ]],
            'orders' => [[
                ...$this->order(),
                'payments' => [[
                    'provider' => 'stripe',
                    'status' => 'captured',
                    'amount_cents' => 2586,
                    'currency' => 'EUR',
                ]],
                'conversation' => [
                    'status' => 'Question ouverte',
                    'staff_unread_count' => 1,
                ],
            ]],
        ];
    }

    private function auditLog(): array
    {
        return [
            'action' => 'product.updated',
            'actor' => ['name' => 'Admin Test', 'email' => 'admin@example.test'],
            'auditable_type' => 'App\\Models\\Product',
            'auditable_id' => 10,
            'metadata' => ['field' => 'stock_quantity'],
            'ip_address' => '127.0.0.1',
            'created_at' => '2026-06-16T10:00:00Z',
        ];
    }

    private function paymentMethod(): array
    {
        return [
            'id' => 5,
            'code' => 'stripe_cards_fr',
            'provider' => 'stripe',
            'provider_name' => 'Stripe',
            'display_name' => ['fr' => 'Carte bancaire', 'en' => 'Card'],
            'environment' => 'sandbox',
            'status' => 'draft',
            'is_enabled' => false,
            'countries' => ['FR', 'BE'],
            'currencies' => ['EUR'],
            'credentials' => [
                'configured' => ['publishable_key', 'secret_key', 'webhook_signing_secret'],
                'missing_required' => [],
                'masked' => [
                    'publishable_key' => 'pk_test_123',
                    'secret_key' => '********',
                    'webhook_signing_secret' => '********',
                ],
            ],
            'last_test_message' => 'Credentials structure is complete.',
        ];
    }
}
