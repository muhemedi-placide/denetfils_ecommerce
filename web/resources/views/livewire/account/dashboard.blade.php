<div>
    @php
        $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $displayName = $displayName !== '' ? $displayName : __('home.account.overview.fallback_name');
        $defaultAddressLine = is_array($defaultAddress)
            ? trim(($defaultAddress['postal_code'] ?? '') . ' ' . ($defaultAddress['city'] ?? ''))
            : '';
    @endphp

    @if ($statusMessage)
        <div class="mt-8 rounded-lg border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-leaf dark:border-white/10 dark:bg-white/10 dark:text-meadow">
            {{ $statusMessage }}
        </div>
    @elseif (session('status'))
        <div class="mt-8 rounded-lg border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-leaf dark:border-white/10 dark:bg-white/10 dark:text-meadow">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-8 rounded-lg border border-coral/25 bg-coral/10 px-4 py-3 text-sm text-cocoa dark:text-cream">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-8 grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
        <section class="rounded-lg border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ __('home.account.overview.eyebrow') }}</p>
                    <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.overview.title', ['name' => $displayName]) }}</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.account.overview.body') }}</p>
                </div>

                <div class="rounded-lg border border-leaf/10 bg-linen px-4 py-3 text-sm dark:border-white/10 dark:bg-ink">
                    <p class="font-bold text-cocoa dark:text-cream">{{ $user['email'] ?? '' }}</p>
                    <p class="mt-1 text-cocoa/55 dark:text-cream/55">{{ __('home.account.profile.status') }}: {{ $user['status'] ?? 'active' }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-leaf/10 bg-cream p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-xs font-bold uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ __('home.account.overview.orders') }}</p>
                    <p class="mt-2 text-3xl font-black text-cocoa dark:text-cream">{{ $ordersCount }}</p>
                </div>
                <div class="rounded-lg border border-leaf/10 bg-cream p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-xs font-bold uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ __('home.account.overview.addresses') }}</p>
                    <p class="mt-2 text-3xl font-black text-cocoa dark:text-cream">{{ $addressesCount }}</p>
                </div>
                <div class="rounded-lg border border-leaf/10 bg-cream p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-xs font-bold uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ __('home.account.profile.role') }}</p>
                    <p class="mt-2 truncate text-lg font-black text-cocoa dark:text-cream">{{ $roles ?: 'customer' }}</p>
                </div>
            </div>
        </section>

        <aside class="rounded-lg border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ __('home.account.overview.default_shipping') }}</p>
            @if (is_array($defaultAddress))
                <h3 class="mt-3 text-xl font-black text-cocoa dark:text-cream">{{ $defaultAddress['label'] ?: $defaultAddress['recipient_name'] }}</h3>
                <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">
                    {{ $defaultAddress['street_line_1'] ?? '' }}
                    @if ($defaultAddressLine !== '')
                        <br>{{ $defaultAddressLine }}
                    @endif
                    @if (! empty($defaultAddress['country_code']))
                        <br>{{ $defaultAddress['country_code'] }}
                    @endif
                </p>
            @else
                <h3 class="mt-3 text-xl font-black text-cocoa dark:text-cream">{{ __('home.account.overview.no_default_shipping') }}</h3>
                <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ __('home.account.addresses.intro') }}</p>
            @endif

            <button type="button" wire:click="logout" wire:loading.attr="disabled" class="mt-6 w-full rounded-lg border border-leaf/20 bg-white px-5 py-3 text-sm font-bold uppercase tracking-wide text-cocoa transition hover:border-coral hover:text-coral disabled:opacity-60 dark:border-white/10 dark:bg-white/5 dark:text-cream">
                {{ __('home.account.profile.logout') }}
            </button>
        </aside>
    </div>

    <section class="mt-6 rounded-lg border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ __('home.account.orders.eyebrow') }}</p>
                <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.orders.title') }}</h2>
            </div>
            <p class="max-w-xl text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ __('home.account.orders.intro') }}</p>
        </div>

        <div class="mt-5 space-y-3">
            @forelse ($orders as $order)
                @php
                    $items = $order['items'] ?? [];
                    $placedAt = $order['placed_at'] ?? $order['created_at'] ?? null;
                @endphp
                <article class="rounded-lg border border-leaf/10 bg-linen p-4 dark:border-white/10 dark:bg-ink/70" wire:key="order-{{ $order['id'] ?? $loop->index }}">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-black text-cocoa dark:text-cream">{{ $order['order_number'] ?? __('home.account.orders.number') }}</h3>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold uppercase tracking-wide text-leaf dark:bg-white/10 dark:text-meadow">{{ $order['status'] ?? '-' }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold text-cocoa/60 dark:text-cream/60">
                                <span>{{ __('home.account.orders.payment') }}: {{ $order['payment_status'] ?? '-' }}</span>
                                <span>{{ __('home.account.orders.fulfillment') }}: {{ $order['fulfillment_status'] ?? '-' }}</span>
                                <span>{{ trans_choice('home.account.orders.items', count($items), ['count' => count($items)]) }}</span>
                            </div>
                        </div>

                        <div class="shrink-0 text-left lg:text-right">
                            <p class="text-xl font-black text-cocoa dark:text-cream">{{ $order['formatted_total'] ?? '' }}</p>
                            @if ($placedAt)
                                <p class="mt-1 text-xs font-semibold text-cocoa/55 dark:text-cream/55">
                                    {{ __('home.account.orders.placed_at', ['date' => \Illuminate\Support\Carbon::parse($placedAt)->locale($locale)->isoFormat('LL')]) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if (! empty($items))
                        <ul class="mt-4 grid gap-2 text-sm text-cocoa/70 dark:text-cream/70 sm:grid-cols-2">
                            @foreach (array_slice($items, 0, 2) as $item)
                                <li class="rounded-lg bg-white px-3 py-2 dark:bg-white/5">
                                    @if (data_get($item, 'product.slug'))
                                        <a class="font-bold text-cocoa transition hover:text-leaf dark:text-cream dark:hover:text-meadow" href="{{ route('products.show', ['locale' => $locale, 'slug' => data_get($item, 'product.slug')]) }}" wire:navigate>
                                            {{ data_get($item, 'product.name') }}
                                        </a>
                                    @else
                                        <span class="font-bold text-cocoa dark:text-cream">{{ data_get($item, 'product.name') }}</span>
                                    @endif
                                    <span class="text-cocoa/55 dark:text-cream/55">x {{ $item['quantity'] ?? 1 }}</span>
                                </li>
                            @endforeach

                            @if (count($items) > 2)
                                <li class="rounded-lg bg-white px-3 py-2 font-semibold text-cocoa/55 dark:bg-white/5 dark:text-cream/55">
                                    +{{ count($items) - 2 }} {{ __('home.account.orders.more_items') }}
                                </li>
                            @endif
                        </ul>
                    @endif
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-leaf/20 bg-linen p-6 text-sm leading-7 text-cocoa/65 dark:border-white/15 dark:bg-ink dark:text-cream/65">
                    {{ __('home.account.orders.empty') }}
                </div>
            @endforelse
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        <section class="rounded-lg border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
            <h2 class="text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.profile.section_title') }}</h2>
            <form wire:submit.prevent="updateProfile" class="mt-6 space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="profile-first-name" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.first_name') }}</label>
                        <input id="profile-first-name" wire:model="profile.first_name" required class="mt-2 min-h-[48px] w-full rounded-lg border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                    </div>
                    <div>
                        <label for="profile-last-name" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.last_name') }}</label>
                        <input id="profile-last-name" wire:model="profile.last_name" required class="mt-2 min-h-[48px] w-full rounded-lg border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="profile-phone" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.phone') }}</label>
                        <input id="profile-phone" wire:model="profile.phone" class="mt-2 min-h-[48px] w-full rounded-lg border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                    </div>
                    <div>
                        <label for="profile-country" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.country') }}</label>
                        <select id="profile-country" wire:model="profile.country_code" required class="mt-2 min-h-[48px] w-full rounded-lg border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                            @foreach ($countries as $country)
                                <option value="{{ $country['code'] }}">{{ $country['name'] }} &middot; {{ $country['currency'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="profile-locale" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.profile.preferred_locale') }}</label>
                        <select id="profile-locale" wire:model="profile.preferred_locale" required class="mt-2 min-h-[48px] w-full rounded-lg border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                            <option value="fr">{{ __('home.locale.fr') }}</option>
                            <option value="en">{{ __('home.locale.en') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="profile-timezone" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.profile.timezone') }}</label>
                        <select id="profile-timezone" wire:model="profile.timezone" required class="mt-2 min-h-[48px] w-full rounded-lg border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}">{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="updateProfile">
                    <span wire:loading.remove wire:target="updateProfile">{{ __('home.account.profile.save') }}</span>
                    <span wire:loading wire:target="updateProfile">{{ __('home.cart.loading') }}</span>
                </button>
            </form>
        </section>

        <section class="rounded-lg border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ __('home.account.addresses.title') }}</p>
            <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.addresses.new_title') }}</h2>
            <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.account.addresses.intro') }}</p>

            <form wire:submit.prevent="createAddress" class="mt-6 space-y-4">
                @include('livewire.account.partials.address-fields', ['model' => 'newAddress', 'prefix' => 'new'])
                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="createAddress">
                    <span wire:loading.remove wire:target="createAddress">{{ __('home.account.addresses.save') }}</span>
                    <span wire:loading wire:target="createAddress">{{ __('home.cart.loading') }}</span>
                </button>
            </form>
        </section>
    </div>

    <section class="mt-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-leaf dark:text-meadow">{{ __('home.account.addresses.title') }}</p>
                <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.addresses.title') }}</h2>
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-2">
            @forelse ($addresses as $address)
                <details class="rounded-lg border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5" @if ($loop->first) open @endif wire:key="address-{{ $address['id'] }}">
                    <summary class="cursor-pointer list-none">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-black text-cocoa dark:text-cream">{{ $address['label'] ?: $address['recipient_name'] }}</h3>
                                    @if ($address['is_default'])
                                        <span class="rounded-full bg-mint px-3 py-1 text-xs font-bold uppercase tracking-wide text-leaf dark:bg-white/10 dark:text-meadow">{{ __('home.account.addresses.default_badge') }}</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm leading-6 text-cocoa/65 dark:text-cream/65">
                                    {{ $address['street_line_1'] }}, {{ $address['postal_code'] }} {{ $address['city'] }} &middot; {{ $address['country_code'] }}
                                </p>
                            </div>
                            <span class="rounded-full border border-leaf/15 px-3 py-1 text-xs font-bold uppercase tracking-wide text-cocoa/70 dark:border-white/10 dark:text-cream/70">
                                {{ __('home.account.addresses.' . $address['type']) }}
                            </span>
                        </div>
                    </summary>

                    <div class="mt-5 border-t border-leaf/10 pt-5 dark:border-white/10">
                        <form wire:submit.prevent="updateAddress({{ (int) $address['id'] }})" class="space-y-4">
                            @include('livewire.account.partials.address-fields', ['model' => 'addressForms.' . $address['id'], 'prefix' => 'address-' . $address['id']])
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="updateAddress({{ (int) $address['id'] }})">
                                    {{ __('home.account.addresses.update') }}
                                </button>
                                <button type="button" wire:click="deleteAddress({{ (int) $address['id'] }})" wire:loading.attr="disabled" class="rounded-lg border border-leaf/20 px-5 py-3 text-sm font-bold text-cocoa transition hover:border-coral hover:text-coral disabled:opacity-60 dark:border-white/10 dark:text-cream">
                                    {{ __('home.account.addresses.delete') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </details>
            @empty
                <div class="rounded-lg border border-leaf/10 bg-white p-6 text-sm text-cocoa/65 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-cream/65">
                    {{ __('home.account.addresses.empty') }}
                </div>
            @endforelse
        </div>
    </section>
</div>
