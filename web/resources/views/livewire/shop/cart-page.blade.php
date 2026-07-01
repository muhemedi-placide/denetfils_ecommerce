<div>
    <section class="store-page py-10 lg:py-14">
        <div class="store-container">
            @include('partials.checkout-progress', ['currentLocale' => $locale, 'currentStep' => 'cart'])

            @if ($recoveryMessage)
                <div class="mt-5 flex items-start gap-3 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-sm font-semibold text-neutral-900 dark:border-orange-500/30 dark:bg-orange-500/10 dark:text-white" role="status">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-orange-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg>
                    <span>{{ $recoveryMessage }}</span>
                </div>
            @endif

            <nav class="mobile-scrollbarless mt-5 flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm font-semibold text-neutral-500" aria-label="{{ $locale === 'fr' ? 'Fil d’Ariane' : 'Breadcrumb' }}">
                <a href="{{ route('home.localized', ['locale' => $locale]) }}" class="transition hover:text-orange-600" wire:navigate>{{ __('home.nav.home') }}</a>
                <span>/</span>
                <span class="text-neutral-950 dark:text-white">{{ __('home.cart.title') }}</span>
            </nav>

            <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start">
                <main>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-orange-600">{{ $locale === 'fr' ? 'Étape 1' : 'Step 1' }}</p>
                            <h1 class="mt-3 text-3xl font-black text-neutral-950 dark:text-white sm:text-4xl">{{ $locale === 'fr' ? 'Votre panier' : 'Your cart' }}</h1>
                            <p class="mt-3 text-sm leading-6 text-neutral-600 dark:text-neutral-300">{{ $locale === 'fr' ? 'Vérifiez les produits, les variantes, les quantités et les prix avant de continuer.' : 'Review products, variants, quantities and prices before continuing.' }}</p>
                        </div>
                        <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="btn-secondary w-full sm:w-auto" wire:navigate>{{ $locale === 'fr' ? 'Continuer les achats' : 'Continue shopping' }}</a>
                    </div>

                    <div wire:loading.flex class="mt-6 rounded-2xl border border-neutral-200 bg-white p-5 text-sm font-semibold dark:border-white/10 dark:bg-white/5">{{ __('home.cart.loading') }}</div>
                    @if ($cartError)
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm font-semibold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">{{ $cartError }}</div>
                    @endif

                    @if (! $cartLoading && count($this->cartItems()) === 0)
                        <div class="mt-6 rounded-2xl border border-neutral-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                            <h2 class="text-2xl font-black text-neutral-950 dark:text-white">{{ __('home.cart.empty') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-neutral-600 dark:text-neutral-300">{{ $locale === 'fr' ? 'Ajoutez vos produits préférés avant de passer à la commande.' : 'Add your favourite products before checking out.' }}</p>
                            <a href="{{ route('shop.index', ['locale' => $locale]) }}" class="btn-primary mt-5 w-full sm:w-auto" wire:navigate>{{ __('home.hero.primary_cta') }}</a>
                        </div>
                    @endif

                    @if (count($this->cartItems()) > 0)
                        <div class="mt-6 space-y-3">
                            @foreach ($this->cartItems() as $item)
                                @php
                                    $imageUrl = data_get($item, 'product.image.url');
                                    $stock = (int) (data_get($item, 'variant.stock_quantity') ?? data_get($item, 'product.stock_quantity', 0));
                                    $productUrl = route('products.show', ['locale' => $locale, 'slug' => data_get($item, 'product.slug')]);
                                @endphp
                                <article class="grid gap-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm transition hover:border-orange-300 dark:border-white/10 dark:bg-white/5 sm:grid-cols-[104px_1fr]" wire:key="cart-page-item-{{ $item['id'] }}">
                                    <a href="{{ $productUrl }}" wire:navigate>
                                        @if ($imageUrl)
                                            <img class="h-28 w-full rounded-xl object-cover sm:h-[104px] sm:w-[104px]" src="{{ $imageUrl }}" alt="{{ data_get($item, 'product.image.alt_text', data_get($item, 'product.name')) }}" width="104" height="104" loading="lazy" decoding="async">
                                        @else
                                            <div class="grid h-28 w-full place-items-center rounded-xl bg-orange-50 text-sm font-black text-orange-600 sm:h-[104px] sm:w-[104px]">MP</div>
                                        @endif
                                    </a>
                                    <div class="min-w-0">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <a href="{{ $productUrl }}" class="text-lg font-black text-neutral-950 transition hover:text-orange-600 dark:text-white" wire:navigate>{{ data_get($item, 'product.name') }}</a>
                                                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-neutral-500 dark:text-neutral-400">
                                                    <span>{{ $locale === 'fr' ? 'Réf.' : 'SKU' }} {{ data_get($item, 'variant.sku') ?: data_get($item, 'product.sku', '—') }}</span>
                                                    @if (data_get($item, 'variant.name'))<span>{{ data_get($item, 'variant.name') }}</span>@endif
                                                    @if (data_get($item, 'product.origin'))<span>{{ data_get($item, 'product.origin') }}</span>@endif
                                                    <span class="{{ $stock >= (int) $item['quantity'] ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600' }}">{{ $stock >= (int) $item['quantity'] ? ($locale === 'fr' ? 'En stock' : 'In stock') : ($locale === 'fr' ? 'Stock insuffisant' : 'Insufficient stock') }}</span>
                                                </div>
                                                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">{{ $item['formatted_unit_price'] }} × {{ (int) $item['quantity'] }}</p>
                                            </div>
                                            <button type="button" class="inline-flex min-h-10 items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-xs font-black uppercase tracking-wide text-neutral-600 transition hover:border-red-500 hover:text-red-600 disabled:opacity-60 dark:border-white/10 dark:text-neutral-300" wire:click="removeItem({{ (int) $item['id'] }})" wire:loading.attr="disabled">{{ __('home.cart.remove') }}</button>
                                        </div>
                                        <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                                            <div class="flex min-h-11 items-center rounded-full border border-neutral-200 bg-neutral-50 dark:border-white/10 dark:bg-white/5">
                                                <button type="button" class="px-4 py-2 text-sm font-bold disabled:opacity-40" wire:click="decrementItem({{ (int) $item['id'] }}, {{ (int) $item['quantity'] }})" @disabled((int) $item['quantity'] <= 1) aria-label="{{ $locale === 'fr' ? 'Réduire la quantité' : 'Decrease quantity' }}">−</button>
                                                <input class="w-14 bg-transparent text-center text-sm font-bold outline-none" type="number" min="1" max="{{ $stock }}" value="{{ (int) $item['quantity'] }}" wire:change="updateItem({{ (int) $item['id'] }}, $event.target.value)" aria-label="{{ $locale === 'fr' ? 'Quantité' : 'Quantity' }}">
                                                <button type="button" class="px-4 py-2 text-sm font-bold disabled:opacity-40" wire:click="incrementItem({{ (int) $item['id'] }}, {{ (int) $item['quantity'] }})" @disabled((int) $item['quantity'] >= $stock) aria-label="{{ $locale === 'fr' ? 'Augmenter la quantité' : 'Increase quantity' }}">+</button>
                                            </div>
                                            <strong class="text-xl font-black text-neutral-950 dark:text-white">{{ $item['formatted_line_total'] }}</strong>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </main>

                <aside class="space-y-4 lg:sticky lg:top-32">
                    <div class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-orange-600">{{ $locale === 'fr' ? 'Résumé' : 'Summary' }}</p>
                        <div class="mt-4 flex items-center justify-between gap-4">
                            <h2 class="text-xl font-black text-neutral-950 dark:text-white">{{ data_get($cart, 'reference', $locale === 'fr' ? 'Votre panier' : 'Your cart') }}</h2>
                            <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700 dark:bg-orange-500/15 dark:text-orange-300">{{ data_get($cart, 'items_count', $this->itemCount()) }} {{ $locale === 'fr' ? 'article(s)' : 'item(s)' }}</span>
                        </div>
                        <dl class="mt-5 space-y-3 border-y border-neutral-200 py-4 text-sm dark:border-white/10">
                            <div class="flex justify-between gap-4"><dt class="text-neutral-600 dark:text-neutral-300">{{ $locale === 'fr' ? 'Sous-total' : 'Subtotal' }}</dt><dd class="font-bold text-neutral-950 dark:text-white">{{ data_get($cart, 'formatted_subtotal', $this->formattedTotal()) }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-neutral-600 dark:text-neutral-300">{{ $locale === 'fr' ? 'TVA estimée' : 'Estimated VAT' }}</dt><dd class="font-bold text-neutral-950 dark:text-white">{{ data_get($estimate, 'formatted_tax', $locale === 'fr' ? 'À calculer' : 'To calculate') }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-neutral-600 dark:text-neutral-300">{{ $locale === 'fr' ? 'Livraison' : 'Delivery' }}</dt><dd class="text-right font-bold text-neutral-950 dark:text-white">{{ data_get($estimate, 'formatted_shipping_from', $locale === 'fr' ? 'À l’étape suivante' : 'At next step') }}</dd></div>
                            @if (data_get($cart, 'total_weight_grams'))
                                <div class="flex justify-between gap-4"><dt class="text-neutral-600 dark:text-neutral-300">{{ $locale === 'fr' ? 'Poids' : 'Weight' }}</dt><dd class="font-bold text-neutral-950 dark:text-white">{{ number_format((int) data_get($cart, 'total_weight_grams') / 1000, 2, ',', ' ') }} kg</dd></div>
                            @endif
                        </dl>
                        <div class="mt-4 flex items-end justify-between gap-4"><span class="font-black text-neutral-950 dark:text-white">{{ $locale === 'fr' ? 'Total estimé' : 'Estimated total' }}</span><strong class="text-2xl font-black text-neutral-950 dark:text-white">{{ data_get($estimate, 'formatted_total', $this->formattedTotal()) }}</strong></div>
                        <a href="{{ route('checkout.show', ['locale' => $locale]) }}" class="btn-primary mt-5 w-full {{ count($this->cartItems()) === 0 ? 'pointer-events-none opacity-50' : '' }}" wire:navigate>{{ $locale === 'fr' ? 'Continuer vers la livraison' : 'Continue to delivery' }}</a>
                    </div>

                    <div class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5" x-data="{ copied: false }">
                        <h2 class="font-black text-neutral-950 dark:text-white">{{ $locale === 'fr' ? 'Partager ou récupérer ce panier' : 'Share or recover this cart' }}</h2>
                        <p class="mt-2 text-sm leading-6 text-neutral-600 dark:text-neutral-300">{{ $locale === 'fr' ? 'Créez un lien sécurisé pour reprendre ce panier sur un autre appareil ou rappeler un panier abandonné.' : 'Create a secure link to resume this cart on another device or remind someone about an abandoned cart.' }}</p>
                        @if ($recoveryUrl)
                            <div class="mt-4 flex gap-2">
                                <input x-ref="recoveryUrl" class="min-w-0 flex-1 rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-2 text-xs text-neutral-700 outline-none dark:border-white/10 dark:bg-black/20 dark:text-neutral-200" value="{{ $recoveryUrl }}" readonly aria-label="{{ $locale === 'fr' ? 'Lien de récupération' : 'Recovery link' }}">
                                <button type="button" class="rounded-xl bg-neutral-950 px-4 py-2 text-xs font-black text-white transition hover:bg-orange-600 dark:bg-white dark:text-black" x-on:click="navigator.clipboard.writeText($refs.recoveryUrl.value); copied = true; setTimeout(() => copied = false, 1800)">
                                    <span x-show="!copied">{{ $locale === 'fr' ? 'Copier' : 'Copy' }}</span>
                                    <span x-cloak x-show="copied">{{ $locale === 'fr' ? 'Copié' : 'Copied' }}</span>
                                </button>
                            </div>
                            @if ($recoveryExpiresAt)<p class="mt-2 text-xs text-neutral-500">{{ $locale === 'fr' ? 'Lien valable jusqu’au' : 'Link valid until' }} {{ \Illuminate\Support\Carbon::parse($recoveryExpiresAt)->locale($locale)->translatedFormat('d M Y, H:i') }}</p>@endif
                        @else
                            <button type="button" class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-orange-500 px-4 py-2 text-sm font-black text-orange-600 transition hover:bg-orange-500 hover:text-white disabled:opacity-50" wire:click="createRecoveryLink" wire:loading.attr="disabled" @disabled(count($this->cartItems()) === 0)>
                                <span wire:loading.remove wire:target="createRecoveryLink">{{ $locale === 'fr' ? 'Créer le lien sécurisé' : 'Create secure link' }}</span>
                                <span wire:loading wire:target="createRecoveryLink">{{ $locale === 'fr' ? 'Création…' : 'Creating…' }}</span>
                            </button>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if (! empty($recommendedProducts))
        <section class="border-t border-neutral-200 bg-neutral-50 px-4 py-12 dark:border-white/10 dark:bg-neutral-950 sm:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-7 flex items-end justify-between gap-4"><div><p class="text-xs font-black uppercase tracking-[0.16em] text-orange-600">{{ $locale === 'fr' ? 'Compléter le panier' : 'Complete your cart' }}</p><h2 class="mt-2 text-3xl font-black text-neutral-950 dark:text-white">{{ __('home.product.related_title') }}</h2></div><a href="{{ route('shop.index', ['locale' => $locale]) }}" class="btn-secondary hidden sm:inline-flex" wire:navigate>{{ __('home.spotlight.cta') }}</a></div>
                <div class="mobile-scrollbarless flex gap-4 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:overflow-visible">
                    @foreach ($recommendedProducts as $product)
                        <article class="market-card group min-w-[260px] overflow-hidden bg-white dark:bg-white/5 lg:min-w-0" wire:key="recommended-{{ $product['id'] }}">
                            <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" wire:navigate>
                                @if (! empty($product['primary_image']['url']))<img class="h-48 w-full object-cover transition group-hover:scale-[1.04]" src="{{ $product['primary_image']['url'] }}" alt="{{ $product['primary_image']['alt_text'] ?? $product['name'] }}" loading="lazy" decoding="async">@else<div class="grid h-48 place-items-center bg-orange-50 text-orange-600">MP</div>@endif
                            </a>
                            <div class="p-5"><h3 class="line-clamp-2 text-xl font-black text-neutral-950 dark:text-white">{{ $product['name'] }}</h3><div class="mt-4 flex items-center justify-between gap-3"><span class="text-2xl font-black text-neutral-950 dark:text-white">{{ $product['formatted_price'] }}</span><button type="button" class="btn-primary px-4 py-2 text-xs" wire:click="addRecommended({{ (int) $product['id'] }})" wire:loading.attr="disabled">{{ __('home.products.cta') }}</button></div></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @script
        <script>
            (() => {
                const storageKey = 'marche_peyi_cart_token';
                const recoveredFromLink = @js($recoveredFromLink);

                if (recoveredFromLink) {
                    const recoveredToken = @js($cartToken);
                    if (recoveredToken) localStorage.setItem(storageKey, recoveredToken);
                } else {
                    $wire.restoreFromBrowser(localStorage.getItem(storageKey));
                }

                const payload = (event) => Array.isArray(event) ? (event[0] || {}) : (event || {});
                $wire.on('cart-token-stored', (event) => {
                    const detail = payload(event);
                    if (detail.token) localStorage.setItem(storageKey, detail.token);
                });
                $wire.on('cart-token-cleared', () => localStorage.removeItem(storageKey));
            })();
        </script>
    @endscript
</div>
