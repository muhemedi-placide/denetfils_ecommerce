@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Overview dashboard')
@section('page_subtitle', 'Vue analytique rapide du catalogue, du stock, des paniers et des clients.')

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

    $formatNumber = fn ($value) => number_format((float) $value, 0, ',', ' ');
    $formatPercent = fn ($value) => number_format((float) $value, 1, ',', ' ') . '%';
    $safePercent = fn ($part, $total) => $total > 0 ? min(100, round(($part / max($total, 1)) * 100, 1)) : 0;

    $productsTotal = (int) ($catalog['products_total'] ?? 0);
    $productsActive = (int) ($catalog['products_active'] ?? 0);
    $productsDraft = max(0, $productsTotal - $productsActive);
    $stockUnits = (int) ($inventory['total_units_available'] ?? 0);
    $lowStock = (int) ($inventory['low_stock_products'] ?? 0);
    $cartsActive = (int) ($carts['active_count'] ?? 0);
    $cartValue = $carts['formatted_active_value'] ?? '0,00 EUR';
    $usersTotal = (int) ($identity['users_total'] ?? 0);
    $customersTotal = (int) ($identity['customers_total'] ?? 0);

    $missingImages = (int) ($health['products_missing_images'] ?? 0);
    $missingVariants = (int) ($health['products_missing_variants'] ?? 0);
    $missingSeo = (int) ($health['products_missing_seo'] ?? 0);
    $inactiveCategories = (int) ($health['inactive_categories_with_active_products'] ?? 0);

    $qualityScore = max(0, 100 - ($productsTotal > 0 ? round((($missingImages + $missingVariants + $missingSeo) / max($productsTotal * 3, 1)) * 100) : 0));
    $publicationRate = $safePercent($productsActive, $productsTotal);
    $customerShare = $safePercent($customersTotal, max($usersTotal, 1));
    $stockHealth = max(0, 100 - min(100, $lowStock * 12));

    $metricCards = [
        [
            'label' => 'Total sales',
            'value' => $cartValue,
            'change' => '+2.6%',
            'trend' => 'up',
            'hint' => 'Valeur paniers actifs',
            'points' => [18, 22, 21, 31, 28, 36, 34, 42, 39, 48, 45, 58],
        ],
        [
            'label' => 'Produits actifs',
            'value' => $formatNumber($productsActive),
            'change' => $formatPercent($publicationRate),
            'trend' => 'up',
            'hint' => $formatNumber($productsTotal) . ' produits au total',
            'points' => [12, 18, 16, 24, 23, 30, 32, 38, 36, 44, 47, 52],
        ],
        [
            'label' => 'Stock disponible',
            'value' => $formatNumber($stockUnits),
            'change' => $lowStock > 0 ? '-' . $lowStock . ' alertes' : '+ stable',
            'trend' => $lowStock > 0 ? 'down' : 'up',
            'hint' => 'Unites disponibles',
            'points' => [58, 56, 53, 55, 49, 48, 43, 45, 42, 38, 41, 36],
        ],
        [
            'label' => 'Paniers actifs',
            'value' => $formatNumber($cartsActive),
            'change' => '+ live',
            'trend' => 'up',
            'hint' => 'Sessions avec panier',
            'points' => [8, 10, 9, 14, 12, 18, 17, 21, 19, 26, 24, 30],
        ],
        [
            'label' => 'Clients',
            'value' => $formatNumber($customersTotal),
            'change' => $formatPercent($customerShare),
            'trend' => 'up',
            'hint' => $formatNumber($usersTotal) . ' utilisateurs',
            'points' => [14, 15, 17, 16, 21, 24, 22, 29, 31, 34, 36, 41],
        ],
        [
            'label' => 'Qualite catalogue',
            'value' => $qualityScore . '%',
            'change' => $missingSeo > 0 ? '-' . $missingSeo . ' SEO' : '+ OK',
            'trend' => $qualityScore >= 80 ? 'up' : 'down',
            'hint' => 'Images, variantes, SEO',
            'points' => [42, 46, 44, 51, 55, 53, 59, 62, 66, 70, 73, $qualityScore],
        ],
        [
            'label' => 'Brouillons',
            'value' => $formatNumber($productsDraft),
            'change' => $productsDraft > 0 ? 'a publier' : '+ clean',
            'trend' => $productsDraft > 0 ? 'down' : 'up',
            'hint' => 'Produits non publies',
            'points' => [30, 28, 25, 26, 22, 20, 18, 16, 14, 12, 10, max(4, $productsDraft)],
        ],
        [
            'label' => 'Categories a verifier',
            'value' => $formatNumber($inactiveCategories),
            'change' => $inactiveCategories > 0 ? 'attention' : '+ OK',
            'trend' => $inactiveCategories > 0 ? 'down' : 'up',
            'hint' => 'Categories inactives avec produits',
            'points' => [18, 16, 14, 12, 15, 11, 9, 8, 7, 6, 4, max(2, $inactiveCategories)],
        ],
    ];

    $lineSeries = [
        ['label' => 'Catalogue', 'score' => $publicationRate, 'path' => 'M8 116 C70 70, 126 88, 184 55 S292 32, 352 48 S468 26, 548 34', 'stroke' => '#1f8a5b'],
        ['label' => 'Stock', 'score' => $stockHealth, 'path' => 'M8 80 C70 74, 126 48, 184 68 S294 94, 352 74 S456 86, 548 52', 'stroke' => '#c46a2a'],
        ['label' => 'Qualite', 'score' => $qualityScore, 'path' => 'M8 130 C72 122, 128 112, 184 96 S292 78, 352 70 S454 54, 548 40', 'stroke' => '#6554c0'],
    ];

    $progressRows = [
        ['label' => 'Publication produits', 'value' => $publicationRate, 'meta' => $productsActive . '/' . max($productsTotal, 1)],
        ['label' => 'Sante stock', 'value' => $stockHealth, 'meta' => $lowStock . ' alertes'],
        ['label' => 'Qualite catalogue', 'value' => $qualityScore, 'meta' => $missingImages + $missingVariants + $missingSeo . ' corrections'],
        ['label' => 'Base clients', 'value' => $customerShare, 'meta' => $customersTotal . ' clients'],
    ];
@endphp

@section('content')
    @if (! ($dashboard['ok'] ?? false))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
            {{ $dashboard['message'] ?? 'Impossible de charger le dashboard admin.' }}
        </div>
    @endif

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($metricCards as $index => $card)
            @php
                $points = $card['points'];
                $max = max($points) ?: 1;
                $min = min($points);
                $range = max($max - $min, 1);
                $svgPoints = collect($points)->map(function ($point, $key) use ($points, $min, $range) {
                    $x = 8 + ($key * (176 / max(count($points) - 1, 1)));
                    $y = 62 - ((($point - $min) / $range) * 42);
                    return round($x, 1) . ',' . round($y, 1);
                })->implode(' ');
                $lastX = 8 + ((count($points) - 1) * (176 / max(count($points) - 1, 1)));
                $lastY = 62 - (((end($points) - $min) / $range) * 42);
                $isUp = $card['trend'] === 'up';
            @endphp

            <article class="group relative overflow-hidden rounded-2xl border border-leaf/10 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-leaf/30 hover:shadow-xl dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-meadow/40">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-cocoa/50 dark:text-cream/50">{{ $card['label'] }}</p>
                        <div class="mt-2 flex items-end gap-2">
                            <strong class="text-2xl font-black text-ink dark:text-cream sm:text-3xl">{{ $card['value'] }}</strong>
                            <span class="mb-1 rounded-full px-2 py-0.5 text-[11px] font-black {{ $isUp ? 'bg-green-50 text-green-700 dark:bg-green-400/10 dark:text-green-300' : 'bg-orange-50 text-orange-700 dark:bg-orange-400/10 dark:text-orange-300' }}">
                                {{ $card['change'] }}
                            </span>
                        </div>
                    </div>
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-linen text-leaf ring-1 ring-leaf/10 transition group-hover:scale-110 group-hover:bg-leaf group-hover:text-white dark:bg-white/10 dark:text-meadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17 9 11l4 4 8-8"/><path d="M14 7h7v7"/></svg>
                    </span>
                </div>

                <div class="relative mt-5 h-20">
                    <svg viewBox="0 0 192 74" class="h-full w-full overflow-visible">
                        <defs>
                            <linearGradient id="fill-{{ $index }}" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="{{ $isUp ? '#1f8a5b' : '#c46a2a' }}" stop-opacity="0.22"/>
                                <stop offset="100%" stop-color="{{ $isUp ? '#1f8a5b' : '#c46a2a' }}" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <polyline points="{{ $svgPoints }} 184,70 8,70" fill="url(#fill-{{ $index }})" stroke="none"/>
                        <polyline points="{{ $svgPoints }}" fill="none" stroke="{{ $isUp ? '#1f8a5b' : '#c46a2a' }}" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="transition duration-300 group-hover:stroke-[3.5]"/>
                        @foreach ($points as $key => $point)
                            @php
                                $cx = 8 + ($key * (176 / max(count($points) - 1, 1)));
                                $cy = 62 - ((($point - $min) / $range) * 42);
                            @endphp
                            <circle cx="{{ round($cx, 1) }}" cy="{{ round($cy, 1) }}" r="2.1" fill="white" stroke="{{ $isUp ? '#1f8a5b' : '#c46a2a' }}" stroke-width="1.8" class="opacity-0 transition group-hover:opacity-100">
                                <title>{{ $card['label'] }} : {{ $point }}</title>
                            </circle>
                        @endforeach
                        <circle cx="{{ round($lastX, 1) }}" cy="{{ round($lastY, 1) }}" r="4" fill="{{ $isUp ? '#1f8a5b' : '#c46a2a' }}" class="drop-shadow-sm">
                            <title>{{ $card['label'] }} : {{ end($points) }}</title>
                        </circle>
                    </svg>
                </div>
                <p class="mt-2 text-xs font-semibold text-cocoa/55 dark:text-cream/55">{{ $card['hint'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[1.35fr_0.65fr]">
        <article class="rounded-2xl border border-leaf/10 bg-white p-5 shadow-sm transition hover:border-leaf/25 hover:shadow-xl dark:border-white/10 dark:bg-white/[0.04] sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-leaf dark:text-meadow">Performance overview</p>
                    <h2 class="mt-1 text-xl font-black text-ink dark:text-cream">Comparaison catalogue, stock et qualite</h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($lineSeries as $line)
                        <span class="rounded-full bg-linen px-3 py-1 text-xs font-black text-cocoa/65 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/65">{{ $line['label'] }} {{ round($line['score']) }}%</span>
                    @endforeach
                </div>
            </div>

            <div class="relative mt-6 rounded-2xl border border-leaf/10 bg-linen/40 p-4 dark:border-white/10 dark:bg-black/10">
                <svg viewBox="0 0 560 170" class="h-72 w-full overflow-visible">
                    @for ($i = 0; $i < 5; $i++)
                        <line x1="8" x2="548" y1="{{ 24 + ($i * 30) }}" y2="{{ 24 + ($i * 30) }}" stroke="currentColor" class="text-leaf/10 dark:text-white/10" />
                    @endfor
                    @foreach ($lineSeries as $line)
                        <path d="{{ $line['path'] }}" fill="none" stroke="{{ $line['stroke'] }}" stroke-width="3" stroke-linecap="round" class="transition duration-300 hover:stroke-[5]"/>
                        <circle cx="548" cy="{{ $loop->iteration === 1 ? 34 : ($loop->iteration === 2 ? 52 : 40) }}" r="5" fill="{{ $line['stroke'] }}">
                            <title>{{ $line['label'] }} : {{ round($line['score'], 1) }}%</title>
                        </circle>
                    @endforeach
                    <line x1="8" x2="548" y1="150" y2="150" stroke="currentColor" class="text-cocoa/15 dark:text-white/15" />
                </svg>
            </div>
        </article>

        <article class="rounded-2xl border border-leaf/10 bg-white p-5 shadow-sm transition hover:border-leaf/25 hover:shadow-xl dark:border-white/10 dark:bg-white/[0.04] sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-leaf dark:text-meadow">Progress</p>
                    <h2 class="mt-1 text-xl font-black text-ink dark:text-cream">Objectifs rapides</h2>
                </div>
                <form method="GET" class="flex items-center gap-2">
                    <input id="threshold" name="threshold" type="number" min="0" max="100" value="{{ $threshold }}" class="admin-input w-20">
                    <button class="admin-btn">OK</button>
                </form>
            </div>

            <div class="mt-6 space-y-5">
                @foreach ($progressRows as $row)
                    <div class="group">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="text-sm font-black text-ink dark:text-cream">{{ $row['label'] }}</span>
                            <span class="text-xs font-bold text-cocoa/55 dark:text-cream/55">{{ $row['meta'] }}</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-linen ring-1 ring-leaf/10 dark:bg-white/10 dark:ring-white/10">
                            <div class="h-full rounded-full bg-leaf transition-all duration-700 group-hover:bg-meadow" style="width: {{ min(100, max(0, $row['value'])) }}%"></div>
                        </div>
                        <p class="mt-1 text-right text-xs font-black text-leaf dark:text-meadow">{{ round($row['value'], 1) }}%</p>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
        <article class="rounded-2xl border border-leaf/10 bg-white p-5 shadow-sm transition hover:border-leaf/25 hover:shadow-xl dark:border-white/10 dark:bg-white/[0.04] sm:p-6">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-leaf dark:text-meadow">Stock alerts</p>
                    <h2 class="mt-1 text-xl font-black text-ink dark:text-cream">Produits a traiter</h2>
                </div>
                <a href="{{ route('admin.inventory', ['locale' => $locale]) }}" class="admin-btn-secondary">Stock</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($stockAlerts as $item)
                    <div class="group rounded-xl border border-leaf/10 bg-linen/60 p-4 transition hover:-translate-y-0.5 hover:border-orange-300 hover:bg-orange-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-orange-400/10">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-black text-ink dark:text-cream">{{ $item['name'] ?? '-' }}</p>
                                <p class="mt-1 text-xs font-bold text-cocoa/55 dark:text-cream/55">{{ $item['sku'] ?? '-' }} - {{ data_get($item, 'category.name', 'Sans categorie') }}</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-orange-700 ring-1 ring-orange-200 dark:bg-white/10 dark:text-orange-300 dark:ring-orange-300/20">{{ $item['stock_quantity'] ?? 0 }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-leaf/10 bg-linen/60 p-4 text-sm font-semibold text-cocoa/60 dark:border-white/10 dark:bg-white/5 dark:text-cream/60">Aucune alerte stock critique.</div>
                @endforelse
            </div>
        </article>

        <article class="rounded-2xl border border-leaf/10 bg-white p-5 shadow-sm transition hover:border-leaf/25 hover:shadow-xl dark:border-white/10 dark:bg-white/[0.04] sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-leaf dark:text-meadow">Recent activity</p>
                    <h2 class="mt-1 text-xl font-black text-ink dark:text-cream">Dernieres actions sensibles</h2>
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
        </article>
    </section>
@endsection
