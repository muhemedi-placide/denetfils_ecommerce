<section id="products" class="theme-band-soft surface-transition bg-linen px-4 py-14 dark:bg-[#163319] sm:px-8 lg:py-20">
    @php
        $moodboardImages = [
            'https://moodboard-to-shop.lovable.app/assets/peppers-w9B84COi.jpg',
            'https://moodboard-to-shop.lovable.app/assets/product-epis-C1D47KI3.jpg',
            'https://moodboard-to-shop.lovable.app/assets/product-rice-BcMjvpU2.jpg',
            'https://moodboard-to-shop.lovable.app/assets/product-plantain-CnKnHz17.jpg',
            'https://moodboard-to-shop.lovable.app/assets/product-spices-DL1CC1Hd.jpg',
            'https://moodboard-to-shop.lovable.app/assets/leaves-D-dPOddf.jpg',
            'https://moodboard-to-shop.lovable.app/assets/hero-market-B__fWu60.jpg',
        ];
    @endphp
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div><p class="section-kicker">{{ __('home.products.eyebrow') }}</p><h2 class="section-title mt-3 max-w-4xl">{{ __('home.products.title') }}</h2></div>
            <p class="section-copy">{{ __('home.products.body') }}</p>
        </div>

        <button type="button" class="mb-3 flex min-h-[48px] w-full items-center justify-between rounded-2xl border border-leaf/10 bg-white px-4 py-3 text-sm font-black text-cocoa shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-cream md:hidden" wire:click="$toggle('filtersOpen')"><span>{{ __('home.filters.apply') }}</span><span class="text-forest">{{ $filtersOpen ? '−' : '+' }}</span></button>

        <form wire:submit.prevent="applyFilters" class="{{ $filtersOpen ? 'grid' : 'hidden md:grid' }} glass-panel gap-3 rounded-[1.5rem] p-3 md:grid-cols-[1fr_220px_180px_auto]">
            <input id="catalog-q" wire:model="q" placeholder="{{ __('home.filters.search_placeholder') }}" class="input-premium w-full">
            <select id="catalog-category" wire:model="category" class="input-premium w-full"><option value="">{{ __('home.filters.all_categories') }}</option>@foreach ($categories as $categoryItem)<option value="{{ $categoryItem['slug'] }}">{{ $categoryItem['name'] }} ({{ $categoryItem['products_count'] }})</option>@endforeach</select>
            <select id="catalog-sort" wire:model="sort" class="input-premium w-full">@foreach (trans('home.filters.sort_options') as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
            <div class="grid gap-2 sm:flex"><button type="submit" class="btn-primary w-full sm:w-auto" wire:loading.attr="disabled" wire:target="applyFilters">{{ __('home.filters.apply') }}</button>@if ($hasActiveFilters)<button type="button" wire:click="resetFilters" class="btn-secondary w-full px-4 sm:w-auto" wire:loading.attr="disabled">{{ __('home.filters.reset') }}</button>@endif</div>
        </form>

        <div wire:loading.flex wire:target="applyFilters,resetFilters,filterCategory,searchFromHeader" class="mt-6 rounded-2xl border border-leaf/10 bg-white px-5 py-4 text-sm font-semibold text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70">{{ __('home.cart.loading') }}</div>
        @if ($apiError)<div class="mt-6 rounded-2xl border border-leaf/20 bg-mint px-5 py-4 text-sm font-semibold text-leaf dark:bg-white/5">{{ $apiError }}</div>@endif

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($products as $product)
                @php
                    $ratingValue = number_format((float) data_get($product, 'commerce.rating.average', 4.8), 1, ',', ' ');
                    $reviewCount = (int) data_get($product, 'commerce.rating.count', 0);
                    $isAvailable = (bool) data_get($product, 'commerce.is_available', ((int) ($product['stock_quantity'] ?? 0)) > 0);
                    $imageUrl = $moodboardImages[$loop->index % count($moodboardImages)];
                @endphp
                <article class="market-card group overflow-hidden bg-white dark:bg-white/5" wire:key="product-card-{{ $product['id'] }}">
                    <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="relative block overflow-hidden bg-cream" wire:navigate>
                        <img class="product-card-image" src="{{ $imageUrl }}" alt="{{ $product['name'] }}" width="600" height="450" loading="lazy" decoding="async">
                        <div class="absolute left-4 top-4 flex flex-wrap gap-2"><span class="badge-tropical bg-forest text-cream">{{ $locale === 'fr' ? 'Sélection' : 'Selection' }}</span>@if (! empty($product['origin']))<span class="badge-tropical bg-cream/95 text-forest shadow-sm backdrop-blur">{{ $product['origin'] }}</span>@endif</div>
                    </a>
                    <div class="p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-coral">{{ $product['category']['name'] ?? ($locale === 'fr' ? 'Épicerie tropicale' : 'Tropical grocery') }}</p>
                        <h3 class="mt-2 min-w-0 text-2xl font-black leading-tight text-forest"><a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="line-clamp-2 transition hover:text-leaf" wire:navigate>{{ $product['name'] }}</a></h3>
                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-cocoa/65 dark:text-cream/68">{{ $product['short_description'] ?? $product['description'] }}</p>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-xs font-bold text-cocoa/55 dark:text-cream/55"><span class="text-sunshine">★★★★★</span><span>{{ $ratingValue }} · {{ $reviewCount }} {{ $locale === 'fr' ? 'avis' : 'reviews' }}</span></div>
                        <div class="mt-3 flex items-center gap-2 text-xs font-black uppercase tracking-wide {{ $isAvailable ? 'text-forest dark:text-meadow' : 'text-coral' }}"><span class="h-2 w-2 rounded-full {{ $isAvailable ? 'bg-forest dark:bg-meadow' : 'bg-coral' }}"></span><span>{{ $isAvailable ? __('home.products.stock_label', ['count' => $product['stock_quantity']]) : ($locale === 'fr' ? 'Rupture de stock' : 'Out of stock') }}</span></div>
                        <div class="mt-5 grid gap-3 sm:flex sm:items-center sm:justify-between"><span class="brand-display text-3xl text-forest">{{ $product['formatted_price'] }}</span><div class="flex gap-2"><button class="btn-primary px-4 py-2.5 text-xs disabled:pointer-events-none disabled:opacity-50" type="button" data-testid="product-add-button" x-on:click="window.dispatchEvent(new CustomEvent('cart-opening'))" wire:click="addToCart({{ (int) $product['id'] }})" wire:loading.attr="disabled" @disabled(! $isAvailable)>{{ __('home.products.cta') }}</button><a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="btn-secondary px-4 py-2.5 text-xs" wire:navigate>{{ $locale === 'fr' ? 'Voir' : 'View' }}</a></div></div>
                    </div>
                </article>
            @empty
                <div class="utility-section text-sm text-cocoa/70 dark:text-cream/70 md:col-span-2 lg:col-span-3">{{ __('home.products.empty') }}</div>
            @endforelse
        </div>
    </div>

    @script
        <script>
            $wire.on('catalog-updated', () => { document.getElementById('products')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
            $wire.on('scroll-to-products', () => { document.getElementById('products')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
        </script>
    @endscript
</section>
