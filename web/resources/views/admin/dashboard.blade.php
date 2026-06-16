@extends('layouts.admin')

@section('title', 'Pilotage')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Vue claire des priorites commerce, catalogue, stock et activite equipe.')

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

    $int = fn ($value) => (int) ($value ?? 0);
    $formatNumber = fn ($value) => number_format((int) $value, 0, ',', ' ');
    $percent = fn ($value, $total) => $total > 0 ? max(0, min(100, (int) round(($value / $total) * 100))) : 0;

    $productsTotal = $int($catalog['products_total'] ?? 0);
    $productsActive = $int($catalog['products_active'] ?? 0);
    $productsInactive = $int($catalog['products_inactive'] ?? 0);
    $categoriesTotal = $int($catalog['categories_total'] ?? 0);
    $categoriesActive = $int($catalog['categories_active'] ?? 0);

    $totalUnits = $int($inventory['total_units_available'] ?? 0);
    $lowStock = $int($inventory['low_stock_products'] ?? 0);
    $outOfStock = $int($inventory['out_of_stock_products'] ?? 0);

    $activeCarts = $int($carts['active_count'] ?? 0);
    $cartsToday = $int($carts['created_today'] ?? 0);
    $activeCartValue = $int($carts['active_value_cents'] ?? 0);

    $usersTotal = $int($identity['users_total'] ?? 0);
    $customersTotal = $int($identity['customers_total'] ?? 0);
    $staffTotal = $int($identity['staff_total'] ?? 0);
    $suspendedUsers = $int($identity['suspended_users'] ?? 0);

    $missingImages = $int($health['products_missing_images'] ?? 0);
    $missingVariants = $int($health['products_missing_variants'] ?? 0);
    $missingSeo = $int($health['products_missing_seo'] ?? 0);
    $inactiveCategoriesWithProducts = $int($health['inactive_categories_with_active_products'] ?? 0);
    $qualityIssues = $missingImages + $missingVariants + $missingSeo + $inactiveCategoriesWithProducts;

    $activeRate = $percent($productsActive, max($productsTotal, 1));
    $categoryRate = $percent($categoriesActive, max($categoriesTotal, 1));
    $stockRiskRate = $percent($lowStock + $outOfStock, max($productsTotal, 1));
    $stockHealthRate = max(0, 100 - $stockRiskRate);
    $qualityScore = max(0, 100 - $percent($qualityIssues, max(($productsTotal * 3) + max($categoriesTotal, 1), 1)));
    $cartMomentum = $activeCarts > 0 ? $percent($cartsToday, $activeCarts) : ($cartsToday > 0 ? 100 : 0);
    $customerRate = $percent($customersTotal, max($usersTotal, 1));
    $teamCoverage = min(100, $staffTotal * 20);
    $globalScore = (int) round(($activeRate + $stockHealthRate + $qualityScore + max(10, $teamCoverage)) / 4);

    $metricCards = [
        [
            'label' => 'Catalogue publie',
            'value' => $formatNumber($productsActive),
            'unit' => 'produits actifs',
            'hint' => $formatNumber($productsTotal).' produits total, '.$formatNumber($productsInactive).' brouillons',
            'progress' => $activeRate,
            'href' => route('admin.catalog.products', ['locale' => $locale]),
            'tone' => 'leaf',
            'icon' => 'M5 7h14l-1 13H6L5 7Zm3 0a4 4 0 0 1 8 0',
        ],
        [
            'label' => 'Stock disponible',
            'value' => $formatNumber($totalUnits),
            'unit' => 'unites',
            'hint' => $formatNumber($lowStock).' faibles, '.$formatNumber($outOfStock).' ruptures',
            'progress' => $stockHealthRate,
            'href' => route('admin.inventory', ['locale' => $locale]),
            'tone' => $stockRiskRate > 35 ? 'danger' : 'leaf',
            'icon' => 'M4 7 12 3l8 4-8 4-8-4Zm0 0v10l8 4 8-4V7M12 11v10',
        ],
        [
            'label' => 'Paniers actifs',
            'value' => $formatNumber($activeCarts),
            'unit' => 'en cours',
            'hint' => ($carts['formatted_active_value'] ?? '0,00 EUR').' de valeur active',
            'progress' => min(100, max(12, $cartMomentum)),
            'href' => route('admin.modules.show', ['locale' => $locale, 'module' => 'paniers']),
            'tone' => 'meadow',
            'icon' => 'M6 7h12l-1 12H7L6 7Zm3 0a3 3 0 0 1 6 0M9 12h6',
        ],
        [
            'label' => 'Comptes clients',
            'value' => $formatNumber($customersTotal),
            'unit' => 'clients',
            'hint' => $formatNumber($staffTotal).' staff, '.$formatNumber($suspendedUsers).' suspendus',
            'progress' => max(8, $customerRate),
            'href' => route('admin.users', ['locale' => $locale]),
            'tone' => 'olive',
            'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87',
        ],
    ];

    $scorePanels = [
        ['label' => 'Publication', 'score' => $activeRate, 'detail' => $formatNumber($productsActive).' / '.$formatNumber($productsTotal).' produits actifs'],
        ['label' => 'Qualite data', 'score' => $qualityScore, 'detail' => $formatNumber($qualityIssues).' points a corriger'],
        ['label' => 'Stock sain', 'score' => $stockHealthRate, 'detail' => $formatNumber($lowStock + $outOfStock).' references a surveiller'],
        ['label' => 'Equipe', 'score' => max(10, $teamCoverage), 'detail' => $formatNumber($staffTotal).' comptes staff'],
    ];

    $matrix = [
        ['axis' => 'Catalogue', 'now' => $activeRate, 'target' => 90, 'load' => $productsInactive, 'label' => 'Publie vs brouillon'],
        ['axis' => 'Categories', 'now' => $categoryRate, 'target' => 85, 'load' => max(0, $categoriesTotal - $categoriesActive), 'label' => 'Rayons actifs'],
        ['axis' => 'Stock', 'now' => $stockHealthRate, 'target' => 80, 'load' => $lowStock + $outOfStock, 'label' => 'Risque faible'],
        ['axis' => 'Qualite', 'now' => $qualityScore, 'target' => 88, 'load' => $qualityIssues, 'label' => 'Images, variantes, SEO'],
        ['axis' => 'Paniers', 'now' => min(100, max(0, $cartMomentum)), 'target' => 35, 'load' => $activeCarts, 'label' => 'Creation du jour'],
        ['axis' => 'Clients', 'now' => $customerRate, 'target' => 70, 'load' => $suspendedUsers, 'label' => 'Base client active'],
    ];

    $trend = function (int $score, int $offset = 0): array {
        $start = max(8, min(92, $score - 26 + $offset));

        return [
            $start,
            max(8, min(96, $start + 8)),
            max(8, min(98, $score - 10 + $offset)),
            max(8, min(99, $score - 4)),
            max(8, min(100, $score)),
        ];
    };

    $plot = function (array $values): string {
        $points = [];
        $count = max(count($values) - 1, 1);

        foreach ($values as $index => $value) {
            $x = 22 + (($index / $count) * 316);
            $y = 142 - (max(0, min(100, (int) $value)) * 1.12);
            $points[] = round($x, 1).','.round($y, 1);
        }

        return implode(' ', $points);
    };

    $chartLines = [
        ['label' => 'Catalogue', 'score' => $activeRate, 'points' => $plot($trend($activeRate, 0)), 'color' => '#2f7d1b'],
        ['label' => 'Stock', 'score' => $stockHealthRate, 'points' => $plot($trend($stockHealthRate, -4)), 'color' => '#4fb000'],
        ['label' => 'Qualite', 'score' => $qualityScore, 'points' => $plot($trend($qualityScore, -8)), 'color' => '#8ed957'],
        ['label' => 'Equipe', 'score' => max(10, $teamCoverage), 'points' => $plot($trend(max(10, $teamCoverage), 6)), 'color' => '#6f8f2a'],
    ];

    $pipeline = [
        ['label' => 'Catalogue pret', 'score' => $activeRate, 'hint' => 'Produits publies et categories actives'],
        ['label' => 'Stock controle', 'score' => $stockHealthRate, 'hint' => 'Ruptures et faibles stocks sous surveillance'],
        ['label' => 'Panier capte', 'score' => min(100, max(8, $cartMomentum)), 'hint' => 'Volume cree aujourd hui sur paniers actifs'],
        ['label' => 'Equipe outillee', 'score' => max(10, $teamCoverage), 'hint' => 'Comptes staff et gouvernance admin'],
    ];
@endphp

@section('content')
    <style>
        .dashboard-line {
            stroke-dasharray: 520;
            stroke-dashoffset: 520;
            animation: dashboard-draw 1.4s ease-out forwards;
        }

        .dashboard-progress {
            transform-origin: left;
            animation: dashboard-fill .9s ease-out both;
        }

        .dashboard-float {
            animation: dashboard-float 5.5s ease-in-out infinite;
        }

        @keyframes dashboard-draw {
            to { stroke-dashoffset: 0; }
        }

        @keyframes dashboard-fill {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        @keyframes dashboard-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
    </style>

    @if (! ($dashboard['ok'] ?? false))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
            {{ $dashboard['message'] ?? 'Impossible de charger le dashboard admin.' }}
        </div>
    @endif

    <section class="animate-rise overflow-hidden rounded-2xl border border-leaf/10 bg-forest text-white shadow-sm dark:border-white/10">
        <div class="relative grid gap-6 p-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="absolute inset-0 opacity-80" style="background: radial-gradient(circle at 16% 18%, rgba(142, 217, 87, .22), transparent 28%), radial-gradient(circle at 92% 8%, rgba(255,255,255,.16), transparent 26%);"></div>
            <div class="relative min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-white/12 px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-meadow">Live API</span>
                    <span class="rounded-full bg-white/12 px-3 py-1 text-xs font-bold text-white/70">{{ $data['timezone'] ?? 'Europe/Paris' }}</span>
                    <span class="rounded-full bg-white/12 px-3 py-1 text-xs font-bold text-white/70">Seuil stock {{ $data['low_stock_threshold'] ?? $threshold }}</span>
                </div>
                <h2 class="mt-6 max-w-3xl text-3xl font-black leading-tight sm:text-4xl xl:text-5xl">Centre de pilotage commerce, stock et operations.</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/70 sm:text-base">Les blocs ci-dessous transforment les donnees API existantes en priorites visuelles: ce qui vend, ce qui bloque, ce qui manque et ce qui doit etre traite aujourd hui.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('admin.catalog.products', ['locale' => $locale]) }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-black text-forest transition hover:bg-mint">Gerer le catalogue</a>
                    <a href="{{ route('admin.inventory', ['locale' => $locale]) }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-white/18 px-4 py-2.5 text-sm font-black text-white transition hover:bg-white/10">Voir les alertes stock</a>
                </div>
            </div>

            <div class="relative dashboard-float rounded-2xl border border-white/12 bg-white/10 p-5 backdrop-blur">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-meadow">Score operationnel</p>
                <div class="mt-5 flex items-center gap-5">
                    <div class="grid h-32 w-32 shrink-0 place-items-center rounded-full p-2" style="background: conic-gradient(#8ed957 {{ $globalScore }}%, rgba(255,255,255,.18) 0);">
                        <div class="grid h-full w-full place-items-center rounded-full bg-forest text-center shadow-inner">
                            <div>
                                <p class="text-4xl font-black">{{ $globalScore }}%</p>
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-white/50">global</p>
                            </div>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 space-y-3">
                        @foreach ($scorePanels as $panel)
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-2 text-xs font-bold text-white/70">
                                    <span>{{ $panel['label'] }}</span>
                                    <span>{{ $panel['score'] }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-white/12">
                                    <div class="dashboard-progress h-full rounded-full bg-meadow" style="width: {{ $panel['score'] }}%; animation-delay: {{ $loop->index * 90 }}ms"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <p class="mt-5 text-xs leading-5 text-white/58">Derniere generation API: {{ $data['generated_at'] ?? 'non disponible' }}</p>
            </div>
        </div>
    </section>

    <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($metricCards as $card)
            <a href="{{ $card['href'] }}" class="animate-rise admin-card group overflow-hidden p-5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-leaf/10" style="animation-delay: {{ $loop->index * 70 }}ms">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="admin-kicker">{{ $card['label'] }}</p>
                        <div class="mt-4 flex items-end gap-2">
                            <strong class="text-4xl font-black text-ink dark:text-cream">{{ $card['value'] }}</strong>
                        </div>
                        <p class="mt-1 text-xs font-black uppercase tracking-[0.14em] text-cocoa/42 dark:text-cream/42">{{ $card['unit'] }}</p>
                    </div>
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-mint text-leaf transition group-hover:bg-leaf group-hover:text-white dark:bg-white/10 dark:text-meadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="{{ $card['icon'] }}" /></svg>
                    </span>
                </div>
                <p class="mt-5 min-h-[40px] text-sm font-semibold leading-5 text-cocoa/58 dark:text-cream/58">{{ $card['hint'] }}</p>
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-linen dark:bg-white/10">
                    <div class="dashboard-progress h-full rounded-full {{ $card['tone'] === 'danger' ? 'bg-red-500' : 'bg-leaf dark:bg-meadow' }}" style="width: {{ $card['progress'] }}%; animation-delay: {{ 180 + ($loop->index * 80) }}ms"></div>
                </div>
            </a>
        @endforeach
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1.2fr)_420px]">
        <article class="admin-card p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="admin-kicker">Graphique multi-lignes</p>
                    <h2 class="mt-2 admin-heading">Comparaison des poles</h2>
                    <p class="mt-2 admin-muted">Courbes construites depuis les scores actuels de l API dashboard: catalogue, stock, qualite et equipe.</p>
                </div>
                <span class="admin-pill">Objectif: lisibilite rapide</span>
            </div>

            <div class="mt-5 rounded-2xl border border-leaf/10 bg-linen p-4 dark:border-white/10 dark:bg-white/5">
                <svg viewBox="0 0 360 170" class="h-72 w-full" role="img" aria-label="Comparaison multi-lignes du dashboard">
                    <defs>
                        <linearGradient id="dashboardChartFill" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#8ed957" stop-opacity="0.20" />
                            <stop offset="100%" stop-color="#8ed957" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    @for ($i = 0; $i < 5; $i++)
                        <line x1="22" x2="338" y1="{{ 30 + ($i * 28) }}" y2="{{ 30 + ($i * 28) }}" stroke="currentColor" class="text-leaf/10 dark:text-white/10" stroke-width="1" />
                    @endfor
                    @foreach ($chartLines as $line)
                        <polyline points="{{ $line['points'] }}" fill="none" stroke="{{ $line['color'] }}" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="dashboard-line" style="animation-delay: {{ $loop->index * 120 }}ms" />
                        @php [$lastX, $lastY] = explode(',', collect(explode(' ', $line['points']))->last()); @endphp
                        <circle cx="{{ $lastX }}" cy="{{ $lastY }}" r="5" fill="{{ $line['color'] }}" />
                    @endforeach
                </svg>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($chartLines as $line)
                        <div class="rounded-xl bg-white p-3 ring-1 ring-leaf/10 dark:bg-white/10 dark:ring-white/10">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="text-sm font-black text-ink dark:text-cream">{{ $line['label'] }}</span>
                                <span class="text-xs font-black" style="color: {{ $line['color'] }}">{{ $line['score'] }}%</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-linen dark:bg-white/10">
                                <div class="dashboard-progress h-full rounded-full" style="width: {{ $line['score'] }}%; background: {{ $line['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>

        <article class="admin-card p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="admin-kicker">Matrice</p>
                    <h2 class="mt-2 admin-heading">Lecture prioritaire</h2>
                </div>
                <form method="GET" class="flex items-center gap-2">
                    <input id="threshold" name="threshold" type="number" min="0" max="100" value="{{ $threshold }}" class="admin-input w-20" aria-label="Seuil stock">
                    <button class="admin-btn">OK</button>
                </form>
            </div>

            <div class="mt-5 space-y-3">
                @foreach ($matrix as $row)
                    @php
                        $delta = $row['now'] - $row['target'];
                        $stateClass = $row['now'] >= $row['target'] ? 'bg-mint text-leaf dark:bg-meadow/15 dark:text-meadow' : ($row['now'] >= ($row['target'] - 20) ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-200' : 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-200');
                    @endphp
                    <div class="rounded-xl border border-leaf/10 bg-linen p-3 dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black text-ink dark:text-cream">{{ $row['axis'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-cocoa/50 dark:text-cream/50">{{ $row['label'] }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-black {{ $stateClass }}">{{ $delta >= 0 ? '+' : '' }}{{ $delta }} pts</span>
                        </div>
                        <div class="mt-3 grid grid-cols-[1fr_auto] items-center gap-3">
                            <div class="h-2 overflow-hidden rounded-full bg-white dark:bg-white/10">
                                <div class="dashboard-progress h-full rounded-full bg-leaf dark:bg-meadow" style="width: {{ $row['now'] }}%"></div>
                            </div>
                            <span class="text-sm font-black text-ink dark:text-cream">{{ $row['now'] }}%</span>
                        </div>
                        <p class="mt-2 text-xs font-semibold text-cocoa/45 dark:text-cream/45">Charge: {{ $formatNumber($row['load']) }}</p>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[420px_minmax(0,1fr)]">
        <article class="rounded-2xl border border-leaf/10 bg-forest p-5 text-white shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-meadow">Progression</p>
            <h2 class="mt-2 text-2xl font-black">Pipeline operationnel</h2>
            <div class="mt-5 space-y-4">
                @foreach ($pipeline as $step)
                    <div class="rounded-xl bg-white/10 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black">{{ $loop->iteration }}. {{ $step['label'] }}</p>
                                <p class="mt-1 text-sm leading-6 text-white/62">{{ $step['hint'] }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-black text-forest">{{ $step['score'] }}%</span>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/12">
                            <div class="dashboard-progress h-full rounded-full bg-meadow" style="width: {{ $step['score'] }}%; animation-delay: {{ $loop->index * 110 }}ms"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="admin-card p-5 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="admin-kicker">Comparaison</p>
                    <h2 class="mt-2 admin-heading">Qualite catalogue</h2>
                    <p class="mt-2 admin-muted">Les points ci-dessous viennent de `catalog_health` et donnent les corrections les plus rentables.</p>
                </div>
                <span class="admin-pill">{{ $qualityScore }}% score data</span>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['label' => 'Produits sans image', 'value' => $missingImages, 'impact' => 'Visuel produit'],
                    ['label' => 'Produits sans variante', 'value' => $missingVariants, 'impact' => 'Choix client'],
                    ['label' => 'SEO incomplet', 'value' => $missingSeo, 'impact' => 'Acquisition'],
                    ['label' => 'Categories inactives', 'value' => $inactiveCategoriesWithProducts, 'impact' => 'Navigation'],
                ] as $item)
                    <div class="rounded-2xl border border-leaf/10 bg-linen p-4 transition hover:-translate-y-0.5 hover:bg-mint dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black text-ink dark:text-cream">{{ $item['label'] }}</p>
                                <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-cocoa/42 dark:text-cream/42">{{ $item['impact'] }}</p>
                            </div>
                            <span class="text-3xl font-black {{ $item['value'] > 0 ? 'text-red-600 dark:text-red-300' : 'text-leaf dark:text-meadow' }}">{{ $item['value'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <article class="admin-card p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="admin-kicker">Priorite stock</p>
                    <h2 class="mt-2 admin-heading">Alertes a traiter</h2>
                </div>
                <a href="{{ route('admin.inventory', ['locale' => $locale]) }}" class="admin-btn-secondary">Stock</a>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($stockAlerts as $item)
                    <div class="rounded-xl border border-leaf/10 bg-linen p-4 transition hover:bg-mint dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-black text-ink dark:text-cream">{{ $item['name'] ?? '-' }}</p>
                                <p class="mt-1 text-xs font-semibold text-cocoa/55 dark:text-cream/55">{{ $item['sku'] ?? '-' }} - {{ data_get($item, 'category.name', 'Sans categorie') }}</p>
                            </div>
                            <span class="rounded-full {{ ($item['stock_quantity'] ?? 0) <= 0 ? 'bg-red-600 text-white' : 'bg-mint text-leaf dark:bg-meadow/15 dark:text-meadow' }} px-3 py-1 text-xs font-black">{{ $item['stock_quantity'] ?? 0 }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-leaf/10 bg-linen p-4 text-sm font-semibold text-cocoa/58 dark:border-white/10 dark:bg-white/5 dark:text-cream/58">Aucune alerte stock critique.</div>
                @endforelse
            </div>
        </article>

        <article class="admin-card p-5 sm:p-6">
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
                                    <td class="px-4 py-3 text-cocoa/65 dark:text-cream/65">{{ data_get($log, 'actor.name') ?: data_get($log, 'actor.email', 'Systeme') }}</td>
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
