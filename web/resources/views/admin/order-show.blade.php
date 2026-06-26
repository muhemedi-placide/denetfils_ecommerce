@extends('layouts.admin')

@section('title', 'Commande '.($order['order_number'] ?? ''))
@section('page_title', 'Commande #'.($order['id'] ?? '-').' '.($order['order_number'] ?? ''))
@section('page_subtitle', 'Detail operationnel de la commande, documents, client, produits et suivi.')

@php
    $statusLabels = [
        'pending_payment' => 'Paiement en attente',
        'confirmed' => 'Confirmee',
        'processing' => 'En traitement',
        'completed' => 'Terminee',
        'cancelled' => 'Annulee',
        'refunded' => 'Remboursee',
    ];
    $paymentLabels = [
        'unpaid' => 'Non payee',
        'authorized' => 'Autorisee',
        'paid' => 'Payee',
        'failed' => 'Echec',
        'partially_refunded' => 'Partiel',
        'refunded' => 'Remboursee',
    ];
    $fulfillmentLabels = [
        'unfulfilled' => 'Non preparee',
        'preparing' => 'En preparation',
        'ready_to_ship' => 'Prete a expedier',
        'shipped' => 'Expediee',
        'delivered' => 'Livree',
        'returned' => 'Retournee',
        'cancelled' => 'Annulee',
    ];
    $carrierLabels = [
        'mondial_relay_pickup' => 'Mondial Relay',
        'chrono_relais_pickup' => 'Chrono Relais',
        'chronopost_home' => 'Chronopost domicile',
    ];
    $stateBadge = function (?string $value): string {
        return match ($value) {
            'paid', 'completed', 'delivered', 'shipped' => 'bg-emerald-600 text-white',
            'confirmed', 'processing', 'preparing', 'ready_to_ship', 'authorized' => 'bg-sky-600 text-white',
            'pending_payment', 'unpaid', 'unfulfilled' => 'bg-amber-500 text-white',
            'failed', 'cancelled', 'refunded', 'returned' => 'bg-red-600 text-white',
            default => 'bg-cocoa/20 text-cocoa dark:bg-white/15 dark:text-cream',
        };
    };
    $status = $order['status'] ?? 'pending_payment';
    $payment = $order['payment_status'] ?? 'unpaid';
    $fulfillment = $order['fulfillment_status'] ?? 'unfulfilled';
    $carrier = $order['carrier'] ?? '';
    $shipping = collect($order['addresses'] ?? [])->firstWhere('type', 'shipping');
    $billing = collect($order['addresses'] ?? [])->firstWhere('type', 'billing');
    $items = $order['items'] ?? [];
    $notes = data_get($order, 'admin_notes', []);
    $pickup = data_get($order, 'metadata.pickup_point');
    $shipment = collect($order['shipments'] ?? [])->first(fn ($item) => filled(data_get($item, 'tracking_number')) || filled(data_get($item, 'external_shipment_id')))
        ?: collect($order['shipments'] ?? [])->first();
    $shipmentCount = count($order['shipments'] ?? []);
    $documentCount = 2 + ($shipment && data_get($shipment, 'has_label') ? 1 : 0);
    $shipmentTrackingNumber = data_get($shipment, 'tracking_number') ?: data_get($shipment, 'external_shipment_id');
    $trackingNumber = data_get($order, 'tracking.number') ?: $shipmentTrackingNumber;
    $trackingUrl = data_get($order, 'tracking.url') ?: ($trackingNumber ? route('pages.tracking', ['locale' => $locale, 'tracking_number' => $trackingNumber]) : null);
    $paymentMethod = data_get($order, 'payment_method') ?: ($paymentLabels[$payment] ?? $payment);
    $dateValue = fn (?string $value) => $value ? Str::of($value)->replace('T', ' ')->before('+')->before('Z') : '-';
    $timeline = [
        ['label' => $paymentLabels[$payment] ?? $payment, 'state' => $payment, 'actor' => 'Systeme paiement', 'date' => $order['placed_at'] ?? $order['created_at'] ?? null],
        ['label' => $statusLabels[$status] ?? $status, 'state' => $status, 'actor' => 'Back-office', 'date' => $order['updated_at'] ?? $order['created_at'] ?? null],
        ['label' => $fulfillmentLabels[$fulfillment] ?? $fulfillment, 'state' => $fulfillment, 'actor' => $carrierLabels[$carrier] ?? 'Logistique', 'date' => data_get($order, 'tracking.updated_at')],
    ];
    $sources = data_get($order, 'metadata.source_events', []);

    if (empty($sources)) {
        $sources = [
            ['date' => $order['created_at'] ?? $order['placed_at'] ?? null, 'from' => 'Boutique web', 'to' => 'Checkout DEN & FILS'],
            ['date' => $order['placed_at'] ?? null, 'from' => 'Panier client', 'to' => 'Commande API'],
        ];
    }

    $conversationStatus = $conversation['status'] ?? 'not_started';
    $conversationMessages = $conversation['messages'] ?? [];
    $staffUnreadCount = (int) ($conversation['staff_unread_count'] ?? 0);
    $customerUnreadCount = (int) ($conversation['customer_unread_count'] ?? 0);
    $conversationStatusLabel = [
        'not_started' => 'A ouvrir',
        'open' => 'Ouverte',
        'closed' => 'Closee',
    ][$conversationStatus] ?? $conversationStatus;
@endphp

@section('content')
    <section class="border border-leaf/10 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <a href="{{ route('admin.orders', ['locale' => $locale]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-ink transition hover:text-leaf dark:text-cream dark:hover:text-meadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-leaf dark:text-meadow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 7h12l-1 12H7L6 7Z" /><path d="M9 7a3 3 0 0 1 6 0" /></svg>
                    Commandes
                </a>
                <h2 class="mt-4 text-3xl font-black tracking-normal text-ink dark:text-cream sm:text-4xl">
                    Commande <span class="text-cocoa/55 dark:text-cream/55">#{{ $order['id'] ?? '-' }}</span> {{ $order['order_number'] ?? '' }}
                </h2>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.orders.invoice', ['locale' => $locale, 'order' => $order['id']]) }}" class="inline-flex min-h-[48px] items-center gap-2 border border-ink/70 px-5 py-3 text-sm font-black text-ink transition hover:bg-ink hover:text-white dark:border-white/60 dark:text-cream dark:hover:bg-white dark:hover:text-ink">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z" /><path d="M8 8h8" /><path d="M8 12h8" /><path d="M8 16h5" /></svg>
                    Facture
                </a>
                <a href="{{ route('admin.orders.delivery-note', ['locale' => $locale, 'order' => $order['id']]) }}" class="inline-flex min-h-[48px] items-center gap-2 border border-ink/70 px-5 py-3 text-sm font-black text-ink transition hover:bg-ink hover:text-white dark:border-white/60 dark:text-cream dark:hover:bg-white dark:hover:text-ink">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h12v9H3z" /><path d="M15 10h3l3 3v3h-6z" /><circle cx="7" cy="18" r="2" /><circle cx="18" cy="18" r="2" /></svg>
                    Bon de livraison
                </a>
                <a href="{{ route('admin.orders.print', ['locale' => $locale, 'order' => $order['id']]) }}" target="_blank" class="inline-flex min-h-[48px] items-center gap-2 border border-ink/70 px-5 py-3 text-sm font-black text-ink transition hover:bg-ink hover:text-white dark:border-white/60 dark:text-cream dark:hover:bg-white dark:hover:text-ink">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7" /><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><path d="M6 14h12v8H6z" /></svg>
                    Imprimer
                </a>
                <button type="button" class="inline-flex min-h-[48px] items-center gap-2 bg-cocoa/15 px-5 py-3 text-sm font-black text-ink transition hover:bg-cocoa/25 dark:bg-white/10 dark:text-cream">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7h-9" /><path d="M14 17H5" /><circle cx="17" cy="17" r="3" /><circle cx="7" cy="7" r="3" /></svg>
                    Booster les ventes
                </button>
                <a href="{{ route('admin.modules.show', ['locale' => $locale, 'module' => 'assistance']) }}" class="inline-flex min-h-[48px] items-center border border-ink/70 px-5 py-3 text-sm font-black text-ink transition hover:bg-ink hover:text-white dark:border-white/60 dark:text-cream dark:hover:bg-white dark:hover:text-ink">Aide</a>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.orders.update', ['locale' => $locale, 'order' => $order['id']]) }}" class="mt-5 grid gap-3 border border-leaf/10 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5 md:grid-cols-[1fr_1fr_1fr_auto_auto]">
        @csrf
        @method('PATCH')
        <select name="status" class="admin-select rounded-none">
            @foreach ($statusLabels as $key => $label)
                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="admin-select rounded-none">
            @foreach ($paymentLabels as $key => $label)
                <option value="{{ $key }}" @selected($payment === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="fulfillment_status" class="admin-select rounded-none">
            @foreach ($fulfillmentLabels as $key => $label)
                <option value="{{ $key }}" @selected($fulfillment === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="admin-btn rounded-none">Mettre a jour l'etat</button>
        <a href="{{ route('admin.orders', ['locale' => $locale]) }}" class="admin-btn-secondary rounded-none">Retour</a>
    </form>

    <section class="mt-5 grid gap-5 xl:grid-cols-[300px_minmax(0,1fr)]">
        <aside class="space-y-5">
            <article class="border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <h3 class="text-2xl font-black text-ink dark:text-cream">Basic information</h3>
                <dl class="mt-6 space-y-4 text-sm">
                    <div><dt class="font-black text-ink dark:text-cream">ID</dt><dd>{{ $order['id'] ?? '-' }}</dd></div>
                    <div><dt class="font-black text-ink dark:text-cream">Reference de commande</dt><dd>{{ $order['order_number'] ?? '-' }}</dd></div>
                    <div><dt class="font-black text-ink dark:text-cream">Created from cart</dt><dd>#{{ data_get($order, 'cart_id', data_get($order, 'metadata.cart_id', '-')) }}</dd></div>
                    <div><dt class="font-black text-ink dark:text-cream">Prix total</dt><dd>{{ $order['formatted_total'] ?? '-' }}</dd></div>
                    <div><dt class="font-black text-ink dark:text-cream">Cree le</dt><dd>{{ $dateValue($order['created_at'] ?? $order['placed_at'] ?? null) }}</dd></div>
                    <div><dt class="font-black text-ink dark:text-cream">Paiement</dt><dd>{{ $paymentMethod }}</dd></div>
                    <div><dt class="font-black text-ink dark:text-cream">Livraison</dt><dd>{{ $carrierLabels[$carrier] ?? $carrier ?: '-' }}</dd></div>
                </dl>
            </article>

            <article class="border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <h3 class="text-2xl font-black text-ink dark:text-cream">Client</h3>
                <div class="mt-6 bg-linen p-4 dark:bg-white/5">
                    <p class="text-xl font-black text-ink dark:text-cream">{{ data_get($order, 'customer.name', '-') }}</p>
                    <p class="mt-2 inline-flex bg-ink px-3 py-1 text-xs font-black text-white">Afficher les details</p>
                    <p class="mt-4 inline-flex rounded-full bg-white px-4 py-2 text-sm font-black text-cocoa ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream">Client enregistre</p>
                </div>
                <dl class="mt-5 space-y-2 text-sm">
                    <div><dt class="inline font-black">E-mail : </dt><dd class="inline">{{ data_get($order, 'customer.email', '-') }}</dd></div>
                    <div><dt class="inline font-black">Telephone : </dt><dd class="inline">{{ data_get($order, 'customer.phone', '-') }}</dd></div>
                    <div><dt class="inline font-black">Pays : </dt><dd class="inline">{{ data_get($order, 'customer.country_code', '-') }}</dd></div>
                    <div><dt class="inline font-black">Commandes validees : </dt><dd class="inline">{{ data_get($order, 'customer.orders_count', '-') }}</dd></div>
                </dl>
            </article>

            <article class="grid gap-3 border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 md:grid-cols-2 xl:grid-cols-1">
                @foreach ([['title' => 'Adresse de livraison', 'address' => $shipping], ['title' => 'Adresse de facturation', 'address' => $billing]] as $block)
                    <div class="bg-linen p-4 dark:bg-white/5">
                        <h4 class="font-black text-ink dark:text-cream">{{ $block['title'] }}</h4>
                        <p class="mt-3 text-sm leading-6 text-cocoa/75 dark:text-cream/75">
                            {{ data_get($block['address'], 'recipient_name', '-') }}<br>
                            {{ data_get($block['address'], 'street_line_1', '-') }}<br>
                            {{ data_get($block['address'], 'street_line_2') }}<br>
                            {{ data_get($block['address'], 'postal_code', '') }} {{ data_get($block['address'], 'city', '') }}<br>
                            {{ data_get($block['address'], 'country_code', '') }}
                        </p>
                    </div>
                @endforeach
            </article>
        </aside>

        <div class="space-y-5">
            <article class="border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-7">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-3xl font-black text-ink dark:text-cream">Produits ({{ count($items) }})</h3>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="admin-btn-secondary rounded-none">Ajouter un produit</button>
                        <button type="button" class="admin-btn-secondary rounded-none">Ajouter une reduction</button>
                    </div>
                </div>

                <div class="mt-7 overflow-x-auto">
                    <table class="min-w-[880px] w-full text-left text-sm">
                        <thead>
                            <tr class="border-b-2 border-ink text-ink dark:border-white dark:text-cream">
                                <th class="px-3 py-3">Produit</th>
                                <th class="px-3 py-3">Prix unitaire<br><span class="font-normal text-cocoa/55 dark:text-cream/55">TTC</span></th>
                                <th class="px-3 py-3">Quantite</th>
                                <th class="px-3 py-3">Disponible</th>
                                <th class="px-3 py-3">Total<br><span class="font-normal text-cocoa/55 dark:text-cream/55">TTC</span></th>
                                <th class="px-3 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-leaf/10 dark:divide-white/10">
                            @forelse ($items as $item)
                                <tr>
                                    <td class="px-3 py-4">
                                        <div class="flex items-center gap-4">
                                            @if (data_get($item, 'product.image.url'))
                                                <img src="{{ data_get($item, 'product.image.url') }}" alt="" class="h-14 w-14 object-cover">
                                            @else
                                                <span class="grid h-14 w-14 place-items-center bg-linen text-xs font-black text-cocoa/45 dark:bg-white/10 dark:text-cream/45">DF</span>
                                            @endif
                                            <div>
                                                <p class="font-semibold text-ink dark:text-cream">{{ data_get($item, 'product.name', '-') }}</p>
                                                <p class="mt-1 text-xs text-cocoa/55 dark:text-cream/55">{{ data_get($item, 'product.sku', '-') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4">{{ $item['formatted_unit_price'] ?? '-' }}</td>
                                    <td class="px-3 py-4">{{ $item['quantity'] ?? 0 }}</td>
                                    <td class="px-3 py-4">{{ data_get($item, 'available_quantity', '-') }}</td>
                                    <td class="px-3 py-4 font-semibold">{{ $item['formatted_line_total'] ?? '-' }}</td>
                                    <td class="px-3 py-4 text-right">
                                        <span class="inline-flex gap-3">
                                            <button type="button" class="transition hover:text-leaf" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 5 5L8 21H3v-5L16 3Z" /></svg></button>
                                            <button type="button" class="transition hover:text-red-600" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /></svg></button>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-8 text-center text-cocoa/55 dark:text-cream/55">Aucun produit dans cette commande.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 ml-auto grid max-w-md gap-2 bg-linen p-5 text-sm dark:bg-white/5">
                    <div class="flex justify-between"><span>Produits</span><strong>{{ $order['formatted_subtotal'] ?? '-' }}</strong></div>
                    <div class="flex justify-between"><span>Livraison</span><strong>{{ $order['formatted_shipping'] ?? '-' }}</strong></div>
                    <div class="flex justify-between"><span>TVA</span><strong>{{ $order['formatted_tax'] ?? '-' }}</strong></div>
                    <div class="flex justify-between border-t border-leaf/10 pt-3 text-lg dark:border-white/10"><span class="font-black">Total</span><strong class="bg-ink px-2 py-1 text-white">{{ $order['formatted_total'] ?? '-' }}</strong></div>
                </div>
                <p class="mt-5 text-center text-sm text-cocoa/60 dark:text-cream/60">Pour ce groupe de clients, les prix sont affiches : <strong>TTC</strong>.</p>
            </article>

            <article class="border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-7" x-data="{ activeOrderTab: 'state' }">
                <div class="flex flex-wrap gap-2 border-b border-leaf/10 dark:border-white/10">
                    @foreach ([['key' => 'state', 'label' => 'Etat', 'count' => count($timeline)], ['key' => 'documents', 'label' => 'Documents', 'count' => $documentCount], ['key' => 'carrier', 'label' => 'Transporteurs', 'count' => $shipmentCount], ['key' => 'returns', 'label' => 'Retours produit', 'count' => 0]] as $tab)
                        <button type="button" x-on:click="activeOrderTab = '{{ $tab['key'] }}'" class="inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition" x-bind:class="activeOrderTab === '{{ $tab['key'] }}' ? 'border-ink text-ink dark:border-white dark:text-cream' : 'border-transparent text-cocoa/70 hover:text-ink dark:text-cream/70 dark:hover:text-cream'">{{ $tab['label'] }} ({{ $tab['count'] }})</button>
                    @endforeach
                </div>

                <div class="mt-5 space-y-3" x-show="activeOrderTab === 'state'">
                    @foreach ($timeline as $event)
                        <div class="grid gap-3 border-b border-leaf/10 py-3 text-sm dark:border-white/10 md:grid-cols-[1fr_180px_220px_auto] md:items-center">
                            <span><span class="inline-flex px-2 py-1 text-xs font-black {{ $stateBadge($event['state'] ?? null) }}">{{ $event['label'] }}</span></span>
                            <span>{{ $event['actor'] }}</span>
                            <span>{{ $dateValue($event['date']) }}</span>
                            <button type="button" class="justify-self-start bg-ink px-4 py-2 text-xs font-black text-white md:justify-self-end">Renvoyer l'e-mail</button>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('admin.orders.update', ['locale' => $locale, 'order' => $order['id']]) }}" class="mt-8 grid gap-3 md:grid-cols-[1fr_auto]" x-show="activeOrderTab === 'state'">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="payment_status" value="{{ $payment }}">
                    <input type="hidden" name="fulfillment_status" value="{{ $fulfillment }}">
                    <textarea name="admin_note" class="admin-textarea rounded-none" placeholder="Order private note">{{ old('admin_note') }}</textarea>
                    <button class="admin-btn rounded-none self-start">Ajouter la note</button>
                </form>

                <div class="mt-6" x-show="activeOrderTab === 'documents'">
                    <div class="bg-linen p-4 dark:bg-white/5">
                        <h4 class="text-xl font-black">Documents</h4>
                        <div class="mt-4 grid gap-2">
                            <a href="{{ route('admin.orders.invoice', ['locale' => $locale, 'order' => $order['id']]) }}" class="admin-btn-secondary rounded-none">Telecharger la facture</a>
                            <a href="{{ route('admin.orders.delivery-note', ['locale' => $locale, 'order' => $order['id']]) }}" class="admin-btn-secondary rounded-none">Telecharger le bon de livraison</a>
                            <a href="{{ route('admin.orders.print', ['locale' => $locale, 'order' => $order['id']]) }}" target="_blank" class="admin-btn-secondary rounded-none">Ouvrir la version imprimable</a>
                            @if ($shipment && data_get($shipment, 'has_label'))
                                <a href="{{ route('admin.orders.shipment.label', ['locale' => $locale, 'order' => $order['id'], 'shipment' => data_get($shipment, 'id')]) }}" class="admin-btn rounded-none">Telecharger l'etiquette transporteur</a>
                            @else
                                <span class="rounded-xl bg-white px-4 py-3 text-sm font-bold text-cocoa/55 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/55">Etiquette transporteur non disponible.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-6" x-show="activeOrderTab === 'carrier'">
                    <div class="bg-linen p-4 dark:bg-white/5">
                        <h4 class="text-xl font-black">Transporteur</h4>
                        <p class="mt-3 text-sm">{{ $carrierLabels[$carrier] ?? $carrier ?: '-' }}</p>
                        @if ($shipment)
                            <p class="mt-1 text-sm">Statut expedition: {{ data_get($shipment, 'status', '-') }}</p>
                            @if (data_get($shipment, 'last_error'))
                                <p class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-bold leading-5 text-red-700">{{ data_get($shipment, 'last_error') }}</p>
                            @endif
                        @endif
                        <p class="mt-1 text-sm">Suivi: {{ $trackingNumber ?: '-' }}</p>
                        @if ($shipmentTrackingNumber && data_get($order, 'tracking.number') && data_get($order, 'tracking.number') !== $shipmentTrackingNumber)
                            <p class="mt-1 text-xs text-cocoa/55 dark:text-cream/55">Numero Mondial Relay: {{ $shipmentTrackingNumber }}</p>
                        @endif
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($trackingUrl)
                                <a href="{{ $trackingUrl }}" class="admin-btn-secondary rounded-none">Ouvrir le suivi</a>
                            @endif
                            @if ($shipment && data_get($shipment, 'has_label'))
                                <a href="{{ route('admin.orders.shipment.label', ['locale' => $locale, 'order' => $order['id'], 'shipment' => data_get($shipment, 'id')]) }}" class="admin-btn rounded-none">Telecharger etiquette</a>
                            @endif
                            @if ($shipment)
                                <form method="POST" action="{{ route('admin.orders.shipment.create', ['locale' => $locale, 'order' => $order['id']]) }}">
                                    @csrf
                                    <button class="admin-btn-secondary rounded-none" type="submit">{{ in_array(data_get($shipment, 'status'), ['creation_failed', 'label_failed'], true) ? 'Relancer expedition' : 'Generer expedition' }}</button>
                                </form>
                            @endif
                        </div>
                        @if ($pickup)
                            <p class="mt-3 text-xs leading-5 text-cocoa/65 dark:text-cream/65">{{ data_get($pickup, 'name') }} - {{ data_get($pickup, 'address') }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-6" x-show="activeOrderTab === 'returns'">
                    <div class="bg-linen p-4 text-sm font-semibold text-cocoa/65 dark:bg-white/5 dark:text-cream/65">
                        Aucun retour produit declare pour cette commande.
                    </div>
                </div>

                <div class="mt-6" x-show="activeOrderTab === 'state'">
                    <div class="bg-linen p-4 dark:bg-white/5">
                        <h4 class="text-xl font-black">Paiement</h4>
                        <p class="mt-3 text-sm">{{ $paymentMethod }}</p>
                        <p class="mt-1 text-sm">{{ $paymentLabels[$payment] ?? $payment }}</p>
                        <p class="mt-1 text-sm font-black">{{ $order['formatted_total'] ?? '-' }}</p>
                    </div>
                </div>
            </article>

            <section class="grid gap-5 xl:grid-cols-2">
                <article class="rounded-2xl border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 xl:col-span-2">
                    <div class="flex flex-col gap-4 border-b border-leaf/10 pb-5 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-leaf dark:text-meadow">SAV commande</p>
                            <h3 class="mt-2 text-2xl font-black text-ink dark:text-cream">Discussion client</h3>
                            <p class="mt-1 text-sm font-semibold text-cocoa/60 dark:text-cream/60">Messages visibles par le client depuis son espace commande.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex min-h-[34px] items-center rounded-full px-3 text-xs font-black uppercase tracking-wide {{ $conversationStatus === 'closed' ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-200' : 'bg-mint text-leaf dark:bg-white/10 dark:text-meadow' }}">
                                {{ $conversationStatusLabel }}
                            </span>
                            @if ($staffUnreadCount > 0)
                                <form method="POST" action="{{ route('admin.orders.discussion.read', ['locale' => $locale, 'order' => $order['id']]) }}">
                                    @csrf
                                    <button class="inline-flex min-h-[34px] items-center rounded-full bg-amber-500 px-3 text-xs font-black uppercase tracking-wide text-white transition hover:bg-amber-600">
                                        {{ $staffUnreadCount }} non lu{{ $staffUnreadCount > 1 ? 's' : '' }} - marquer lu
                                    </button>
                                </form>
                            @endif
                            @if ($customerUnreadCount > 0)
                                <span class="inline-flex min-h-[34px] items-center rounded-full bg-cocoa/10 px-3 text-xs font-black uppercase tracking-wide text-cocoa dark:bg-white/10 dark:text-cream">
                                    {{ $customerUnreadCount }} non lu client
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
                        <div class="chat-thread">
                            @forelse ($conversationMessages as $message)
                                @php
                                    $isStaff = ($message['sender_type'] ?? null) === 'staff';
                                    $messageStatus = $message['status_for_staff'] ?? $message['status'] ?? 'read';
                                @endphp
                                <article class="chat-bubble {{ $isStaff ? 'chat-bubble-own' : 'chat-bubble-other' }}">
                                    <div class="chat-meta">
                                        <p class="{{ $isStaff ? 'text-cream/70' : 'text-cocoa/45 dark:text-cream/45' }}">
                                            {{ $isStaff ? 'Equipe support' : 'Client' }}
                                        </p>
                                        <span class="shrink-0 {{ $messageStatus === 'unread' ? 'text-amber-600 dark:text-amber-300' : ($isStaff ? 'text-cream/60' : 'text-cocoa/45 dark:text-cream/45') }}">
                                            {{ $messageStatus === 'unread' ? 'Non lu' : 'Lu' }}
                                        </span>
                                    </div>
                                    <p class="mt-2 whitespace-pre-line font-semibold leading-6">{{ $message['body'] ?? '' }}</p>
                                    @if (! empty($message['created_at']))
                                        <p class="mt-2 text-[11px] font-semibold {{ $isStaff ? 'text-cream/55' : 'text-cocoa/45 dark:text-cream/45' }}">
                                            {{ $dateValue($message['created_at']) }}
                                        </p>
                                    @endif
                                </article>
                            @empty
                                <div class="rounded-xl border border-dashed border-leaf/20 bg-white p-6 text-sm font-semibold leading-6 text-cocoa/60 dark:border-white/10 dark:bg-white/5 dark:text-cream/60">
                                    Aucune discussion client pour cette commande. Ouvrez le chat pour envoyer un premier message.
                                </div>
                            @endforelse
                        </div>

                        <aside class="rounded-2xl border border-leaf/10 bg-linen p-4 dark:border-white/10 dark:bg-white/[0.03]">
                            @if ($conversationStatus === 'open')
                                <form method="POST" action="{{ route('admin.orders.discussion.messages', ['locale' => $locale, 'order' => $order['id']]) }}" class="grid gap-3">
                                    @csrf
                                    <label for="admin-order-message-body" class="text-sm font-black text-ink dark:text-cream">Reponse au client</label>
                                    <div class="chat-composer">
                                        <textarea id="admin-order-message-body" name="body" required rows="7" maxlength="2000" class="chat-textarea min-h-[170px]" placeholder="Ecrire une reponse claire au client...">{{ old('body') }}</textarea>
                                        <div class="chat-toolbar">
                                            <span class="text-xs font-semibold text-cocoa/50 dark:text-cream/50">Visible par le client</span>
                                            <button class="inline-flex min-h-[42px] items-center justify-center rounded-full bg-forest px-4 text-sm font-black uppercase tracking-wide text-white transition hover:bg-leaf" type="submit">
                                                Envoyer
                                            </button>
                                        </div>
                                    </div>
                                    <button type="submit" formnovalidate formaction="{{ route('admin.orders.discussion.close', ['locale' => $locale, 'order' => $order['id']]) }}" class="inline-flex min-h-[42px] items-center justify-center rounded-full border border-red-200 bg-red-50 px-4 text-sm font-black uppercase tracking-wide text-red-700 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-200">
                                        Clore la discussion
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.orders.discussion.open', ['locale' => $locale, 'order' => $order['id']]) }}">
                                    @csrf
                                    <button class="inline-flex min-h-[46px] w-full items-center justify-center rounded-full bg-forest px-5 text-sm font-black uppercase tracking-wide text-white transition hover:bg-leaf" type="submit">
                                        {{ $conversationStatus === 'closed' ? 'Rouvrir la discussion' : 'Ouvrir le chat client' }}
                                    </button>
                                </form>
                            @endif
                        </aside>
                    </div>
                </article>

                <article class="border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <h3 class="text-2xl font-black text-ink dark:text-cream">Messages ({{ count($notes) }})</h3>
                    <div class="mt-5 space-y-3">
                        @forelse ($notes as $note)
                            <div class="bg-linen p-4 text-sm dark:bg-white/5">
                                <p class="font-semibold">{{ $note['body'] ?? '' }}</p>
                                <p class="mt-2 text-xs text-cocoa/50 dark:text-cream/50">{{ $note['actor_name'] ?? 'Admin' }} - {{ $dateValue($note['created_at'] ?? null) }}</p>
                            </div>
                        @empty
                            <p class="bg-linen p-4 text-sm text-cocoa/55 dark:bg-white/5 dark:text-cream/55">Aucun message interne pour cette commande.</p>
                        @endforelse
                    </div>
                </article>

                <article class="border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <h3 class="text-2xl font-black text-ink dark:text-cream">Sources <span class="bg-ink px-2 py-1 text-xs text-white">{{ count($sources) }}</span></h3>
                    <ul class="mt-5 space-y-5 text-sm">
                        @foreach ($sources as $source)
                            <li class="relative pl-4 before:absolute before:-ml-4 before:mt-2 before:h-1.5 before:w-1.5 before:rounded-full before:bg-ink">
                                <p>{{ $dateValue($source['date'] ?? null) }}</p>
                                <p><strong>Du</strong> {{ $source['from'] ?? '-' }}</p>
                                <p><strong>Au</strong> {{ $source['to'] ?? '-' }}</p>
                            </li>
                        @endforeach
                    </ul>
                </article>
            </section>
        </div>
    </section>
@endsection
