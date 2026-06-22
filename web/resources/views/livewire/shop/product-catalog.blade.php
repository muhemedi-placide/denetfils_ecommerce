<section id="products" class="bg-cream px-4 py-10 dark:bg-ink sm:px-8 lg:py-12">
    @php
        $moodboardImages = [
            asset('assets/products/product-pikliz.jpg'),
            asset('assets/products/product-epis.jpg'),
            asset('assets/products/product-rice.jpg'),
            asset('assets/products/product-plantain.jpg'),
            asset('assets/products/product-spices.jpg'),
            asset('assets/products/peppers.jpg'),
        ];
        $isShopPage = request()->routeIs('shop.index');
    @endphp

    <div class="mx-auto max-w-7xl">
        @unless ($isShopPage)
            <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div><p class="section-kicker">{{ __('home.products.eyebrow') }}</p><h2 class="section-title mt-3 max-w-4xl">{{ __('home.products.title') }}</h2></div>
                <p class="section-copy">{{ __('home.products.body') }}</p>
            </div>
        @endunless

        <div class="flex flex-col gap-4 border-b border-forest/10 pb-7 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="resetFilters" class="rounded-full px-5 py-2 text-xs font-black uppercase tracking-wide transition {{ $category === '' ? 'bg-forest text-cream' : 'bg-linen text-forest hover:bg-mint' }}">
                    {{ $locale === 'fr' ? 'Tous' : 'All' }}
                </button>
                @foreach ($categories as $categoryItem)
                    <button type="button" wire:click="filterCategory('{{ $categoryItem['slug'] }}')" class="rounded-full px-5 py-2 text-xs font-black uppercase tracking-wide transition {{ $category === $categoryItem['slug'] ? 'bg-forest text-cream' : 'bg-linen text-forest hover:bg-mint' }}">
                        {{ $categoryItem['name'] }}
                    </button>
                @endforeach
            </div>

            <select id="catalog-sort" wire:model="sort" wire:change="applyFilters" class="min-h-[42px] rounded-full border border-forest/20 bg-cream px-5 py-2 text-sm font-semibold text-forest outline-none transition focus:border-forest focus:ring-2 focus:ring-sunshine/60 dark:bg-white/5 dark:text-cream">
                @foreach (trans('home.filters.sort_options') as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div wire:loading.flex wire:target="applyFilters,resetFilters,filterCategory,searchFromHeader" class="mt-6 rounded-2xl border border-leaf/10 bg-white px-5 py-4 text-sm font-semibold text-cocoa/70 dark:border-white/10 dark:bg-white/5 dark:text-cream/70">{{ __('home.cart.loading') }}</div>
        @if ($apiError)<div class="mt-6 rounded-2xl border border-leaf/20 bg-mint px-5 py-4 text-sm font-semibold text-leaf dark:bg-white/5">{{ $apiError }}</div>@endif

        <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($products as $product)
                @php
                    $ratingValue = number_format((float) data_get($product, 'commerce.rating.average', 4.8), 1, ',', ' ');
                    $reviewCount = (int) data_get($product, 'commerce.rating.count', 0);
                    $isAvailable = (bool) data_get($product, 'commerce.is_available', ((int) ($product['stock_quantity'] ?? 0)) > 0);
                    $imageUrl = $moodboardImages[$loop->index % count($moodboardImages)];
                @endphp
                <article class="group overflow-hidden rounded-[1.6rem] border border-forest/10 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-tropical dark:border-white/10 dark:bg-white/5" wire:key="product-card-{{ $product['id'] }}">
                    <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="relative block overflow-hidden bg-sunshine" wire:navigate>
                        <img class="h-72 w-full object-cover transition duration-500 group-hover:scale-[1.04]" src="{{ $imageUrl }}" alt="{{ $product['name'] }}" width="700" height="560" loading="lazy" decoding="async">
                        <span class="absolute left-4 top-4 rounded-full bg-forest px-4 py-2 text-[11px] font-black uppercase tracking-wide text-cream">Best-seller</span>
                        <span class="absolute right-4 top-4 rounded-full bg-cream px-3 py-1 text-xs font-black text-coral">{{ $loop->iteration <= 2 ? 'HOT' : 'NEW' }}</span>
                    </a>
                    <div class="p-6">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-coral">{{ $product['category']['name'] ?? ($locale === 'fr' ? 'Epicerie' : 'Grocery') }} @if (! empty($product['origin'])) · {{ $product['origin'] }} @endif</p>
                        <h3 class="mt-2 text-2xl font-black leading-tight text-forest"><a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="line-clamp-2" wire:navigate>{{ $product['name'] }}</a></h3>
                        <p class="mt-1 line-clamp-1 text-sm font-semibold text-forest/65 dark:text-cream/70">{{ $product['short_description'] ?? $product['description'] }}</p>
                        <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-forest/65 dark:text-cream/70"><span class="text-sunshine">★★★★★</span><span>{{ $ratingValue }} · {{ $reviewCount }} {{ $locale === 'fr' ? 'avis' : 'reviews' }}</span></div>
                        <div class="mt-3 flex items-center gap-2 text-xs font-black uppercase tracking-wide {{ $isAvailable ? 'text-forest' : 'text-coral' }}"><span class="h-2 w-2 rounded-full {{ $isAvailable ? 'bg-forest' : 'bg-coral' }}"></span><span>{{ $isAvailable ? ($locale === 'fr' ? 'En stock' : 'In stock') : ($locale === 'fr' ? 'Rupture de stock' : 'Out of stock') }}</span></div>
                        <div class="mt-7 flex items-center justify-between gap-4">
                            <strong class="text-3xl font-black tracking-tight text-forest">{{ $product['formatted_price'] }}</strong>
                            <button class="rounded-full bg-forest px-5 py-3 text-xs font-black text-cream transition hover:bg-leaf disabled:pointer-events-none disabled:opacity-50" type="button" data-testid="product-add-button" x-on:click="window.dispatchEvent(new CustomEvent('cart-opening'))" wire:click="addToCart({{ (int) $product['id'] }})" wire:loading.attr="disabled" @disabled(! $isAvailable)>+ {{ __('home.products.cta') }}</button>
                        </div>
                        <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="mt-5 block text-center text-xs font-black uppercase tracking-[0.22em] text-forest/65 transition hover:text-forest" wire:navigate>
                            {{ $locale === 'fr' ? 'Voir le produit' : 'View product' }}
                        </a>
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
