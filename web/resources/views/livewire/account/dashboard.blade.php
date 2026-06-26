<div x-data="{ activeTab: 'orders' }" class="account-portal">
    @php
        $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $displayName = $displayName !== '' ? $displayName : __('home.account.overview.fallback_name');
        $initials = collect(explode(' ', $displayName))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
        $initials = $initials !== '' ? mb_strtoupper($initials) : 'DF';
        $isEnglish = $locale === 'en';
        $accountStatus = $user['status'] ?? 'active';
        $defaultAddressLine = is_array($defaultAddress)
            ? trim(($defaultAddress['postal_code'] ?? '') . ' ' . ($defaultAddress['city'] ?? ''))
            : '';
        $shellCard = 'border border-leaf/10 bg-white shadow-sm dark:border-white/10 dark:bg-white/5';
        $sideItem = 'group flex min-h-[46px] items-center justify-between gap-3 rounded-lg px-3 text-sm font-semibold text-cocoa/70 transition hover:bg-linen hover:text-forest dark:text-cream/70 dark:hover:bg-white/10 dark:hover:text-meadow';
        $sideSubItem = 'flex min-h-[36px] items-center justify-between rounded-md px-3 text-xs font-black uppercase tracking-wide text-cocoa/50 transition hover:bg-linen hover:text-forest dark:text-cream/50 dark:hover:bg-white/10 dark:hover:text-meadow';
        $panel = 'rounded-xl border border-leaf/10 bg-white shadow-sm dark:border-white/10 dark:bg-white/5';
        $muted = 'text-cocoa/60 dark:text-cream/60';
        $icon = function (string $name, string $class = 'h-5 w-5') {
            $paths = [
                'home' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v10h5v-6h4v6h5V10"/>',
                'orders' => '<path d="M6 7h12l-1 13H7L6 7Z"/><path d="M9 7a3 3 0 0 1 6 0"/><path d="M9 12h6"/>',
                'heart' => '<path d="M20.8 5.6a5.1 5.1 0 0 0-7.2 0L12 7.2l-1.6-1.6a5.1 5.1 0 1 0-7.2 7.2L12 21l8.8-8.2a5.1 5.1 0 0 0 0-7.2Z"/>',
                'user' => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>',
                'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
                'map' => '<path d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
                'logout' => '<path d="M10 17 15 12l-5-5"/><path d="M15 12H3"/><path d="M21 4v16h-7"/>',
                'chevron' => '<path d="m9 18 6-6-6-6"/>',
                'down' => '<path d="m6 9 6 6 6-6"/>',
                'plus' => '<path d="M12 5v14"/><path d="M5 12h14"/>',
                'calendar' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/>',
                'card' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M7 15h3"/>',
                'truck' => '<path d="M14 18V6H3v12h11Z"/><path d="M14 9h4l3 4v5h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
                'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1A2 2 0 1 1 4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1A2 2 0 1 1 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3h.1a1.7 1.7 0 0 0 1-1.6V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1A2 2 0 1 1 19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9v.1a1.7 1.7 0 0 0 1.6 1h.1a2 2 0 1 1 0 4H21a1.7 1.7 0 0 0-1.6 1Z"/>',
            ];

            return '<svg class="'.e($class).'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['home']).'</svg>';
        };
    @endphp

    @if ($statusMessage)
        <div class="mb-4 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-black text-forest dark:border-white/10 dark:bg-white/10 dark:text-meadow">
            {{ $statusMessage }}
        </div>
    @elseif (session('status'))
        <div class="mb-4 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-black text-forest dark:border-white/10 dark:bg-white/10 dark:text-meadow">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-coral/25 bg-coral/10 px-4 py-3 text-sm font-semibold text-cocoa dark:text-cream">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-leaf/10 bg-[#f6f4ee] shadow-tropical dark:border-white/10 dark:bg-[#101b10]">
        <header class="flex min-h-[72px] items-center justify-between border-b border-leaf/10 bg-white px-4 dark:border-white/10 dark:bg-white/5 sm:px-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">{{ __('home.account.nav') }}</p>
                <h1 class="mt-1 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.profile.title') }}</h1>
            </div>
            <div class="hidden items-center gap-3 sm:flex">
                <div class="text-right">
                    <p class="text-sm font-black text-cocoa dark:text-cream">{{ $displayName }}</p>
                    <p class="text-xs font-semibold {{ $muted }}">{{ $user['email'] ?? '' }}</p>
                </div>
                <div class="grid h-11 w-11 place-items-center rounded-full bg-forest text-sm font-black text-cream dark:bg-meadow dark:text-ink">{{ $initials }}</div>
            </div>
        </header>

        <div class="grid min-h-[720px] lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="border-b border-leaf/10 bg-white p-4 dark:border-white/10 dark:bg-white/5 lg:border-b-0 lg:border-r">
                <div class="rounded-xl bg-linen p-4 dark:bg-white/5">
                    <div class="flex items-center gap-3">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-forest text-sm font-black text-cream dark:bg-meadow dark:text-ink">{{ $initials }}</div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-cocoa dark:text-cream">{{ $isEnglish ? 'Welcome' : 'Bienvenue' }}, {{ $displayName }}</p>
                            <p class="truncate text-xs font-semibold {{ $muted }}">{{ $roles ?: 'customer' }}</p>
                        </div>
                    </div>
                </div>

                <nav class="mt-4 space-y-1" aria-label="{{ $isEnglish ? 'Customer account navigation' : 'Navigation du compte client' }}">
                    <button type="button" x-on:click="activeTab = 'orders'" class="{{ $sideItem }} w-full text-left" x-bind:class="activeTab === 'orders' ? 'bg-linen text-forest dark:bg-white/10 dark:text-meadow' : ''">
                        <span class="flex items-center gap-3"><span class="text-forest dark:text-meadow">{!! $icon('orders') !!}</span>{{ __('home.account.orders.title') }}</span>
                        <span class="rounded-full bg-linen px-2 py-0.5 text-xs font-black text-forest dark:bg-white/10 dark:text-meadow">{{ $ordersCount }}</span>
                    </button>
                    <button type="button" x-on:click="activeTab = 'favorites'" class="{{ $sideItem }} w-full text-left" x-bind:class="activeTab === 'favorites' ? 'bg-linen text-forest dark:bg-white/10 dark:text-meadow' : ''">
                        <span class="flex items-center gap-3"><span class="text-forest dark:text-meadow">{!! $icon('heart') !!}</span>{{ $isEnglish ? 'Favorites' : 'Favoris' }}</span>
                        {!! $icon('chevron', 'h-4 w-4 text-cocoa/35 dark:text-cream/35') !!}
                    </button>

                    <details class="group" open>
                        <summary class="{{ $sideItem }} cursor-pointer list-none">
                            <span class="flex items-center gap-3"><span class="text-forest dark:text-meadow">{!! $icon('user') !!}</span>{{ __('home.account.profile.section_title') }}</span>
                            <span class="transition group-open:rotate-90">{!! $icon('chevron', 'h-4 w-4 text-cocoa/35 dark:text-cream/35') !!}</span>
                        </summary>
                        <div class="ml-8 mt-1 grid gap-1 border-l border-leaf/10 pl-3 dark:border-white/10">
                            <button type="button" x-on:click="activeTab = 'profile'" class="{{ $sideSubItem }} text-left" x-bind:class="activeTab === 'profile' ? 'bg-linen text-forest dark:bg-white/10 dark:text-meadow' : ''"><span>{{ $isEnglish ? 'Personal data' : 'Infos personnelles' }}</span></button>
                            <button type="button" x-on:click="activeTab = 'profile'" class="{{ $sideSubItem }} text-left" x-bind:class="activeTab === 'profile' ? 'bg-linen text-forest dark:bg-white/10 dark:text-meadow' : ''"><span>{{ $isEnglish ? 'Preferences' : 'Preferences' }}</span></button>
                        </div>
                    </details>

                    <button type="button" x-on:click="activeTab = 'security'" class="{{ $sideItem }} w-full text-left" x-bind:class="activeTab === 'security' ? 'bg-linen text-forest dark:bg-white/10 dark:text-meadow' : ''">
                        <span class="flex items-center gap-3"><span class="text-forest dark:text-meadow">{!! $icon('lock') !!}</span>{{ $isEnglish ? 'Password' : 'Mot de passe' }}</span>
                        {!! $icon('chevron', 'h-4 w-4 text-cocoa/35 dark:text-cream/35') !!}
                    </button>

                    <details class="group" open>
                        <summary class="{{ $sideItem }} cursor-pointer list-none">
                            <span class="flex items-center gap-3"><span class="text-forest dark:text-meadow">{!! $icon('map') !!}</span>{{ __('home.account.addresses.title') }}</span>
                            <span class="transition group-open:rotate-90">{!! $icon('chevron', 'h-4 w-4 text-cocoa/35 dark:text-cream/35') !!}</span>
                        </summary>
                        <div class="ml-8 mt-1 grid gap-1 border-l border-leaf/10 pl-3 dark:border-white/10">
                            <button type="button" x-on:click="activeTab = 'addresses'" class="{{ $sideSubItem }} text-left" x-bind:class="activeTab === 'addresses' ? 'bg-linen text-forest dark:bg-white/10 dark:text-meadow' : ''"><span>{{ $isEnglish ? 'Saved addresses' : 'Adresses enregistrees' }}</span><span>{{ $addressesCount }}</span></button>
                            <button type="button" x-on:click="activeTab = 'newAddress'" class="{{ $sideSubItem }} text-left" x-bind:class="activeTab === 'newAddress' ? 'bg-linen text-forest dark:bg-white/10 dark:text-meadow' : ''"><span>{{ __('home.account.addresses.new_title') }}</span>{!! $icon('plus', 'h-4 w-4') !!}</button>
                        </div>
                    </details>

                    <button type="button" wire:click="logout" wire:loading.attr="disabled" class="{{ $sideItem }} w-full text-left hover:text-coral">
                        <span class="flex items-center gap-3"><span class="text-coral">{!! $icon('logout') !!}</span>{{ __('home.account.profile.logout') }}</span>
                    </button>
                </nav>
            </aside>

            <main class="min-w-0 bg-[#f8f7f3] p-4 dark:bg-[#0f2110] sm:p-6">
                <section id="overview" x-show="activeTab === 'orders'" x-transition.opacity.duration.180ms class="grid gap-4 sm:grid-cols-3">
                    <div class="{{ $panel }} p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ __('home.account.overview.orders') }}</p>
                        <p class="mt-2 text-3xl font-black text-forest dark:text-meadow">{{ $ordersCount }}</p>
                    </div>
                    <div class="{{ $panel }} p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ __('home.account.overview.addresses') }}</p>
                        <p class="mt-2 text-3xl font-black text-forest dark:text-meadow">{{ $addressesCount }}</p>
                    </div>
                    <div class="{{ $panel }} p-4">
                        <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ __('home.account.profile.status') }}</p>
                        <p class="mt-3 truncate text-lg font-black text-forest dark:text-meadow">{{ $accountStatus }}</p>
                    </div>
                </section>

                <section id="orders" x-show="activeTab === 'orders'" x-transition.opacity.duration.180ms class="{{ $panel }} mt-5 p-5">
                    <div class="flex flex-col gap-3 border-b border-leaf/10 pb-4 dark:border-white/10 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">{{ __('home.account.orders.eyebrow') }}</p>
                            <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.orders.title') }}</h2>
                        </div>
                        <p class="max-w-xl text-sm font-semibold leading-6 {{ $muted }}">{{ __('home.account.orders.intro') }}</p>
                    </div>

                    <div class="divide-y divide-leaf/10 dark:divide-white/10">
                        @forelse ($orders as $order)
                            @php
                                $items = $order['items'] ?? [];
                                $placedAt = $order['placed_at'] ?? $order['created_at'] ?? null;
                            @endphp
                            <article class="grid gap-4 py-5 transition hover:bg-linen/55 dark:hover:bg-white/5 lg:grid-cols-[minmax(190px,0.9fr)_minmax(0,1.25fr)_130px_150px]" wire:key="order-{{ $order['id'] ?? $loop->index }}">
                                <div class="min-w-0">
                                    <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ __('home.account.orders.number') }}</p>
                                    <h3 class="mt-1 truncate text-base font-black text-cocoa dark:text-cream">
                                        <a href="{{ route('account.orders.show', ['locale' => $locale, 'order' => $order['id']]) }}" class="transition hover:text-forest dark:hover:text-meadow" wire:navigate>
                                            {{ $order['order_number'] ?? '-' }}
                                        </a>
                                    </h3>
                                    @if ($placedAt)
                                        <p class="mt-1 text-xs font-semibold {{ $muted }}">
                                            {{ \Illuminate\Support\Carbon::parse($placedAt)->locale($locale)->isoFormat('LL') }}
                                        </p>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ $isEnglish ? 'Products' : 'Produits' }}</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach (array_slice($items, 0, 3) as $item)
                                            @php
                                                $imageUrl = data_get($item, 'product.image.url');
                                                $productName = data_get($item, 'product.name', '-');
                                            @endphp
                                            <span class="inline-flex min-h-[38px] max-w-[220px] items-center gap-2 rounded-lg bg-linen py-1 pl-1 pr-3 text-xs font-black text-cocoa dark:bg-white/5 dark:text-cream">
                                                @if ($imageUrl)
                                                    <img src="{{ $imageUrl }}" alt="{{ $productName }}" class="h-8 w-8 shrink-0 rounded-md object-cover" loading="lazy" decoding="async">
                                                @else
                                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-md bg-mint text-[10px] text-forest dark:bg-white/10 dark:text-meadow">DF</span>
                                                @endif
                                                <span class="truncate">{{ $productName }} x {{ $item['quantity'] ?? 1 }}</span>
                                            </span>
                                        @endforeach
                                        @if (count($items) > 3)
                                            <span class="inline-flex min-h-[34px] items-center rounded-lg bg-linen px-3 text-xs font-black text-cocoa/55 dark:bg-white/5 dark:text-cream/55">
                                                +{{ count($items) - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ $isEnglish ? 'Total' : 'Total' }}</p>
                                    <p class="mt-2 text-base font-black text-cocoa dark:text-cream">{{ $order['formatted_total'] ?? '' }}</p>
                                </div>

                                <div class="flex flex-col items-start gap-2 lg:items-end">
                                    <span class="inline-flex min-h-[30px] items-center rounded-full bg-mint px-3 text-xs font-black uppercase tracking-wide text-forest dark:bg-white/10 dark:text-meadow">{{ $order['status'] ?? '-' }}</span>
                                    <span class="text-xs font-semibold {{ $muted }}">{{ __('home.account.orders.payment') }}: {{ $order['payment_status'] ?? '-' }}</span>
                                    <a href="{{ route('account.orders.show', ['locale' => $locale, 'order' => $order['id']]) }}" class="inline-flex min-h-[34px] items-center rounded-full border border-leaf/15 px-3 text-xs font-black uppercase tracking-wide text-forest transition hover:bg-mint dark:border-white/10 dark:text-meadow" wire:navigate>
                                        {{ $isEnglish ? 'Details' : 'Details' }}
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="py-8 text-sm font-semibold {{ $muted }}">
                                {{ __('home.account.orders.empty') }}
                            </div>
                        @endforelse
                    </div>
                </section>

                <section id="favorites" x-cloak x-show="activeTab === 'favorites'" x-transition.opacity.duration.180ms class="{{ $panel }} p-5">
                    <div class="flex items-start gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-mint text-forest dark:bg-white/10 dark:text-meadow">{!! $icon('heart') !!}</span>
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">{{ $isEnglish ? 'Favorites' : 'Favoris' }}</p>
                            <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $isEnglish ? 'Saved products' : 'Produits favoris' }}</h2>
                            <p class="mt-2 max-w-2xl text-sm font-semibold leading-7 {{ $muted }}">
                                {{ $isEnglish ? 'This space is ready for saved products and quick reorders.' : 'Cet espace est pret pour afficher les produits favoris et les achats rapides.' }}
                            </p>
                        </div>
                    </div>
                </section>

                <div x-cloak x-show="activeTab === 'profile' || activeTab === 'security'" x-transition.opacity.duration.180ms class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <section id="profile" x-show="activeTab === 'profile'" class="{{ $panel }} p-5 xl:col-span-2">
                        <div class="border-b border-leaf/10 pb-4 dark:border-white/10">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">{{ $isEnglish ? 'Personal area' : 'Espace personnel' }}</p>
                            <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.profile.section_title') }}</h2>
                        </div>

                        <form wire:submit.prevent="updateProfile" class="mt-5 grid gap-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="profile-first-name" class="text-sm font-black text-cocoa dark:text-cream">{{ __('home.account.auth.first_name') }}</label>
                                    <input id="profile-first-name" wire:model="profile.first_name" required class="input-premium mt-2 w-full">
                                </div>
                                <div>
                                    <label for="profile-last-name" class="text-sm font-black text-cocoa dark:text-cream">{{ __('home.account.auth.last_name') }}</label>
                                    <input id="profile-last-name" wire:model="profile.last_name" required class="input-premium mt-2 w-full">
                                </div>
                            </div>

                            <div id="preferences" class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="profile-phone" class="text-sm font-black text-cocoa dark:text-cream">{{ __('home.account.auth.phone') }}</label>
                                    <input id="profile-phone" wire:model="profile.phone" class="input-premium mt-2 w-full">
                                </div>
                                <div>
                                    <label for="profile-country" class="text-sm font-black text-cocoa dark:text-cream">{{ __('home.account.auth.country') }}</label>
                                    <select id="profile-country" wire:model="profile.country_code" required class="input-premium mt-2 w-full">
                                        @foreach ($countries as $country)
                                            <option value="{{ $country['code'] }}">{{ $country['name'] }} &middot; {{ $country['currency'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="profile-locale" class="text-sm font-black text-cocoa dark:text-cream">{{ __('home.account.profile.preferred_locale') }}</label>
                                    <select id="profile-locale" wire:model="profile.preferred_locale" required class="input-premium mt-2 w-full">
                                        <option value="fr">{{ __('home.locale.fr') }}</option>
                                        <option value="en">{{ __('home.locale.en') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="profile-timezone" class="text-sm font-black text-cocoa dark:text-cream">{{ __('home.account.profile.timezone') }}</label>
                                    <select id="profile-timezone" wire:model="profile.timezone" required class="input-premium mt-2 w-full">
                                        @foreach ($timezones as $timezone)
                                            <option value="{{ $timezone }}">{{ $timezone }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn-primary w-full sm:w-fit" wire:loading.attr="disabled" wire:target="updateProfile">
                                <span wire:loading.remove wire:target="updateProfile">{{ __('home.account.profile.save') }}</span>
                                <span wire:loading wire:target="updateProfile">{{ __('home.cart.loading') }}</span>
                            </button>
                        </form>
                    </section>

                    <aside id="security" x-show="activeTab === 'security'" class="{{ $panel }} p-5 xl:col-span-2">
                        <div class="flex items-center gap-3">
                            <span class="grid h-11 w-11 place-items-center rounded-xl bg-mint text-forest dark:bg-white/10 dark:text-meadow">{!! $icon('lock') !!}</span>
                            <div>
                                <h2 class="text-lg font-black text-cocoa dark:text-cream">{{ $isEnglish ? 'Password and security' : 'Mot de passe et securite' }}</h2>
                                <p class="text-sm font-semibold {{ $muted }}">{{ $isEnglish ? 'Security settings for your customer account.' : 'Parametres de securite de votre compte client.' }}</p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-xl bg-linen p-4 dark:bg-white/5">
                                <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">Email</p>
                                <p class="mt-1 truncate text-sm font-black text-cocoa dark:text-cream">{{ $user['email'] ?? '' }}</p>
                            </div>
                            <div class="rounded-xl bg-linen p-4 dark:bg-white/5">
                                <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ __('home.account.profile.status') }}</p>
                                <p class="mt-1 truncate text-sm font-black text-cocoa dark:text-cream">{{ $accountStatus }}</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-dashed border-leaf/20 bg-linen p-5 text-sm font-semibold leading-7 {{ $muted }} dark:border-white/10 dark:bg-white/5">
                            {{ $isEnglish ? 'Password update will be connected when the API exposes this endpoint.' : 'La modification du mot de passe sera connectee quand l API exposera cet endpoint.' }}
                        </div>
                    </aside>
                </div>

                <section id="new-address" x-cloak x-show="activeTab === 'newAddress'" x-transition.opacity.duration.180ms class="{{ $panel }} p-5">
                    <details class="group">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">{{ __('home.account.addresses.title') }}</p>
                                <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.addresses.new_title') }}</h2>
                                <p class="mt-2 text-sm font-semibold leading-6 {{ $muted }}">{{ __('home.account.addresses.intro') }}</p>
                            </div>
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-linen text-forest transition group-open:rotate-180 dark:bg-white/10 dark:text-meadow">{!! $icon('down', 'h-5 w-5') !!}</span>
                        </summary>

                        <form wire:submit.prevent="createAddress" class="mt-5 border-t border-leaf/10 pt-5 dark:border-white/10">
                            <div class="grid gap-4">
                                @include('livewire.account.partials.address-fields', ['model' => 'newAddress', 'prefix' => 'new'])
                            </div>
                            <button type="submit" class="btn-primary mt-5 w-full sm:w-fit" wire:loading.attr="disabled" wire:target="createAddress">
                                <span wire:loading.remove wire:target="createAddress">{{ __('home.account.addresses.save') }}</span>
                                <span wire:loading wire:target="createAddress">{{ __('home.cart.loading') }}</span>
                            </button>
                        </form>
                    </details>
                </section>

                <section id="addresses" x-cloak x-show="activeTab === 'addresses'" x-transition.opacity.duration.180ms>
                    <div class="mb-3 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">{{ __('home.account.addresses.title') }}</p>
                            <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.addresses.title') }}</h2>
                        </div>
                        <p class="text-sm font-black {{ $muted }}">{{ $addressesCount }}</p>
                    </div>

                    <div class="grid gap-4">
                        @forelse ($addresses as $address)
                            <details class="{{ $panel }} group overflow-hidden" @if ($loop->first) open @endif wire:key="address-{{ $address['id'] }}">
                                <summary class="cursor-pointer list-none p-5">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="text-lg font-black text-cocoa dark:text-cream">{{ $address['label'] ?: $address['recipient_name'] }}</h3>
                                                @if ($address['is_default'])
                                                    <span class="rounded-full bg-mint px-3 py-1 text-xs font-black uppercase tracking-wide text-leaf dark:bg-white/10 dark:text-meadow">{{ __('home.account.addresses.default_badge') }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-2 text-sm font-semibold leading-6 {{ $muted }}">
                                                {{ $address['street_line_1'] }}, {{ $address['postal_code'] }} {{ $address['city'] }} &middot; {{ $address['country_code'] }}
                                            </p>
                                        </div>
                                        <span class="inline-flex min-h-[34px] items-center rounded-full border border-leaf/15 px-3 py-1 text-xs font-black uppercase tracking-wide text-cocoa/70 dark:border-white/10 dark:text-cream/70">
                                            {{ __('home.account.addresses.' . $address['type']) }}
                                        </span>
                                    </div>
                                </summary>

                                <form wire:submit.prevent="updateAddress({{ (int) $address['id'] }})" class="border-t border-leaf/10 p-5 dark:border-white/10">
                                    <div class="grid gap-4">
                                        @include('livewire.account.partials.address-fields', ['model' => 'addressForms.' . $address['id'], 'prefix' => 'address-' . $address['id']])
                                    </div>
                                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                        <button type="submit" class="btn-primary w-full sm:w-fit" wire:loading.attr="disabled" wire:target="updateAddress({{ (int) $address['id'] }})">
                                            {{ __('home.account.addresses.update') }}
                                        </button>
                                        <button type="button" wire:click="deleteAddress({{ (int) $address['id'] }})" wire:loading.attr="disabled" class="inline-flex min-h-[46px] items-center justify-center rounded-full border border-leaf/20 px-5 py-3 text-sm font-black uppercase tracking-wide text-cocoa transition hover:border-coral hover:text-coral disabled:opacity-60 dark:border-white/10 dark:text-cream">
                                            {{ __('home.account.addresses.delete') }}
                                        </button>
                                    </div>
                                </form>
                            </details>
                        @empty
                            <div class="{{ $panel }} p-6 text-sm font-semibold {{ $muted }}">
                                {{ __('home.account.addresses.empty') }}
                            </div>
                        @endforelse
                    </div>
                </section>
            </main>
        </div>
    </div>
</div>
