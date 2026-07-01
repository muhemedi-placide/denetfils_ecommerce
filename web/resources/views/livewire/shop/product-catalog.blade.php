<section id="products" class="store-page py-10 lg:py-12">
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

    <div class="store-container">
        @unless ($isShopPage)
            <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div><p class="section-kicker">{{ __('home.products.eyebrow') }}</p><h2 class="section-title mt-3 max-w-4xl">{{ __('home.products.title') }}</h2></div>
                <p class="section-copy">{{ __('home.products.body') }}</p>
            </div>
        @endunless

        <div class="flex flex-col gap-4 border-b border-forest/10 pb-7 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="resetFilters" class="rounded-full px-5 py-2 text-xs font-bold uppercase tracking-wide transition {{ $category === '' ? 'bg-[#f97316] text-white' : 'border border-[#fcd9b8] hover:border-[#f97316] hover:text-[#f97316]' }}">
                    {{ $locale === 'fr' ? 'Tous' : 'All' }}
                </button>
                @foreach ($categories as $categoryItem)
                    <button type="button" wire:click="filterCategory('{{ $categoryItem['slug'] }}')" class="rounded-full px-5 py-2 text-xs font-bold uppercase tracking-wide transition {{ $category === $categoryItem['slug'] ? 'bg-[#f97316] text-white' : 'border border-[#fcd9b8] hover:border-[#f97316] hover:text-[#f97316]' }}">
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

        <div class="store-products-grid mt-8">
            @forelse ($products as $product)
                @php
                    $ratingValue = number_format((float) data_get($product, 'commerce.rating.average', 4.8), 1, ',', ' ');
                    $reviewCount = (int) data_get($product, 'commerce.rating.count', 0);
                    $isAvailable = (bool) data_get($product, 'commerce.is_available', ((int) ($product['stock_quantity'] ?? 0)) > 0);
                    $imageUrl = data_get($product, 'primary_image.url') ?: $moodboardImages[$loop->index % count($moodboardImages)];
                    $gallery = collect([$imageUrl])
                        ->merge(collect($product['images'] ?? [])->pluck('url'))
                        ->push($imageUrl.(str_contains($imageUrl, '?') ? '&' : '?').'view=detail&crop=left')
                        ->push($imageUrl.(str_contains($imageUrl, '?') ? '&' : '?').'view=detail&crop=right')
                        ->filter()
                        ->unique()
                        ->take(3)
                        ->values();
                    $displayPrice = preg_replace('/\bEUR\b/u', '€', (string) $product['formatted_price']);
                @endphp
                <article class="store-product-card group" wire:key="product-card-{{ $product['id'] }}">
                    <a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="store-card-image block" wire:navigate>
                        @foreach ($gallery as $galleryImage)
                            <img src="{{ $galleryImage }}" alt="{{ $product['name'] }}" width="700" height="560" loading="lazy" decoding="async">
                        @endforeach
                    </a>
                    <div class="store-card-body">
                        <span class="store-badge">{{ $product['category']['name'] ?? ($locale === 'fr' ? 'Épicerie' : 'Grocery') }}</span>
                        <h3 class="store-card-title"><a href="{{ route('products.show', ['locale' => $locale, 'slug' => $product['slug']]) }}" class="line-clamp-2" wire:navigate>{{ $product['name'] }}</a></h3>
                        <div class="mb-4 mt-auto"><strong class="store-price">{{ $displayPrice }}</strong></div>
                        <button class="store-button store-button-outline w-full disabled:pointer-events-none disabled:opacity-50" type="button" data-testid="product-add-button" wire:click="addToCart({{ (int) $product['id'] }})" wire:loading.attr="disabled" @disabled(! $isAvailable)><x-icon name="plus" class="h-4 w-4" /> {{ __('home.products.cta') }}</button>
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
