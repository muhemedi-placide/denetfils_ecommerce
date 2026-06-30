<div>
    <button type="button" data-testid="header-cart-open-button" class="store-icon-button relative" x-on:click="window.dispatchEvent(new CustomEvent('cart-opening'))" wire:click="open">
        <x-icon name="cart" class="h-5 w-5" />
        <span class="sr-only">{{ __('home.cart.title') }}</span>
        <span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-[#f97316] px-1 text-[10px] font-bold text-white">{{ $this->itemCount() }}</span>
    </button>

    @teleport('body')
        <div x-data="{ open: @entangle('isOpen').live }" x-on:cart-opening.window="open = true" x-on:keydown.escape.window="open = false; $wire.close()">
            <div x-cloak x-show="open" class="pointer-events-none fixed inset-x-3 bottom-20 z-[80] flex justify-end sm:inset-y-0 sm:bottom-auto sm:left-auto sm:right-0 sm:w-[30rem]" role="dialog" data-testid="cart-drawer" aria-label="{{ __('home.cart.title') }}">
                <aside x-show="open" x-transition class="pointer-events-auto relative z-10 flex max-h-[calc(100svh-6.5rem)] min-h-0 w-full flex-col overflow-hidden rounded-[1.5rem] border border-leaf/10 bg-cream shadow-2xl dark:border-white/10 dark:bg-ink sm:h-svh sm:max-h-none sm:max-w-[30rem] sm:rounded-none sm:border-y-0 sm:border-r-0">
                    <div class="shrink-0 border-b border-leaf/10 bg-cream px-5 py-5 dark:border-white/10 dark:bg-ink">
                        <div class="flex items-start justify-between gap-4">
                            <div><p class="section-kicker">{{ $locale === 'fr' ? 'Panier rapide' : 'Quick cart' }}</p><h2 class="mt-2 text-2xl font-black text-forest dark:text-meadow">{{ __('home.cart.title') }}</h2></div>
                            <button type="button" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-cocoa transition hover:bg-mint hover:text-forest dark:text-cream" x-on:click="open = false" wire:click="close"><span class="sr-only">{{ __('home.cart.close') }}</span><span aria-hidden="true" class="text-2xl">&times;</span></button>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto bg-linen px-4 py-4 dark:bg-[#111111] sm:px-5">
                        <div wire:loading.flex class="mb-4 items-center gap-3 rounded-[1rem] border border-leaf/10 bg-white px-4 py-3 text-sm font-semibold text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70">{{ __('home.cart.loading') }}</div>
                        @if ($cartError)<div class="mb-4 rounded-[1rem] border border-coral/25 bg-coral/10 px-4 py-3 text-sm font-semibold text-cocoa dark:text-cream">{{ $cartError }}</div>@endif
                        @if (! $cartLoading && count($this->cartItems()) === 0)
                            <div class="rounded-[1.25rem] border border-dashed border-leaf/20 bg-white p-6 text-center dark:border-white/10 dark:bg-white/5">
                                <p class="text-2xl font-black text-forest dark:text-meadow">{{ __('home.cart.empty') }}</p>
                                <p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-cocoa/65 dark:text-cream/65">{{ $locale === 'fr' ? 'Ajoutez un produit pour retrouver ici les quantités, le total et le passage commande.' : 'Add a product to see quantities, total and checkout here.' }}</p>
                                <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="btn-secondary mt-5 w-full" x-on:click="open = false" wire:navigate.hover>{{ __('home.hero.primary_cta') }}</a>
                            </div>
                        @endif
                        <div class="space-y-3" data-testid="cart-drawer-items">
                            @foreach ($this->cartItems() as $item)
                                @php($imageUrl = data_get($item, 'product.image.url'))
                                <article data-testid="cart-drawer-item" class="grid grid-cols-[78px_1fr] gap-3 rounded-[1.15rem] border border-leaf/10 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-white/5" wire:key="drawer-cart-item-{{ $item['id'] }}">
                                    @if ($imageUrl)<img class="h-[78px] w-[78px] rounded-[0.9rem] object-cover" src="{{ $imageUrl }}" alt="{{ data_get($item, 'product.image.alt_text', data_get($item, 'product.name')) }}" loading="lazy" decoding="async" width="78" height="78">@else<div class="grid h-[78px] w-[78px] place-items-center rounded-[0.9rem] bg-sunshine/35 text-xs font-black text-forest">MP</div>@endif
                                    <div class="min-w-0"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><h3 class="line-clamp-2 text-sm font-black leading-snug text-cocoa dark:text-cream">{{ data_get($item, 'product.name') }}</h3><p class="mt-1 truncate text-xs text-cocoa/60 dark:text-cream/60">{{ data_get($item, 'variant.name') ?: data_get($item, 'product.origin') }}</p></div><button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-lg leading-none text-cocoa/60 transition hover:bg-mint hover:text-forest" wire:click="removeItem({{ (int) $item['id'] }})" wire:loading.attr="disabled" aria-label="{{ __('home.cart.remove') }}">&times;</button></div><div class="mt-3 flex flex-wrap items-center justify-between gap-3"><div class="grid h-10 grid-cols-[38px_44px_38px] overflow-hidden rounded-full border border-leaf/20 bg-mint/60 text-center"><button type="button" class="text-sm font-bold disabled:opacity-40" wire:click="decrementItem({{ (int) $item['id'] }}, {{ (int) $item['quantity'] }})" @disabled((int) $item['quantity'] <= 1)>&minus;</button><input class="w-full bg-transparent text-center text-sm font-bold outline-none" type="number" min="1" value="{{ (int) $item['quantity'] }}" wire:change="updateItem({{ (int) $item['id'] }}, $event.target.value)"><button type="button" class="text-sm font-bold" wire:click="incrementItem({{ (int) $item['id'] }}, {{ (int) $item['quantity'] }})">+</button></div><span class="text-sm font-black text-forest dark:text-meadow">{{ $item['formatted_line_total'] }}</span></div></div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="safe-bottom shrink-0 border-t border-leaf/10 bg-cream p-5 dark:border-white/10 dark:bg-ink">
                        <div class="rounded-[1rem] bg-white p-4 text-sm dark:bg-white/5"><div class="flex items-center justify-between"><span class="font-black text-cocoa dark:text-cream">{{ __('home.cart.total') }}</span><strong class="text-xl font-black text-forest dark:text-meadow">{{ $this->formattedTotal() }}</strong></div></div>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2"><a href="{{ route('shop.index', ['locale' => $locale]) }}" class="btn-secondary w-full px-4" x-on:click="open = false" wire:navigate.hover>{{ $locale === 'fr' ? 'Continuer' : 'Continue' }}</a><a href="{{ route('checkout.show', ['locale' => $locale]) }}" data-testid="cart-drawer-checkout-link" class="btn-primary w-full px-4 {{ count($this->cartItems()) === 0 ? 'pointer-events-none opacity-50' : '' }}" x-on:click="open = false" wire:navigate.hover>{{ __('home.cart.checkout_later') }}</a></div>
                        <p class="mt-3 text-center text-[11px] font-semibold text-cocoa/50 dark:text-cream/50">{{ $locale === 'fr' ? 'Paiement sécurisé : Visa, Mastercard, Apple Pay, Google Pay, PayPal.' : 'Secure payment: Visa, Mastercard, Apple Pay, Google Pay, PayPal.' }}</p>
                    </div>
                </aside>
            </div>
        </div>
    @endteleport

    @teleport('body')
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-leaf/10 bg-cream/95 px-4 py-3 shadow-[0_-12px_30px_rgba(0,0,0,0.08)] backdrop-blur dark:border-white/10 dark:bg-ink/95 lg:hidden"><div class="mx-auto grid max-w-md grid-cols-2 gap-3"><a href="{{ route('shop.index', ['locale' => $locale]) }}" class="btn-primary min-h-[46px] px-4 py-3 text-xs" wire:navigate.hover>{{ __('home.nav.shop') }}</a><button type="button" data-testid="mobile-cart-open-button" class="btn-secondary min-h-[46px] px-4 py-3 text-xs" x-on:click="window.dispatchEvent(new CustomEvent('cart-opening'))" wire:click="open">{{ __('home.cart.title') }}<span class="ml-2 rounded-full bg-sunshine px-2 py-0.5 text-xs text-forest">{{ $this->itemCount() }}</span></button></div></div>
    @endteleport

    @script
        <script>
            (() => {
                const cartStorageKey = 'marche_peyi_cart_token';
                $wire.restoreFromBrowser(localStorage.getItem(cartStorageKey));
                const payload = (event) => Array.isArray(event) ? (event[0] || {}) : (event || {});
                $wire.on('cart-token-stored', (event) => { const detail = payload(event); if (detail.token) { localStorage.setItem(cartStorageKey, detail.token); } });
                $wire.on('cart-token-cleared', () => { localStorage.removeItem(cartStorageKey); });
            })();
        </script>
    @endscript
</div>
