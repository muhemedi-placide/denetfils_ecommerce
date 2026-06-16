@extends('layouts.admin')

@section('title', 'Pilotage')
@section('page_title', 'Pilotage general')

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
@endphp

@section('content')
    @if (! ($dashboard['ok'] ?? false))
        <div class="mb-5 rounded-3xl border border-red-200 bg-red-50 p-5 text-sm font-semibold text-red-700">
            {{ $dashboard['message'] ?? 'Impossible de charger le dashboard admin.' }}
        </div>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <article class="rounded-3xl border border-black/5 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#2f7d1b]">{{ $card['label'] }}</p>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <strong class="text-4xl font-black text-[#12210f]">{{ $card['value'] }}</strong>
                    <span class="rounded-full bg-[#e8f6dd] px-3 py-1 text-xs font-black text-[#2f7d1b]">Live API</span>
                </div>
                <p class="mt-3 text-sm font-semibold text-[#1f2a1c]/55">{{ $card['hint'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-3xl border border-black/5 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#2f7d1b]">Sante catalogue</p>
                    <h2 class="mt-2 text-2xl font-black text-[#12210f]">Qualite des donnees</h2>
                </div>
                <form method="GET" class="flex items-center gap-2">
                    <label class="text-xs font-bold uppercase tracking-wide text-[#1f2a1c]/55" for="threshold">Seuil</label>
                    <input id="threshold" name="threshold" type="number" min="0" max="100" value="{{ $threshold }}" class="h-11 w-24 rounded-2xl border border-black/10 bg-[#f7f5ef] px-3 text-sm font-bold outline-none focus:border-[#2f7d1b]">
                    <button class="h-11 rounded-2xl bg-[#12210f] px-4 text-xs font-black uppercase tracking-wide text-white">OK</button>
                </form>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ([
                    'Sans image' => $health['products_missing_images'] ?? 0,
                    'Sans variante' => $health['products_missing_variants'] ?? 0,
                    'SEO incomplet' => $health['products_missing_seo'] ?? 0,
                    'Categories inactives avec produits' => $health['inactive_categories_with_active_products'] ?? 0,
                ] as $label => $value)
                    <div class="rounded-2xl bg-[#f7f5ef] p-4">
                        <p class="text-sm font-bold text-[#1f2a1c]/60">{{ $label }}</p>
                        <p class="mt-2 text-3xl font-black {{ $value > 0 ? 'text-[#f15b2a]' : 'text-[#2f7d1b]' }}">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-3xl border border-black/5 bg-[#12210f] p-5 text-white shadow-sm sm:p-6">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-[#8ed957]">Priorite stock</p>
            <h2 class="mt-2 text-2xl font-black">Alertes a traiter</h2>
            <div class="mt-5 space-y-3">
                @forelse ($stockAlerts as $item)
                    <div class="rounded-2xl bg-white/10 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-black">{{ $item['name'] ?? '-' }}</p>
                                <p class="mt-1 text-xs text-white/55">{{ $item['sku'] ?? '-' }} - {{ data_get($item, 'category.name', 'Sans categorie') }}</p>
                            </div>
                            <span class="rounded-full bg-[#f15b2a] px-3 py-1 text-xs font-black">{{ $item['stock_quantity'] ?? 0 }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-white/10 p-4 text-sm text-white/65">Aucune alerte stock critique.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 rounded-3xl border border-black/5 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#2f7d1b]">Audit recent</p>
                <h2 class="mt-2 text-2xl font-black text-[#12210f]">Dernieres actions sensibles</h2>
            </div>
            <a href="{{ route('admin.audit', ['locale' => $locale]) }}" class="rounded-2xl border border-black/10 px-4 py-3 text-sm font-black text-[#12210f] hover:bg-[#f7f5ef]">Voir tout</a>
        </div>
        <div class="mt-5 overflow-hidden rounded-2xl border border-black/5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-black/5 text-left text-sm">
                    <thead class="bg-[#f7f5ef] text-xs font-black uppercase tracking-wide text-[#1f2a1c]/55">
                        <tr>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">Acteur</th>
                            <th class="px-4 py-3">Cible</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        @forelse ($recentActivity as $log)
                            <tr>
                                <td class="px-4 py-3 font-bold text-[#12210f]">{{ $log['action'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-[#1f2a1c]/65">{{ data_get($log, 'actor.name', 'Systeme') }}</td>
                                <td class="px-4 py-3 text-[#1f2a1c]/65">{{ class_basename($log['auditable_type'] ?? '-') }} #{{ $log['auditable_id'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-[#1f2a1c]/55">{{ $log['created_at'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-[#1f2a1c]/55">Aucune activite recente.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
