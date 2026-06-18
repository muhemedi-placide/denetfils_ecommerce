@php
    $ratingLabel = number_format($ratingAverage, 1, ',', ' ');
@endphp

<aside class="glass-panel mt-7 rounded-[1.5rem] p-5 sm:p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="section-kicker">{{ __('home.product.price') }}</p>
            <p class="brand-display mt-3 text-4xl text-forest dark:text-meadow sm:text-5xl">{{ $product['formatted_price'] }}</p>
            <p class="mt-3 text-xs font-bold text-cocoa/55 dark:text-cream/55">
                <span class="text-sunshine">★★★★★</span>
                <span class="ml-1">{{ $ratingLabel }} · {{ $ratingCount }} {{ $locale === 'fr' ? 'avis' : 'reviews' }}</span>
            </p>
        </div>
        <span class="rounded-full bg-mint px-3 py-2 text-xs font-black text-forest dark:bg-white/10 dark:text-meadow">
            {{ $isAvailable ? __('home.product.available', ['count' => $product['stock_quantity']]) : ($locale === 'fr' ? 'Rupture de stock' : 'Out of stock') }}
        </span>
    </div>

    @if (! empty($product['variants']))
        <label class="mt-6 block text-sm font-black text-cocoa dark:text-cream" for="variant">{{ __('home.product.variant') }}</label>
        <select id="variant" class="input-premium mt-2 w-full" wire:model="variantId">
            @foreach ($product['variants'] as $variant)
                <option value="{{ $variant['id'] }}">{{ $variant['name'] }} · {{ $variant['formatted_price'] }}</option>
            @endforeach
        </select>
    @endif

    <div class="mt-6 grid gap-2">
        <button type="button" data-testid="product-detail-add-button" class="btn-primary w-full py-4 text-base disabled:pointer-events-none disabled:opacity-50" x-on:click="window.dispatchEvent(new CustomEvent('cart-opening'))" wire:click="addToCart" wire:loading.attr="disabled" @disabled(! $isAvailable)>
            <span wire:loading.remove wire:target="addToCart" class="inline-flex items-center gap-2"><span>+</span>{{ __('home.products.cta') }}</span>
            <span wire:loading wire:target="addToCart">{{ __('home.cart.loading') }}</span>
        </button>
        <a href="{{ route('cart.show', ['locale' => $locale]) }}" class="btn-secondary w-full" wire:navigate>{{ $locale === 'fr' ? 'Voir mon panier' : 'View my cart' }}</a>
    </div>

    <div class="mt-5 grid gap-2 rounded-[1rem] bg-mint p-4 text-xs font-black uppercase tracking-wide text-forest dark:bg-white/5 dark:text-meadow sm:grid-cols-3">
        <span>{{ $locale === 'fr' ? 'Ajout instantané' : 'Instant add' }}</span>
        <span>{{ $locale === 'fr' ? 'Relais disponible' : 'Pickup ready' }}</span>
        <span>{{ $locale === 'fr' ? 'Paiement sécurisé' : 'Secure payment' }}</span>
    </div>

    <p class="mt-5 text-center text-xs font-semibold leading-5 text-cocoa/60 dark:text-cream/60">
        {{ data_get($shipping, 'dispatch_time', __('home.product.shipping_note')) }}
        {{ $locale === 'fr' ? 'Mondial Relay et Chrono Relais seront proposés à la commande.' : 'Mondial Relay and Chrono Relais will be available at checkout.' }}
    </p>
</aside>
