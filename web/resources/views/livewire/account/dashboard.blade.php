<div>
    @if ($statusMessage)
        <div class="mt-8 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-leaf dark:border-white/10 dark:bg-white/10 dark:text-meadow">
            {{ $statusMessage }}
        </div>
    @elseif (session('status'))
        <div class="mt-8 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-leaf dark:border-white/10 dark:bg-white/10 dark:text-meadow">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-8 rounded-xl border border-terracotta/25 bg-terracotta/10 px-4 py-3 text-sm text-cocoa dark:text-cream">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <aside class="space-y-4">
            <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-sm font-bold uppercase tracking-wide text-leaf dark:text-meadow">{{ __('home.account.profile.status') }}</p>
                <p class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $user['status'] ?? 'active' }}</p>
                <p class="mt-1 text-sm text-cocoa/60 dark:text-cream/60">{{ $user['email'] ?? '' }}</p>
            </div>
            <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-sm font-bold uppercase tracking-wide text-leaf dark:text-meadow">{{ __('home.account.profile.role') }}</p>
                <p class="mt-2 text-lg font-extrabold text-cocoa dark:text-cream">{{ $roles ?: 'customer' }}</p>
            </div>
            <button type="button" wire:click="logout" wire:loading.attr="disabled" class="w-full rounded-full border border-leaf/20 bg-white px-5 py-3 text-sm font-bold uppercase tracking-wide text-cocoa transition hover:border-leaf hover:text-leaf disabled:opacity-60 dark:border-white/10 dark:bg-white/5 dark:text-cream dark:hover:border-meadow dark:hover:text-meadow">
                {{ __('home.account.profile.logout') }}
            </button>
        </aside>

        <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-7">
            <h2 class="text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.profile.section_title') }}</h2>
            <form wire:submit.prevent="updateProfile" class="mt-6 space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="profile-first-name" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.first_name') }}</label>
                        <input id="profile-first-name" wire:model="profile.first_name" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                    </div>
                    <div>
                        <label for="profile-last-name" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.last_name') }}</label>
                        <input id="profile-last-name" wire:model="profile.last_name" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="profile-phone" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.phone') }}</label>
                        <input id="profile-phone" wire:model="profile.phone" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                    </div>
                    <div>
                        <label for="profile-country" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.country') }}</label>
                        <select id="profile-country" wire:model="profile.country_code" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                            @foreach ($countries as $country)
                                <option value="{{ $country['code'] }}">{{ $country['name'] }} &middot; {{ $country['currency'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="profile-locale" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.profile.preferred_locale') }}</label>
                        <select id="profile-locale" wire:model="profile.preferred_locale" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                            <option value="fr">{{ __('home.locale.fr') }}</option>
                            <option value="en">{{ __('home.locale.en') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="profile-timezone" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.profile.timezone') }}</label>
                        <select id="profile-timezone" wire:model="profile.timezone" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}">{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay disabled:opacity-60" wire:loading.attr="disabled" wire:target="updateProfile">
                    <span wire:loading.remove wire:target="updateProfile">{{ __('home.account.profile.save') }}</span>
                    <span wire:loading wire:target="updateProfile">{{ __('home.cart.loading') }}</span>
                </button>
            </form>
        </div>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        <section class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-7">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.account.addresses.title') }}</p>
            <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.addresses.new_title') }}</h2>
            <p class="mt-3 text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ __('home.account.addresses.intro') }}</p>

            <form wire:submit.prevent="createAddress" class="mt-6 space-y-4">
                @include('livewire.account.partials.address-fields', ['model' => 'newAddress', 'prefix' => 'new'])
                <button type="submit" class="w-full rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay disabled:opacity-60" wire:loading.attr="disabled" wire:target="createAddress">
                    <span wire:loading.remove wire:target="createAddress">{{ __('home.account.addresses.save') }}</span>
                    <span wire:loading wire:target="createAddress">{{ __('home.cart.loading') }}</span>
                </button>
            </form>
        </section>

        <section class="space-y-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ __('home.account.addresses.title') }}</p>
                <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ __('home.account.addresses.title') }}</h2>
            </div>

            @forelse ($addresses as $address)
                <details class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5" @if ($loop->first) open @endif wire:key="address-{{ $address['id'] }}">
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
                            <button type="submit" class="rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay disabled:opacity-60" wire:loading.attr="disabled" wire:target="updateAddress({{ (int) $address['id'] }})">
                                {{ __('home.account.addresses.update') }}
                            </button>
                        </form>
                        <button type="button" wire:click="deleteAddress({{ (int) $address['id'] }})" wire:loading.attr="disabled" class="mt-3 rounded-full border border-leaf/20 px-5 py-2.5 text-sm font-bold text-cocoa transition hover:border-terracotta hover:text-terracotta disabled:opacity-60 dark:border-white/10 dark:text-cream">
                            {{ __('home.account.addresses.delete') }}
                        </button>
                    </div>
                </details>
            @empty
                <div class="rounded-[1.25rem] border border-leaf/10 bg-white p-6 text-sm text-cocoa/65 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-cream/65">
                    {{ __('home.account.addresses.empty') }}
                </div>
            @endforelse
        </section>
    </div>
</div>
