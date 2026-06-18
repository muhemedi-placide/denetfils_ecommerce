<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($categories as $category)
        <a
            href="#products"
            wire:click.prevent="selectCategory('{{ $category['slug'] }}')"
            class="premium-card group relative overflow-hidden bg-white p-5 text-left dark:bg-white/5 sm:p-6"
        >
            <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-br from-sunshine/30 via-mango/20 to-caribbean/20 opacity-80"></div>
            <div class="relative flex items-start justify-between gap-4">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-sunshine/25 text-2xl font-black text-forest shadow-sm">{{ str($category['name'])->substr(0, 1) }}</span>
                <span class="rounded-full bg-cream px-3 py-1 text-xs font-black uppercase tracking-wide text-leaf dark:bg-white/10 dark:text-meadow">{{ __('home.categories.count', ['count' => $category['products_count']]) }}</span>
            </div>
            <div class="relative mt-8">
                <h3 class="brand-display text-2xl uppercase leading-none text-leaf dark:text-meadow">{{ $category['name'] }}</h3>
                <p class="mt-3 text-sm font-semibold leading-6 text-cocoa/65 dark:text-cream/65">{{ $locale === 'fr' ? 'Découvrez ce rayon et ajoutez vos essentiels au panier.' : 'Discover this aisle and add your essentials to the cart.' }}</p>
                <span class="mt-4 inline-flex text-sm font-black uppercase tracking-wide text-tomato transition group-hover:translate-x-1">{{ $locale === 'fr' ? 'Découvrir' : 'Explore' }}</span>
            </div>
        </a>
    @endforeach
</div>
