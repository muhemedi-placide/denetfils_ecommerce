<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminBackOfficeFrontendTest extends TestCase
{
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
            ->assertSee('product-create-modal', false)
            ->assertSee('product-publication-10', false);

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
            'sku' => 'MIEL-001',
            'price_cents' => 890,
            'currency' => 'EUR',
            'stock_quantity' => 12,
            'is_active' => true,
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
