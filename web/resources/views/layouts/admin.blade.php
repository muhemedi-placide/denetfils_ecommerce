@php
    $currentLocale = $locale ?? app()->getLocale();
    $adminName = data_get($adminUser ?? [], 'name', 'Admin Denetfils');
    $adminEmail = data_get($adminUser ?? [], 'email', '');
    $navItems = [
        ['key' => 'dashboard', 'label' => 'Pilotage', 'route' => 'admin.dashboard', 'hint' => 'KPIs'],
        ['key' => 'catalog', 'label' => 'Catalogue', 'route' => 'admin.catalog', 'hint' => 'Produits'],
        ['key' => 'inventory', 'label' => 'Stock', 'route' => 'admin.inventory', 'hint' => 'Alertes'],
        ['key' => 'users', 'label' => 'Utilisateurs', 'route' => 'admin.users', 'hint' => 'Clients & equipe'],
        ['key' => 'access', 'label' => 'Acces', 'route' => 'admin.access', 'hint' => 'Roles'],
        ['key' => 'audit', 'label' => 'Audit', 'route' => 'admin.audit', 'hint' => 'Journal'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Back-office') | Denetfils</title>
        <meta name="robots" content="noindex,nofollow">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f7f5ef] text-[#1f2a1c] antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
            <aside class="hidden border-r border-black/5 bg-[#12210f] text-white lg:block">
                <div class="sticky top-0 flex h-screen flex-col p-5">
                    <a href="{{ route('admin.dashboard', ['locale' => $currentLocale]) }}" class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f15b2a] text-sm font-black">DF</span>
                        <span>
                            <span class="block text-sm font-black uppercase tracking-[0.22em]">Denetfils</span>
                            <span class="block text-xs text-white/55">Back-office commerce</span>
                        </span>
                    </a>

                    <nav class="mt-8 space-y-2" aria-label="Administration">
                        @foreach ($navItems as $item)
                            @php $isActive = ($activeAdmin ?? '') === $item['key']; @endphp
                            <a href="{{ route($item['route'], ['locale' => $currentLocale]) }}" class="group flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-bold transition {{ $isActive ? 'bg-white text-[#12210f] shadow-xl' : 'text-white/72 hover:bg-white/10 hover:text-white' }}">
                                <span>{{ $item['label'] }}</span>
                                <span class="rounded-full px-2 py-1 text-[10px] uppercase tracking-wide {{ $isActive ? 'bg-[#e8f6dd] text-[#2f7d1b]' : 'bg-white/10 text-white/55 group-hover:text-white' }}">{{ $item['hint'] }}</span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="mt-auto rounded-3xl bg-white/10 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/45">Session admin</p>
                        <p class="mt-2 truncate text-sm font-extrabold">{{ $adminName }}</p>
                        <p class="mt-1 truncate text-xs text-white/55">{{ $adminEmail }}</p>
                        <form method="POST" action="{{ route('admin.logout', ['locale' => $currentLocale]) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="w-full rounded-2xl bg-white px-4 py-3 text-sm font-black text-[#12210f] transition hover:bg-[#e8f6dd]">Deconnexion</button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="sticky top-0 z-30 border-b border-black/5 bg-white/90 px-4 py-3 shadow-sm backdrop-blur lg:px-8">
                    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <a href="{{ route('admin.dashboard', ['locale' => $currentLocale]) }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#12210f] text-xs font-black text-white lg:hidden">DF</a>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-black uppercase tracking-[0.2em] text-[#2f7d1b]">Back-office Denetfils</p>
                                <h1 class="truncate text-lg font-black text-[#1f2a1c] sm:text-xl">@yield('page_title', 'Administration')</h1>
                            </div>
                        </div>
                        <div class="hidden items-center gap-2 text-right sm:flex">
                            <div>
                                <p class="truncate text-sm font-extrabold text-[#1f2a1c]">{{ $adminName }}</p>
                                <p class="truncate text-xs text-[#1f2a1c]/55">{{ $adminEmail }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.logout', ['locale' => $currentLocale]) }}">
                                @csrf
                                <button type="submit" class="rounded-full border border-[#2f7d1b]/20 bg-white px-4 py-2 text-xs font-black uppercase tracking-wide text-[#2f7d1b] hover:bg-[#e8f6dd]">Quitter</button>
                            </form>
                        </div>
                    </div>

                    <nav class="mobile-scrollbarless mt-3 flex gap-2 overflow-x-auto lg:hidden" aria-label="Administration mobile">
                        @foreach ($navItems as $item)
                            @php $isActive = ($activeAdmin ?? '') === $item['key']; @endphp
                            <a href="{{ route($item['route'], ['locale' => $currentLocale]) }}" class="shrink-0 rounded-full px-4 py-2 text-xs font-black uppercase tracking-wide {{ $isActive ? 'bg-[#12210f] text-white' : 'bg-[#f7f5ef] text-[#1f2a1c]/70' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </nav>
                </header>

                <main class="px-4 py-5 lg:px-8 lg:py-8">
                    <div class="mx-auto max-w-7xl">
                        @if (session('status'))
                            <div class="mb-5 rounded-2xl border border-[#2f7d1b]/15 bg-[#e8f6dd] px-4 py-3 text-sm font-semibold text-[#2f7d1b]">
                                {{ session('status') }}
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
