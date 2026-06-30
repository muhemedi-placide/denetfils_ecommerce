@php
    $ratingLabel = number_format($ratingAverage, 1, ',', ' ');
@endphp

<div class="mt-8">
    @if (! empty($product['variants']))
        <label class="mb-2 block text-xs font-black uppercase tracking-[0.22em] text-forest/45 dark:text-cream/50" for="variant">{{ __('home.product.variant') }}</label>
        <select id="variant" class="min-h-[50px] w-full rounded-full border-2 border-forest/20 bg-cream px-5 text-sm font-black text-forest outline-none transition focus:border-forest dark:bg-ink dark:text-cream" wire:model="variantId">
            @foreach ($product['variants'] as $variant)
                <option value="{{ $variant['id'] }}">{{ $variant['name'] }} - {{ $variant['formatted_price'] }}</option>
            @endforeach
        </select>
    @endif

    <p class="text-xs font-black uppercase tracking-[0.22em] text-forest/35 dark:text-cream/45">{{ __('home.product.price') }}</p>
    <p class="mt-1 text-5xl font-black tracking-tight text-forest dark:text-meadow">{{ $product['formatted_price'] }}</p>

    <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="grid h-[54px] w-full grid-cols-[54px_64px_54px] overflow-hidden rounded-full border-2 border-forest bg-cream text-center text-forest dark:bg-ink dark:text-cream sm:w-auto">
            <button type="button" class="text-xl font-black transition hover:bg-mint dark:hover:bg-white/10" wire:click="decrementQuantity" @disabled($quantity <= 1)>-</button>
            <input class="w-full bg-transparent text-center text-base font-black outline-none" type="number" min="1" wire:model.live="quantity">
            <button type="button" class="text-xl font-black transition hover:bg-mint dark:hover:bg-white/10" wire:click="incrementQuantity">+</button>
        </div>
        <button type="button" data-testid="product-detail-add-button" class="min-h-[54px] w-full rounded-full bg-forest px-8 py-4 text-sm font-black uppercase tracking-wide text-cream transition hover:bg-leaf disabled:pointer-events-none disabled:opacity-50 sm:w-auto" wire:click="addToCart" wire:loading.attr="disabled" @disabled(! $isAvailable)>
            <span wire:loading.remove wire:target="addToCart">{{ $locale === 'fr' ? 'Ajouter au panier' : 'Add to cart' }}</span>
            <span wire:loading wire:target="addToCart">{{ __('home.cart.loading') }}</span>
        </button>
    </div>

    <div class="mt-8 grid gap-4 border-t border-forest/10 pt-6 text-sm font-semibold text-forest/70 dark:border-white/10 dark:text-cream/70 sm:grid-cols-3">
        <span class="inline-flex items-center gap-2"><span class="text-coral">+</span>{{ $locale === 'fr' ? 'Livraison 48h' : '48h delivery' }}</span>
        <span class="inline-flex items-center gap-2"><span class="text-coral">+</span>{{ $locale === 'fr' ? 'Paiement securise' : 'Secure payment' }}</span>
        <span class="inline-flex items-center gap-2"><span class="text-coral">+</span>{{ $locale === 'fr' ? 'Source authentique' : 'Authentic source' }}</span>
    </div>
</div>
