@extends('layouts.admin')

@section('title', 'Pilotage')
@section('page_title', 'Pilotage general')
@section('page_subtitle', 'Vue rapide du catalogue, du stock, des paniers et des actions sensibles.')

@php
    $data = $dashboard['data'] ?? [];
    $kpis = $data['kpis'] ?? [];
    $catalog = $kpis['catalog'] ?? [];
    $inventory = $kpis['inventory'] ?? [];
    $carts = $kpis['carts'] ?? [];
    $identity = $kpis['identity'] ?? [];
    $health = $data['catalog_health'] ?? [];
    $stockAlerts = $data['stock_alerts'] ?? [];
    $recentActivity = $data['recent_activity'] ?? [];
    $cards = [
        ['label' => 'Produits actifs', 'value' => $catalog['products_active'] ?? 0, 'hint' => ($catalog['products_total'] ?? 0) . ' produits au total'],
        ['label' => 'Unites disponibles', 'value' => $inventory['total_units_available'] ?? 0, 'hint' => ($inventory['low_stock_products'] ?? 0) . ' alertes stock faible'],
        ['label' => 'Paniers actifs', 'value' => $carts['active_count'] ?? 0, 'hint' => $carts['formatted_active_value'] ?? '0,00 EUR'],
        ['label' => 'Utilisateurs', 'value' => $identity['users_total'] ?? 0, 'hint' => ($identity['customers_total'] ?? 0) . ' clients'],
    ];
    $workspaces = [
        ['label' => 'Vendre', 'title' => 'Commandes & paniers', 'href' => route('admin.modules.show', ['locale' => $locale, 'module' => 'commandes']), 'items' => ['Commandes', 'Factures', 'Avoirs', 'Paniers']],
        ['label' => 'Catalogue', 'title' => 'Produits & stock', 'href' => route('admin.catalog.products', ['locale' => $locale]), 'items' => ['Produits', 'Categories', 'Stock', 'Reductions']],
        ['label' => 'CRM', 'title' => 'Clients & SAV', 'href' => route('admin.users', ['locale' => $locale]), 'items' => ['Clients', 'Adresses', 'SAV', 'Retours']],
        ['label' => 'Configurer', 'title' => 'Parametres & audit', 'href' => route('admin.access', ['locale' => $locale]), 'items' => ['Roles', 'Audit', 'Paiement', 'Livraison']],
    ];
    $pipeline = [
        ['label' => 'Commande recue', 'hint' => 'Verification paiement et adresse'],
        ['label' => 'Preparation', 'hint' => 'Stock, picking et bon de livraison'],
        ['label' => 'Expedition', 'hint' => 'Transporteur et suivi client'],
        ['label' => 'SAV / fidelisation', 'hint' => 'Retours, avis et relance CRM'],
    ];
@endphp

@section('content')
    @if (! ($dashboard['ok'] ?? false))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
            {{ $dashboard['message'] ?? 'Impossible de charger le dashboard admin.' }}
        </div>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <article class="admin-card p-5">
                <p class="admin-kicker">{{ $card['label'] }}</p>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <strong class="text-4xl font-black text-ink dark:text-cream">{{ $card['value'] }}</strong>
                    <span class="admin-pill">Live API</span>
                </div>
                <p class="mt-3 text-sm font-semibold text-cocoa/55 dark:text-cream/55">{{ $card['hint'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[1fr_420px]">
        <article class="admin-card p-5 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="admin-kicker">Cockpit ERP / CRM</p>
                    <h2 class="mt-2 admin-heading">Espaces de travail</h2>
                </div>
                <span class="admin-pill">Modules separes</span>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($workspaces as $workspace)
                    <a href="{{ $workspace['href'] }}" class="group rounded-2xl border border-leaf/10 bg-linen p-5 transition hover:border-leaf/25 hover:bg-mint dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
                        <p class="admin-kicker">{{ $workspace['label'] }}</p>
                        <div class="mt-3 flex items-start justify-between gap-4">
                            <h3 class="text-xl font-black text-ink dark:text-cream">{{ $workspace['title'] }}</h3>
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white text-leaf ring-1 ring-leaf/10 transition group-hover:bg-leaf group-hover:text-white dark:bg-white/10 dark:text-meadow dark:ring-white/10">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg>
                            </span>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($workspace['items'] as $item)
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-cocoa/60 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/60 dark:ring-white/10">{{ $item }}</span>
                            @endforeach
                        </div>
                    </a>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl border border-leaf/10 bg-forest p-5 text-white shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-meadow">Flux operationnel</p>
            <h2 class="mt-2 text-2xl font-black">De la commande au SAV</h2>
            <div class="mt-5 space-y-3">
                @foreach ($pipeline as $step)
                    <div class="rounded-xl bg-white/10 p-4">
                        <div class="flex gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white text-xs font-black text-forest">{{ $loop->iteration }}</span>
                            <div>
                                <p class="font-black">{{ $step['label'] }}</p>
                                <p class="mt-1 text-sm leading-6 text-white/65">{{ $step['hint'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
        <article class="admin-card p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="admin-kicker">Sante catalogue</p>
                    <h2 class="mt-2 admin-heading">Qualite des donnees</h2>
                </div>
                <form method="GET" class="flex items-center gap-2">
                    <label class="text-xs font-bold uppercase tracking-wide text-cocoa/55 dark:text-cream/55" for="threshold">Seuil</label>
                    <input id="threshold" name="threshold" type="number" min="0" max="100" value="{{ $threshold }}" class="admin-input w-24">
                    <button class="admin-btn">OK</button>
                </form>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ([
                    'Sans image' => $health['products_missing_images'] ?? 0,
                    'Sans variante' => $health['products_missing_variants'] ?? 0,
                    'SEO incomplet' => $health['products_missing_seo'] ?? 0,
                    'Categories inactives avec produits' => $health['inactive_categories_with_active_products'] ?? 0,
                ] as $label => $value)
                    <div class="admin-panel p-4">
                        <p class="text-sm font-bold text-cocoa/60 dark:text-cream/60">{{ $label }}</p>
                        <p class="mt-2 text-3xl font-black {{ $value > 0 ? 'text-red-600' : 'text-leaf dark:text-meadow' }}">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl border border-leaf/10 bg-forest p-5 text-white shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-meadow">Priorite stock</p>
            <h2 class="mt-2 text-2xl font-black">Alertes a traiter</h2>
            <div class="mt-5 space-y-3">
                @forelse ($stockAlerts as $item)
                    <div class="rounded-xl bg-white/10 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-black">{{ $item['name'] ?? '-' }}</p>
                                <p class="mt-1 text-xs text-white/60">{{ $item['sku'] ?? '-' }} - {{ data_get($item, 'category.name', 'Sans categorie') }}</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-forest">{{ $item['stock_quantity'] ?? 0 }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl bg-white/10 p-4 text-sm text-white/70">Aucune alerte stock critique.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 admin-card p-5 sm:p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="admin-kicker">Audit recent</p>
                <h2 class="mt-2 admin-heading">Dernieres actions sensibles</h2>
            </div>
            <a href="{{ route('admin.audit', ['locale' => $locale]) }}" class="admin-btn-secondary">Voir tout</a>
        </div>
        <div class="mt-5 overflow-hidden rounded-xl border border-leaf/10 dark:border-white/10">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">Acteur</th>
                            <th class="px-4 py-3">Cible</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentActivity as $log)
                            <tr class="transition hover:bg-linen dark:hover:bg-white/5">
                                <td class="px-4 py-3 font-bold text-ink dark:text-cream">{{ $log['action'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-cocoa/65 dark:text-cream/65">{{ data_get($log, 'actor.name', 'Systeme') }}</td>
                                <td class="px-4 py-3 text-cocoa/65 dark:text-cream/65">{{ class_basename($log['auditable_type'] ?? '-') }} #{{ $log['auditable_id'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-cocoa/55 dark:text-cream/55">{{ $log['created_at'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-cocoa/55 dark:text-cream/55">Aucune activite recente.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
