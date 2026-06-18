<section id="products" class="theme-band-soft surface-transition bg-linen px-4 py-12 dark:bg-[#163319] sm:px-8 lg:py-16">
    <div class="mx-auto max-w-7xl">
        <div class="mb-7 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <p class="theme-subtle text-xs font-black uppercase tracking-[0.22em] text-tomato dark:text-sunshine">{{ __('home.products.eyebrow') }}</p>
                <h2 class="brand-display theme-title mt-2 max-w-3xl text-4xl uppercase leading-none text-leaf dark:text-meadow sm:text-5xl">{{ __('home.products.title') }}</h2>
            </div>
            <p class="theme-muted max-w-xl text-sm font-semibold leading-7 text-cocoa/70 dark:text-cream/70">{{ __('home.products.body') }}</p>
        </div>

        <button type="button" class="mb-3 flex min-h-[46px] w-full items-center justify-between rounded-2xl border border-leaf/10 bg-white px-4 py-3 text-sm font-black text-cocoa shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-cream md:hidden" wire:click="$toggle('filtersOpen')">
            <span>{{ __('home.filters.apply') }}</span>
            <span class="text-leaf">{{ $filtersOpen ? '-' : '+' }}</span>
        </button>

        <form wire:submit.prevent="applyFilters" class="{{ $filtersOpen ? 'grid' : 'hidden md:grid' }} glass-panel gap-3 rounded-[1.5rem] p-3 md:grid-cols-[1fr_220px_180px_auto]">
            <input id="catalog-q" wire:model="q" placeholder="{{ __('home.filters.search_placeholder') }}" class="input-premium w-full">
            <select id="catalog-category" wire:model="category" class="input-premium w-full">
                <option value="">{{ __('home.filters.all_categories') }}</option>
                @foreach ($categories as $categoryItem)
                    <option value="{{ $categoryItem['slug'] }}">{{ $categoryItem['name'] }} ({{ $categoryItem['products_count'] }})</option>
                @endforeach
            </select>
            <select id="catalog-sort" wire:model="sort" class="input-premium w-full">
                @foreach (trans('home.filters.sort_options') as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="grid gap-2 sm:flex">
                <button type="submit" class="btn-primary w-full sm:w-auto" wire:loading.attr="disabled" wire:target="applyFilters">{{ __('home.filters.apply') }}</button>
                @if ($hasActiveFilters)
                    <button type="button" wire:click="resetFilters" class="btn-secondary w-full px-4 sm:w-auto" wire:loading.attr="disabled">{{ __('home.filters.reset') }}</button>
                @endif
            </div>
        </form>

        <div wire:loading.flex wire:target="applyFilters,resetFilters,filterCategory,searchFromHeader" class="mt-6 rounded-2xl border border-leaf/10 bg-white px-5 py-4 text-sm font-semibold text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70">{{ __('home.cart.loading') }}</div>

        @if ($apiError)
            <div class="mt-6 rounded-2xl border border-leaf/20 bg-mint px-5 py-4 text-sm font-semibold text-leaf dark:bg-white/5">{{ $apiError }}</div>
        @endif

        <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($products as $product)
                @php
                    $ratingValue = number_format((float) data_get($product, 'commerce.rating.average', 4.8), 1, ',', ' ');
                    $reviewCount = (int) data_get($product, 'commerce.rating.count', 0);
                    $primaryImage = $product['primary_image'] ?? [];
                @endphp
                <article class="premium-card group overflow-hidden bg-cream dark:bg-white/5" wire:key="product-card-{{ $product['id'] }}">
                    <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="relative block overflow-hidden bg-white" wire:navigate>
                        <img class="h-52 w-full object-cover transition duration-500 group-hover:scale-[1.04] sm:h-60 lg:h-64" src="{{ $primaryImage['url'] ?? '' }}" alt="{{ $primaryImage['alt_text'] ?? $product['name'] }}" width="{{ $primaryImage['width'] ?? 600 }}" height="{{ $primaryImage['height'] ?? 450 }}" loading="{{ $primaryImage['loading'] ?? 'lazy' }}" decoding="async">
                        <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                            <span class="badge-tropical bg-sunshine text-forest">{{ $locale === 'fr' ? 'Best-seller' : 'Best seller' }}</span>
                            <span class="badge-tropical bg-white/95 text-leaf shadow-sm backdrop-blur">{{ $product['origin'] }}</span>
                        </div>
                    </a>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-leaf/70 dark:text-meadow/80">{{ $product['category']['name'] ?? ($locale === 'fr' ? 'Épicerie tropicale' : 'Tropical grocery') }}</p>
                                <h3 class="theme-title mt-1 min-w-0 text-lg font-black leading-snug text-cocoa dark:text-cream"><a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="line-clamp-2 transition hover:text-leaf" wire:navigate>{{ $product['name'] }}</a></h3>
                            </div>
                            <span class="shrink-0 rounded-full bg-mint px-2.5 py-1 text-[11px] font-black text-leaf dark:bg-white/10 dark:text-cream">{{ $product['stock_quantity'] }}</span>
                        </div>
                        <p class="theme-muted mt-2 line-clamp-2 text-sm leading-6 text-cocoa/70 dark:text-cream/70">{{ $product['short_description'] ?? $product['description'] }}</p>
                        <div class="mt-3 flex items-center justify-between gap-3 text-xs font-bold text-cocoa/55 dark:text-cream/55"><span class="text-sunshine">*****</span><span>{{ $ratingValue }} - {{ $reviewCount }} {{ $locale === 'fr' ? 'avis clients' : 'customer reviews' }}</span></div>
                        <div class="mt-5 grid gap-3 sm:flex sm:items-center sm:justify-between">
                            <span class="theme-title text-2xl font-black text-leaf dark:text-meadow">{{ $product['formatted_price'] }}</span>
                            <button class="btn-primary w-full px-4 py-2.5 disabled:opacity-60 sm:w-auto" type="button" data-testid="product-add-button" x-on:click="window.dispatchEvent(new CustomEvent('cart-opening'))" wire:click="$dispatchTo('shop.cart-manager', 'cart:add', { productId: {{ (int) $product['id'] }} })" wire:loading.attr="disabled">{{ __('home.products.cta') }}</button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="theme-card rounded-[1.5rem] border border-leaf/10 bg-white p-6 text-sm text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70 md:col-span-2 lg:col-span-3">{{ __('home.products.empty') }}</div>
            @endforelse
        </div>
    </div>

    @script
        <script>
            $wire.on('catalog-updated', () => {
                document.getElementById('products')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            $wire.on('scroll-to-products', () => {
                document.getElementById('products')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        </script>
    @endscript
</section>
