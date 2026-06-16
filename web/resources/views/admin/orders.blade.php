@extends('layouts.admin')

@section('title', 'Commandes')
@section('page_title', 'Commandes')
@section('page_subtitle', 'Suivi des commandes web, paiement, preparation et expedition.')

@php
    $rows = data_get($orders, 'data', []);
    $summary = data_get($orders, 'summary', []);
    $totalRows = data_get($orders, 'meta.total', data_get($summary, 'total_orders', count($rows)));
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
        'ready_to_ship' => 'Prete',
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
    $dateValue = fn (?string $value) => $value ? Str::of($value)->replace('T', ' ')->before('+')->before('Z') : '-';
@endphp

@section('content')
    @if (! data_get($orders, 'ok', true))
        <div class="mb-5 rounded border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
            {{ data_get($orders, 'message', 'Commandes indisponibles pour le moment.') }}
        </div>
    @endif

    <section class="border border-leaf/10 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm font-semibold text-ink dark:text-cream">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-leaf dark:text-meadow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 7h12l-1 12H7L6 7Z" /><path d="M9 7a3 3 0 0 1 6 0" /></svg>
                    <span>Commandes</span>
                </div>
                <h2 class="mt-2 text-4xl font-black tracking-normal text-ink dark:text-cream">Commandes</h2>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" data-dialog-target="order-create-modal" class="inline-flex min-h-[48px] items-center gap-2 bg-ink px-5 py-3 text-sm font-black text-white transition hover:bg-leaf dark:bg-meadow dark:text-ink">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 8v8" /><path d="M8 12h8" /></svg>
                    Ajouter une commande
                </button>
                <button type="button" class="inline-flex min-h-[48px] items-center gap-2 bg-cocoa/15 px-5 py-3 text-sm font-black text-ink transition hover:bg-cocoa/25 dark:bg-white/10 dark:text-cream">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7h-9" /><path d="M14 17H5" /><circle cx="17" cy="17" r="3" /><circle cx="7" cy="7" r="3" /></svg>
                    Booster les ventes
                </button>
                <a href="{{ route('admin.modules.show', ['locale' => $locale, 'module' => 'assistance']) }}" class="inline-flex min-h-[48px] items-center border border-ink/70 px-5 py-3 text-sm font-black text-ink transition hover:bg-ink hover:text-white dark:border-white/60 dark:text-cream dark:hover:bg-white dark:hover:text-ink">
                    Aide
                </a>
            </div>
        </div>
    </section>

    <section class="mt-5 grid border border-leaf/10 bg-white shadow-sm dark:border-white/10 dark:bg-white/5 lg:grid-cols-[1fr_1fr_1fr_1fr_64px]">
        @foreach ([
            [
                'label' => 'Taux de transformation',
                'value' => number_format((float) data_get($summary, 'conversion_rate_percent', 0), 1, ',', ' ').'%',
                'hint' => '30 jours',
                'color' => 'text-ink dark:text-cream',
                'icon' => '<path d="M4 20V4h16v16H4Z" /><path d="M8 16v-5" /><path d="M12 16V8" /><path d="M16 16v-3" />',
            ],
            [
                'label' => 'Paniers abandonnes',
                'value' => data_get($summary, 'abandoned_carts', 0),
                'hint' => 'paniers sans commande',
                'color' => 'text-red-600 dark:text-red-300',
                'icon' => '<path d="M6 6h15l-2 8H8L6 3H3" /><circle cx="9" cy="20" r="1" /><circle cx="18" cy="20" r="1" />',
            ],
            [
                'label' => 'Panier moyen',
                'value' => data_get($summary, 'formatted_average_order', '0,00 EUR'),
                'hint' => '30 jours',
                'color' => 'text-purple-600 dark:text-purple-300',
                'icon' => '<rect x="5" y="4" width="14" height="16" rx="2" /><path d="M9 8h6" /><path d="M9 12h6" /><path d="M9 16h3" />',
            ],
            [
                'label' => 'Marge nette par visiteur',
                'value' => data_get($summary, 'formatted_net_margin_per_visitor', '0,00 EUR'),
                'hint' => '30 jours',
                'color' => 'text-emerald-600 dark:text-emerald-300',
                'icon' => '<rect x="4" y="4" width="16" height="16" rx="2" /><circle cx="12" cy="10" r="3" /><path d="M7 19a5 5 0 0 1 10 0" />',
            ],
        ] as $metric)
            <article class="flex min-h-[100px] items-center gap-4 border-b border-leaf/10 p-5 dark:border-white/10 lg:border-b-0 lg:border-r">
                <span class="{{ $metric['color'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $metric['icon'] !!}</svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-ink dark:text-cream">{{ $metric['label'] }}</p>
                    <div class="mt-1 flex flex-wrap items-baseline gap-2">
                        <strong class="text-2xl font-normal text-ink dark:text-cream">{{ $metric['value'] }}</strong>
                        <span class="text-xs font-semibold uppercase text-cocoa/40 dark:text-cream/40">{{ $metric['hint'] }}</span>
                    </div>
                </div>
            </article>
        @endforeach
        <a href="{{ route('admin.orders', ['locale' => $locale]) }}" class="grid min-h-[64px] place-items-center bg-ink text-white transition hover:bg-leaf" aria-label="Rafraichir les commandes">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-3-6.7" /><path d="M21 3v6h-6" /></svg>
        </a>
    </section>

    <section class="mt-7 border border-leaf/10 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-7">
        <div class="mb-10 flex items-start justify-between gap-4">
            <div>
                <h3 class="text-3xl font-black text-ink dark:text-cream">Commandes ({{ $totalRows }})</h3>
            </div>
            <button type="button" class="admin-icon-btn" aria-label="Parametres liste">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.36.17.7.38 1 .6.33.25.7.4 1.1.4H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.51 1Z" /></svg>
            </button>
        </div>

        <form method="GET">
            <div class="mb-5 inline-flex min-h-[48px] items-center gap-3 bg-cocoa/5 px-5 text-sm font-black text-cocoa/35 dark:bg-white/5 dark:text-cream/35">
                Actions groupees
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1320px] w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b-2 border-ink text-sm font-black text-ink dark:border-white dark:text-cream">
                            <th class="w-12 px-2 py-3"></th>
                            <th class="w-20 px-3 py-3">ID <span class="ml-2 text-xs">^</span></th>
                            <th class="w-40 px-3 py-3">Reference</th>
                            <th class="w-28 px-3 py-3">Nouveau client</th>
                            <th class="w-32 px-3 py-3">Livraison</th>
                            <th class="w-52 px-3 py-3">Client</th>
                            <th class="w-32 px-3 py-3">Total</th>
                            <th class="w-40 px-3 py-3">Paiement</th>
                            <th class="px-3 py-3">Etat</th>
                            <th class="w-44 px-3 py-3">Date</th>
                            <th class="w-36 px-3 py-3 text-right">Actions</th>
                        </tr>
                        <tr class="border-b border-leaf/10 bg-cocoa/[0.03] dark:border-white/10 dark:bg-white/[0.03]">
                            <th class="px-2 py-4"><input type="checkbox" class="h-6 w-6 rounded-none border-cocoa/30"></th>
                            <th class="px-3 py-4">
                                <input name="id" value="{{ $filters['id'] ?? '' }}" class="admin-input rounded-none px-2" placeholder="ID">
                            </th>
                            <th class="px-3 py-4">
                                <input name="q" value="{{ $filters['q'] ?? '' }}" class="admin-input rounded-none px-2" placeholder="Chercher une reference">
                            </th>
                            <th class="px-3 py-4">
                                <select name="new_customer" class="admin-select rounded-none px-2">
                                    <option value="">Tous</option>
                                    <option value="1" @selected(($filters['new_customer'] ?? '') === '1')>Oui</option>
                                    <option value="0" @selected(($filters['new_customer'] ?? '') === '0')>Non</option>
                                </select>
                            </th>
                            <th class="px-3 py-4">
                                <select name="carrier" class="admin-select rounded-none px-2">
                                    <option value=""></option>
                                    @foreach ($carrierLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['carrier'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="px-3 py-4">
                                <input name="customer" value="{{ $filters['customer'] ?? '' }}" class="admin-input rounded-none px-2" placeholder="Chercher un client">
                            </th>
                            <th class="px-3 py-4">
                                <input name="total" value="{{ $filters['total'] ?? '' }}" class="admin-input rounded-none px-2" placeholder="Chercher">
                            </th>
                            <th class="px-3 py-4">
                                <select name="payment_status" class="admin-select rounded-none px-2">
                                    <option value="">Chercher un paiement</option>
                                    @foreach ($paymentLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['payment_status'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="px-3 py-4">
                                <select name="fulfillment_status" class="admin-select rounded-none px-2">
                                    <option value=""></option>
                                    @foreach ($fulfillmentLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['fulfillment_status'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="px-3 py-4">
                                <div class="grid gap-2">
                                    <input name="date_from" value="{{ $filters['date_from'] ?? '' }}" type="date" class="admin-input rounded-none px-2">
                                    <input name="date_to" value="{{ $filters['date_to'] ?? '' }}" type="date" class="admin-input rounded-none px-2">
                                </div>
                            </th>
                            <th class="px-3 py-4 text-right">
                                <button class="inline-flex min-h-[46px] items-center gap-2 bg-cocoa/35 px-5 py-2 text-sm font-black text-white transition hover:bg-ink">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path d="m20 20-3-3" /></svg>
                                    Rechercher
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-leaf/10 dark:divide-white/10">
                        @forelse ($rows as $order)
                            @php
                                $orderId = $order['id'] ?? null;
                                $status = $order['status'] ?? null;
                                $payment = $order['payment_status'] ?? null;
                                $fulfillment = $order['fulfillment_status'] ?? null;
                                $carrier = $order['carrier'] ?? null;
                                $shipping = collect($order['addresses'] ?? [])->firstWhere('type', 'shipping');
                                $isNew = (bool) data_get($order, 'is_new_customer', true);
                                $paymentMethod = data_get($order, 'payment_method') ?: ($paymentLabels[$payment] ?? $payment ?? '-');
                            @endphp
                            <tr class="hover:bg-linen dark:hover:bg-white/5">
                                <td class="px-2 py-4"><input type="checkbox" class="h-6 w-6 rounded-none border-cocoa/30"></td>
                                <td class="px-3 py-4 font-semibold text-ink dark:text-cream">{{ $orderId ?? '-' }}</td>
                                <td class="px-3 py-4 font-semibold text-ink dark:text-cream">{{ $order['order_number'] ?? '-' }}</td>
                                <td class="px-3 py-4 text-ink dark:text-cream">{{ $isNew ? 'Oui' : 'Non' }}</td>
                                <td class="px-3 py-4 text-ink dark:text-cream">{{ data_get($shipping, 'country_code', '-') }}</td>
                                <td class="px-3 py-4 text-ink dark:text-cream">{{ data_get($order, 'customer.name', '-') }}</td>
                                <td class="px-3 py-4">
                                    <span class="inline-flex bg-emerald-700 px-2 py-1 text-sm font-black text-white">{{ $order['formatted_total'] ?? '-' }}</span>
                                </td>
                                <td class="px-3 py-4 text-ink dark:text-cream">{{ $paymentMethod }}</td>
                                <td class="px-3 py-4">
                                    <span class="inline-flex px-2 py-1 text-sm font-black {{ $stateBadge($fulfillment) }}">{{ $fulfillmentLabels[$fulfillment] ?? $statusLabels[$status] ?? $fulfillment ?? '-' }}</span>
                                </td>
                                <td class="px-3 py-4 leading-6 text-ink dark:text-cream">{{ $dateValue($order['placed_at'] ?? $order['created_at'] ?? null) }}</td>
                                <td class="px-3 py-4">
                                    <div class="flex justify-end gap-3 text-ink dark:text-cream">
                                        @if ($orderId)
                                            <a href="{{ route('admin.orders.invoice', ['locale' => $locale, 'order' => $orderId]) }}" class="transition hover:text-leaf" title="Telecharger la facture" aria-label="Telecharger la facture">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z" /><path d="M8 8h8" /><path d="M8 12h8" /><path d="M8 16h5" /></svg>
                                            </a>
                                            <a href="{{ route('admin.orders.delivery-note', ['locale' => $locale, 'order' => $orderId]) }}" class="transition hover:text-leaf" title="Telecharger le bon de livraison" aria-label="Telecharger le bon de livraison">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h12v9H3z" /><path d="M15 10h3l3 3v3h-6z" /><circle cx="7" cy="18" r="2" /><circle cx="18" cy="18" r="2" /></svg>
                                            </a>
                                            <a href="{{ route('admin.orders.show', ['locale' => $locale, 'order' => $orderId]) }}" class="transition hover:text-leaf" title="Voir le detail de la commande" aria-label="Voir le detail de la commande">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="6" /><path d="m20 20-4-4" /><path d="M11 8v6" /><path d="M8 11h6" /></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-8 text-center text-cocoa/55 dark:text-cream/55">Aucune commande ne correspond aux filtres.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </section>
@endsection

@push('admin_modals')
    <dialog id="order-create-modal" class="admin-dialog" @if(session('admin_modal') === 'order-create') data-open-on-load @endif>
        <form method="POST" action="{{ route('admin.orders.store', ['locale' => $locale]) }}" class="admin-modal-card p-5 sm:p-6">
            @csrf
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="admin-kicker">Nouvelle commande</p>
                    <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">Ajouter une commande</h2>
                </div>
                <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                </button>
            </div>

            @if(session('admin_modal') === 'order-create' && $errors->any())
                <div class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mt-5 grid gap-3">
                <label class="block">
                    <span class="admin-kicker mb-2 block">ID client</span>
                    <input name="user_id" value="{{ old('user_id') }}" type="number" min="1" class="admin-input rounded-none" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Token panier</span>
                    <input name="cart_token" value="{{ old('cart_token') }}" class="admin-input rounded-none" required>
                </label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                        <span class="admin-kicker mb-2 block">Adresse livraison ID</span>
                        <input name="shipping_address_id" value="{{ old('shipping_address_id') }}" type="number" min="1" class="admin-input rounded-none" required>
                    </label>
                    <label class="block">
                        <span class="admin-kicker mb-2 block">Adresse facturation ID</span>
                        <input name="billing_address_id" value="{{ old('billing_address_id') }}" type="number" min="1" class="admin-input rounded-none">
                    </label>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                        <span class="admin-kicker mb-2 block">Mode livraison</span>
                        <select name="delivery_method" class="admin-select rounded-none">
                            <option value="standard" @selected(old('delivery_method') === 'standard')>Domicile</option>
                            <option value="relay" @selected(old('delivery_method') === 'relay')>Point relais</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="admin-kicker mb-2 block">Transporteur</span>
                        <select name="carrier" class="admin-select rounded-none">
                            <option value="">Non renseigne</option>
                            @foreach ($carrierLabels as $key => $label)
                                <option value="{{ $key }}" @selected(old('carrier') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Note interne</span>
                    <textarea name="admin_note" class="admin-textarea rounded-none" placeholder="Contexte de creation, demande client, consigne commerciale...">{{ old('admin_note') }}</textarea>
                </label>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <button type="button" data-dialog-close class="admin-btn-secondary rounded-none">Annuler</button>
                <button class="admin-btn rounded-none">Creer la commande</button>
            </div>
        </form>
    </dialog>

@endpush
