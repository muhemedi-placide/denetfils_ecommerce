@php
    $ratingLabel = number_format($ratingAverage, 1, ',', ' ');
@endphp

<aside class="glass-panel mt-6 rounded-[1.35rem] p-4 sm:rounded-[1.6rem] sm:p-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="theme-subtle text-xs font-bold uppercase tracking-[0.2em] text-leaf dark:text-cream/60">{{ __('home.product.price') }}</p>
            <p class="theme-title mt-2 text-3xl font-extrabold text-forest dark:text-cream sm:text-4xl">{{ $product['formatted_price'] }}</p>
            <p class="mt-2 text-xs font-bold text-cocoa/55 dark:text-cream/55">
                <span class="text-leaf dark:text-meadow">*****</span>
                <span class="ml-1">{{ $ratingLabel }} &middot; {{ $ratingCount }} {{ $locale === 'fr' ? 'avis' : 'reviews' }}</span>
            </p>
        </div>
        <span class="rounded-full bg-mint px-3 py-2 text-xs font-bold text-leaf dark:bg-white/10 dark:text-cream">
            {{ $isAvailable ? __('home.product.available', ['count' => $product['stock_quantity']]) : ($locale === 'fr' ? 'Rupture de stock' : 'Out of stock') }}
        </span>
    </div>

    @if (! empty($product['variants']))
        <label class="theme-title mt-5 block text-sm font-bold text-cocoa dark:text-cream" for="variant">{{ __('home.product.variant') }}</label>
        <select id="variant" class="input-premium mt-2 w-full" wire:model="variantId">
            @foreach ($product['variants'] as $variant)
                <option value="{{ $variant['id'] }}">{{ $variant['name'] }} - {{ $variant['formatted_price'] }}</option>
            @endforeach
        </select>
    @endif

    <button type="button" data-testid="product-detail-add-button" class="btn-primary mt-5 w-full py-4 text-base disabled:pointer-events-none disabled:opacity-50" x-on:click="window.dispatchEvent(new CustomEvent('cart-opening'))" wire:click="addToCart" wire:loading.attr="disabled" @disabled(! $isAvailable)>
        <span wire:loading.remove wire:target="addToCart">{{ __('home.products.cta') }}</span>
        <span wire:loading wire:target="addToCart">{{ __('home.cart.loading') }}</span>
    </button>
    <a href="{{ route('home.localized', ['locale' => $locale]) }}#products" class="btn-secondary mt-3 w-full" wire:navigate>{{ __('home.product.back') }}</a>

    <p class="theme-muted mt-4 text-center text-xs leading-5 text-cocoa/60 dark:text-cream/60">
        {{ data_get($shipping, 'dispatch_time', __('home.product.shipping_note')) }}
    </p>
</aside>
