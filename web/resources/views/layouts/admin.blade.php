@php
    $currentLocale = $locale ?? app()->getLocale();
    $alternateLocale = $currentLocale === 'fr' ? 'en' : 'fr';
    $adminName = data_get($adminUser ?? [], 'name', 'Admin Denetfils');
    $adminEmail = data_get($adminUser ?? [], 'email', '');
    $navSections = [
        [
            'title' => 'PILOTER',
            'menus' => [
                [
                    'key' => 'dashboard',
                    'label' => 'Dashboard',
                    'icon' => 'dashboard',
                    'items' => [
                        ['key' => 'dashboard', 'label' => 'Vue generale', 'route' => 'admin.dashboard', 'hint' => 'KPIs'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'VENDRE',
            'menus' => [
                [
                    'key' => 'sales',
                    'label' => 'Commandes',
                    'icon' => 'orders',
                    'items' => [
                        ['key' => 'sales.orders', 'label' => 'Commandes', 'route' => 'admin.modules.show', 'params' => ['module' => 'commandes'], 'hint' => 'Ventes'],
                        ['key' => 'sales.invoices', 'label' => 'Factures', 'route' => 'admin.modules.show', 'params' => ['module' => 'factures'], 'hint' => 'Documents'],
                        ['key' => 'sales.credits', 'label' => 'Avoirs', 'route' => 'admin.modules.show', 'params' => ['module' => 'avoirs'], 'hint' => 'Retours'],
                        ['key' => 'sales.delivery-notes', 'label' => 'Bons de livraison', 'route' => 'admin.modules.show', 'params' => ['module' => 'bons-livraison'], 'hint' => 'Logistique'],
                        ['key' => 'sales.carts', 'label' => 'Paniers', 'route' => 'admin.modules.show', 'params' => ['module' => 'paniers'], 'hint' => 'Abandons'],
                    ],
                ],
                [
                    'key' => 'catalog',
                    'label' => 'Catalogue',
                    'icon' => 'catalog',
                    'items' => [
                        ['key' => 'catalog.products', 'label' => 'Produits', 'route' => 'admin.catalog.products', 'hint' => 'Articles'],
                        ['key' => 'catalog.categories', 'label' => 'Categories', 'route' => 'admin.catalog.categories', 'hint' => 'Rayons'],
                        ['key' => 'catalog.monitoring', 'label' => 'Suivi', 'route' => 'admin.modules.show', 'params' => ['module' => 'suivi-catalogue'], 'hint' => 'Qualite'],
                        ['key' => 'catalog.attributes', 'label' => 'Attributs & caracteristiques', 'route' => 'admin.modules.show', 'params' => ['module' => 'attributs-caracteristiques'], 'hint' => 'Variantes'],
                        ['key' => 'catalog.brands', 'label' => 'Marques et fournisseurs', 'route' => 'admin.modules.show', 'params' => ['module' => 'marques-fournisseurs'], 'hint' => 'Sourcing'],
                        ['key' => 'catalog.files', 'label' => 'Fichiers', 'route' => 'admin.modules.show', 'params' => ['module' => 'fichiers'], 'hint' => 'Medias'],
                        ['key' => 'catalog.discounts', 'label' => 'Reductions', 'route' => 'admin.modules.show', 'params' => ['module' => 'reductions'], 'hint' => 'Prix'],
                        ['key' => 'inventory', 'label' => 'Stock', 'route' => 'admin.inventory', 'hint' => 'Alertes'],
                        ['key' => 'catalog.collections', 'label' => 'Collections de produits', 'route' => 'admin.modules.show', 'params' => ['module' => 'collections-produits'], 'hint' => 'Merchandising'],
                    ],
                ],
                [
                    'key' => 'customers',
                    'label' => 'Clients',
                    'icon' => 'users',
                    'items' => [
                        ['key' => 'users', 'label' => 'Clients', 'route' => 'admin.users', 'hint' => 'Comptes'],
                        ['key' => 'customers.addresses', 'label' => 'Adresses', 'route' => 'admin.modules.show', 'params' => ['module' => 'adresses-clients'], 'hint' => 'Livraison'],
                    ],
                ],
                [
                    'key' => 'support',
                    'label' => 'SAV',
                    'icon' => 'support',
                    'items' => [
                        ['key' => 'support.tickets', 'label' => 'SAV', 'route' => 'admin.modules.show', 'params' => ['module' => 'sav'], 'hint' => 'Tickets'],
                        ['key' => 'support.templates', 'label' => 'Messages predefinis', 'route' => 'admin.modules.show', 'params' => ['module' => 'messages-predefinis'], 'hint' => 'Reponses'],
                        ['key' => 'support.returns', 'label' => 'Retours produits', 'route' => 'admin.modules.show', 'params' => ['module' => 'retours-produits'], 'hint' => 'RMA'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'PERSONNALISER',
            'menus' => [
                [
                    'key' => 'customize',
                    'label' => 'Modules',
                    'icon' => 'modules',
                    'items' => [
                        ['key' => 'customize.modules', 'label' => 'Modules', 'route' => 'admin.modules.show', 'params' => ['module' => 'modules'], 'hint' => 'Apps'],
                        ['key' => 'customize.appearance', 'label' => 'Apparence', 'route' => 'admin.modules.show', 'params' => ['module' => 'apparence'], 'hint' => 'Theme'],
                        ['key' => 'customize.delivery', 'label' => 'Livraison', 'route' => 'admin.modules.show', 'params' => ['module' => 'livraison'], 'hint' => 'Transport'],
                        ['key' => 'customize.payment', 'label' => 'Paiement', 'route' => 'admin.modules.show', 'params' => ['module' => 'paiement'], 'hint' => 'Checkout'],
                        ['key' => 'customize.international', 'label' => 'International', 'route' => 'admin.modules.show', 'params' => ['module' => 'international'], 'hint' => 'Langues'],
                        ['key' => 'customize.marketing', 'label' => 'Marketing', 'route' => 'admin.modules.show', 'params' => ['module' => 'marketing'], 'hint' => 'CRM'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'CONFIGURER',
            'menus' => [
                [
                    'key' => 'settings',
                    'label' => 'Parametres',
                    'icon' => 'settings',
                    'items' => [
                        ['key' => 'settings.shop', 'label' => 'Parametres de la boutique', 'route' => 'admin.modules.show', 'params' => ['module' => 'parametres-boutique'], 'hint' => 'General'],
                        ['key' => 'access', 'label' => 'Acces et roles', 'route' => 'admin.access', 'hint' => 'RBAC'],
                        ['key' => 'audit', 'label' => 'Audit', 'route' => 'admin.audit', 'hint' => 'Journal'],
                        ['key' => 'settings.advanced', 'label' => 'Parametres avances', 'route' => 'admin.modules.show', 'params' => ['module' => 'parametres-avances'], 'hint' => 'Systeme'],
                        ['key' => 'settings.assistance', 'label' => 'Assistance', 'route' => 'admin.modules.show', 'params' => ['module' => 'assistance'], 'hint' => 'Support'],
                        ['key' => 'settings.update', 'label' => 'Update assistant', 'route' => 'admin.modules.show', 'params' => ['module' => 'update-assistant'], 'hint' => 'Maintenance'],
                    ],
                ],
            ],
        ],
    ];
    $navIcons = [
        'dashboard' => '<path d="M4 4h7v7H4V4Zm9 0h7v4h-7V4ZM4 13h7v7H4v-7Zm9-3h7v10h-7V10Z" />',
        'catalog' => '<path d="M5 7h14l-1 13H6L5 7Z" /><path d="M8 7a4 4 0 0 1 8 0" />',
        'orders' => '<path d="M6 7h12l-1 12H7L6 7Z" /><path d="M9 7a3 3 0 0 1 6 0" /><path d="M9 12h6" />',
        'inventory' => '<path d="M4 7 12 3l8 4-8 4-8-4Z" /><path d="M4 7v10l8 4 8-4V7" /><path d="M12 11v10" />',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
        'support' => '<path d="M4 5h16v12H7l-3 3V5Z" /><path d="M8 9h8" /><path d="M8 13h5" />',
        'modules' => '<path d="M9 3h6v6H9V3Z" /><path d="M3 15h6v6H3v-6Z" /><path d="M15 15h6v6h-6v-6Z" /><path d="M12 9v3" /><path d="M6 15v-3h12v3" />',
        'settings' => '<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.36.17.7.38 1 .6.33.25.7.4 1.1.4H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.51 1Z" />',
        'access' => '<path d="M12 3 4 7v6c0 5 3.4 7.6 8 8 4.6-.4 8-3 8-8V7l-8-4Z" /><path d="m9 12 2 2 4-5" />',
        'audit' => '<path d="M8 3h8l4 4v14H4V3h4Z" /><path d="M14 3v5h5" /><path d="M8 13h8" /><path d="M8 17h5" />',
    ];
    $allNavItems = collect($navSections)
        ->flatMap(fn (array $section) => $section['menus'])
        ->flatMap(fn (array $menu) => $menu['items'])
        ->values();
    $activeAdminKey = $activeAdmin ?? 'dashboard';
    $openMenus = collect($navSections)
        ->flatMap(fn (array $section) => $section['menus'])
        ->filter(fn (array $menu) => collect($menu['items'])->contains(fn (array $item) => $item['key'] === $activeAdminKey || Str::startsWith($activeAdminKey, $item['key'].'.')))
        ->pluck('key')
        ->values();
    $commandItems = $allNavItems->map(fn (array $item) => [
        'label' => $item['label'],
        'hint' => $item['hint'],
        'href' => route($item['route'], ['locale' => $currentLocale, ...($item['params'] ?? [])]),
    ])->values();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Back-office') | Denetfils</title>
        <meta name="robots" content="noindex,nofollow">
        <script>
            let storedTheme = null;

            try {
                storedTheme = localStorage.getItem('theme');
            } catch (error) {
                storedTheme = null;
            }

            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if ((storedTheme !== 'light' && storedTheme !== 'dark') || storedTheme === null) {
                storedTheme = systemPrefersDark ? 'dark' : 'light';
            }

            if (storedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="theme-page surface-transition min-h-screen bg-cream text-cocoa antialiased dark:bg-ink dark:text-cream">
        <div
            id="admin-app"
            x-data="adminShell({ commandItems: @js($commandItems), openMenus: @js($openMenus) })"
            x-init="init()"
            x-on:keydown.escape.window="closeOverlays()"
            class="admin-shell-backdrop min-h-screen"
        >
            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-leaf/10 bg-white/95 p-4 shadow-2xl shadow-black/10 backdrop-blur-xl transition-all duration-300 dark:border-white/10 dark:bg-ink/95 lg:translate-x-0"
                :class="[
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                    sidebarExpanded() ? 'lg:w-72' : 'lg:w-[5.75rem]'
                ]"
                x-on:mouseenter="if (sidebarCollapsed) sidebarHover = true"
                x-on:mouseleave="sidebarHover = false"
            >
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('admin.dashboard', ['locale' => $currentLocale]) }}" class="flex min-w-0 items-center gap-3">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-forest text-sm font-black text-white dark:bg-meadow dark:text-ink">DF</span>
                        <span x-show="sidebarExpanded()" x-cloak x-transition.opacity class="min-w-0">
                            <span class="block truncate text-sm font-black uppercase tracking-[0.18em] text-cocoa dark:text-cream">Denetfils</span>
                            <span class="block truncate text-xs font-semibold text-cocoa/55 dark:text-cream/55">Back-office commerce</span>
                        </span>
                    </a>

                    <div class="flex items-center gap-1">
                        <button type="button" x-on:click="toggleSidebarSize()" class="admin-icon-btn hidden lg:inline-grid" :title="sidebarCollapsed ? 'Ouvrir le menu' : 'Reduire le menu'">
                            <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6" /><path d="M20 4v16" /></svg>
                            <svg x-show="sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6" /><path d="M4 4v16" /></svg>
                        </button>
                        <button type="button" x-on:click="sidebarOpen = false" class="admin-icon-btn lg:hidden" aria-label="Fermer le menu">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                        </button>
                    </div>
                </div>

                <nav class="mobile-scrollbarless mt-7 flex-1 space-y-6 overflow-y-auto pr-1" aria-label="Administration">
                    @foreach ($navSections as $section)
                        <section>
                            <p x-show="sidebarExpanded()" x-cloak x-transition.opacity class="mb-2 border-b border-leaf/10 px-2 pb-2 text-[11px] font-black uppercase tracking-[0.18em] text-cocoa/38 dark:border-white/10 dark:text-cream/38">
                                {{ $section['title'] }}
                            </p>

                            <div class="space-y-1.5">
                                @foreach ($section['menus'] as $menu)
                                    @php
                                        $menuActive = collect($menu['items'])->contains(fn (array $item) => $item['key'] === $activeAdminKey || Str::startsWith($activeAdminKey, $item['key'].'.'));
                                    @endphp
                                    <div class="rounded-xl {{ $menuActive ? 'bg-linen dark:bg-white/5' : '' }}" :class="sidebarExpanded() ? '' : 'bg-transparent dark:bg-transparent'">
                                        <button
                                            type="button"
                                            x-on:click="toggleMenu(@js($menu['key']))"
                                            title="{{ $menu['label'] }}"
                                            class="group flex min-h-[48px] w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-black transition {{ $menuActive ? 'text-leaf dark:text-meadow' : 'text-cocoa/75 hover:bg-linen hover:text-leaf dark:text-cream/75 dark:hover:bg-white/10 dark:hover:text-meadow' }}"
                                            :class="sidebarExpanded() ? '' : 'justify-center'"
                                            :aria-expanded="isMenuOpen(@js($menu['key'])) ? 'true' : 'false'"
                                        >
                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $menuActive ? 'bg-white text-leaf ring-1 ring-leaf/10 dark:bg-meadow dark:text-ink dark:ring-0' : 'bg-leaf/10 text-leaf group-hover:bg-leaf group-hover:text-white dark:bg-white/10 dark:text-meadow' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $navIcons[$menu['icon']] ?? $navIcons['modules'] !!}</svg>
                                            </span>
                                            <span x-show="sidebarExpanded()" x-cloak x-transition.opacity class="min-w-0 flex-1 truncate">{{ $menu['label'] }}</span>
                                            <svg x-show="sidebarExpanded()" x-cloak x-transition.opacity xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 transition-transform" :class="isMenuOpen(@js($menu['key'])) ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg>
                                        </button>

                                        <div x-show="sidebarExpanded() && isMenuOpen(@js($menu['key']))" x-cloak x-transition class="space-y-1 px-3 pb-3 pt-1">
                                            @foreach ($menu['items'] as $item)
                                                @php
                                                    $isActive = $activeAdminKey === $item['key'] || Str::startsWith($activeAdminKey, $item['key'].'.');
                                                    $href = route($item['route'], ['locale' => $currentLocale, ...($item['params'] ?? [])]);
                                                @endphp
                                                <a
                                                    href="{{ $href }}"
                                                    class="block rounded-lg px-3 py-2 text-[15px] font-semibold leading-5 transition {{ $isActive ? 'bg-white text-leaf shadow-sm ring-1 ring-leaf/10 dark:bg-white/10 dark:text-meadow dark:ring-white/10' : 'text-cocoa/75 hover:bg-white hover:text-leaf dark:text-cream/70 dark:hover:bg-white/10 dark:hover:text-meadow' }}"
                                                    @if ($isActive) aria-current="page" @endif
                                                >
                                                    <span class="block">{{ $item['label'] }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </nav>

                <div class="mt-4 rounded-2xl border border-leaf/10 bg-linen p-3 dark:border-white/10 dark:bg-white/5" :class="sidebarExpanded() ? '' : 'px-2'">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-forest text-xs font-black uppercase text-white dark:bg-meadow dark:text-ink">{{ Str::of($adminName)->substr(0, 2) }}</span>
                        <div x-show="sidebarExpanded()" x-cloak x-transition.opacity class="min-w-0">
                            <p class="truncate text-sm font-black text-cocoa dark:text-cream">{{ $adminName }}</p>
                            <p class="truncate text-xs text-cocoa/55 dark:text-cream/55">{{ $adminEmail }}</p>
                        </div>
                    </div>
                    <button type="button" x-show="sidebarExpanded()" x-cloak x-transition.opacity x-on:click="logoutOpen = true" class="mt-3 w-full rounded-xl bg-white px-4 py-2.5 text-sm font-black text-cocoa ring-1 ring-leaf/10 transition hover:bg-mint hover:text-leaf dark:bg-white/10 dark:text-cream dark:ring-white/10">
                        Deconnexion
                    </button>
                </div>
            </aside>

            <div x-show="sidebarOpen" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-ink/60 backdrop-blur-sm lg:hidden" x-on:click="sidebarOpen = false"></div>

            <div class="min-h-screen min-w-0 transition-[padding] duration-300" :class="sidebarExpanded() ? 'lg:pl-72' : 'lg:pl-[5.75rem]'">
                <header class="sticky top-0 z-30 border-b border-leaf/10 bg-white/95 shadow-sm backdrop-blur dark:border-white/10 dark:bg-ink/95">
                    <div class="border-b border-leaf/10 bg-linen px-4 py-2 text-xs font-semibold text-leaf dark:border-white/10 dark:bg-[#172414] dark:text-meadow sm:px-6 lg:px-8">
                        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                            <p class="truncate">Back-office connecte a la boutique Denetfils</p>
                            <div class="hidden items-center gap-4 text-cocoa/60 dark:text-cream/60 sm:flex">
                                <a href="{{ route('home.localized', ['locale' => $currentLocale]) }}" class="transition hover:text-leaf dark:hover:text-meadow">Boutique</a>
                                <a href="{{ route('admin.dashboard', ['locale' => $alternateLocale]) }}" class="transition hover:text-leaf dark:hover:text-meadow">{{ strtoupper($alternateLocale) }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-3 sm:px-6 lg:px-8">
                        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <button type="button" x-on:click="sidebarOpen = true" class="admin-icon-btn lg:hidden" aria-label="Ouvrir le menu">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" /></svg>
                                </button>
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">Back-office Denetfils</p>
                                    <h1 class="truncate text-xl font-black text-cocoa dark:text-cream sm:text-2xl">@yield('page_title', 'Administration')</h1>
                                    @hasSection('page_subtitle')
                                        <p class="hidden max-w-2xl truncate text-sm font-semibold text-cocoa/55 dark:text-cream/55 sm:block">@yield('page_subtitle')</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center justify-end gap-2">
                                <button type="button" x-on:click="commandOpen = true; $nextTick(() => $refs.commandInput?.focus())" class="admin-icon-btn" aria-label="Rechercher">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path d="m20 20-3-3" /></svg>
                                </button>
                                <button type="button" x-on:click="quickActionsOpen = true" class="admin-icon-btn" aria-label="Actions rapides">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
                                </button>
                                <button type="button" x-on:click="toggleTheme()" class="admin-icon-btn" aria-label="Changer le theme">
                                    <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4" /><path d="M12 2v2" /><path d="M12 20v2" /><path d="M4.93 4.93l1.41 1.41" /><path d="m17.66 17.66 1.41 1.41" /><path d="M2 12h2" /><path d="M20 12h2" /><path d="m6.34 17.66-1.41 1.41" /><path d="m19.07 4.93-1.41 1.41" /></svg>
                                    <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.99 12.44A8.99 8.99 0 1 1 11.56 3a7 7 0 0 0 9.43 9.44Z" /></svg>
                                </button>
                                <button type="button" x-on:click="logoutOpen = true" class="admin-icon-btn hidden sm:inline-grid" aria-label="Deconnexion">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="m16 17 5-5-5-5" /><path d="M21 12H9" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
                    <div class="mx-auto max-w-7xl">
                        @if (session('status'))
                            <div class="mb-5 rounded-xl border border-leaf/15 bg-mint px-4 py-3 text-sm font-semibold text-leaf dark:border-meadow/20 dark:bg-meadow/10 dark:text-meadow">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->has('admin_action'))
                            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                                {{ $errors->first('admin_action') }}
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </main>
            </div>

            @stack('admin_modals')

            <div x-show="quickActionsOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[70] grid place-items-center bg-ink/70 p-4 backdrop-blur-xl" x-on:click.self="quickActionsOpen = false">
                <div x-show="quickActionsOpen" x-transition class="admin-modal-card w-full max-w-3xl overflow-hidden">
                    <div class="flex items-start justify-between border-b border-leaf/10 p-5 dark:border-white/10 sm:p-6">
                        <div>
                            <p class="admin-kicker">Actions rapides</p>
                            <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">Ouvrir un espace de travail</h2>
                        </div>
                        <button type="button" x-on:click="quickActionsOpen = false" class="admin-icon-btn" aria-label="Fermer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                        </button>
                    </div>
                    <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
                        @foreach ($allNavItems->take(9) as $item)
                            <a href="{{ route($item['route'], ['locale' => $currentLocale, ...($item['params'] ?? [])]) }}" class="group rounded-2xl border border-leaf/10 bg-linen p-4 transition hover:border-leaf/25 hover:bg-mint dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-white text-leaf ring-1 ring-leaf/10 transition group-hover:bg-leaf group-hover:text-white dark:bg-white/10 dark:text-meadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14" /><path d="m12 5 7 7-7 7" /></svg>
                                </span>
                                <h3 class="mt-4 font-black text-cocoa dark:text-cream">{{ $item['label'] }}</h3>
                                <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">{{ $item['hint'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div x-show="commandOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[72] bg-ink/70 p-4 backdrop-blur-xl" x-on:click.self="commandOpen = false">
                <div x-show="commandOpen" x-transition class="admin-modal-card mx-auto mt-16 w-full max-w-2xl overflow-hidden">
                    <div class="border-b border-leaf/10 p-4 dark:border-white/10">
                        <input x-ref="commandInput" x-model="commandQuery" class="w-full bg-transparent text-lg font-black text-cocoa outline-none placeholder:text-cocoa/40 dark:text-cream dark:placeholder:text-cream/40" placeholder="Rechercher un module">
                    </div>
                    <div class="max-h-[60vh] overflow-y-auto p-3">
                        <template x-for="item in filteredCommandItems()" :key="item.href">
                            <a :href="item.href" class="block rounded-xl p-4 transition hover:bg-linen dark:hover:bg-white/10">
                                <p class="font-black text-cocoa dark:text-cream" x-text="item.label"></p>
                                <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55" x-text="item.hint"></p>
                            </a>
                        </template>
                        <p x-show="filteredCommandItems().length === 0" class="rounded-xl p-4 text-sm text-cocoa/55 dark:text-cream/55">Aucun module trouve.</p>
                    </div>
                </div>
            </div>

            <div x-show="logoutOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[74] grid place-items-center bg-ink/70 p-4 backdrop-blur-xl" x-on:click.self="logoutOpen = false">
                <div x-show="logoutOpen" x-transition class="admin-modal-card w-full max-w-md p-6 text-center">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-mint text-leaf dark:bg-meadow/15 dark:text-meadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="m16 17 5-5-5-5" /><path d="M21 12H9" /></svg>
                    </div>
                    <h2 class="mt-4 text-2xl font-black text-cocoa dark:text-cream">Fermer la session admin</h2>
                    <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $adminEmail }}</p>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <button type="button" x-on:click="logoutOpen = false" class="admin-btn-secondary">Annuler</button>
                        <form method="POST" action="{{ route('admin.logout', ['locale' => $currentLocale]) }}">
                            @csrf
                            <button type="submit" class="admin-btn w-full">Sortir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
