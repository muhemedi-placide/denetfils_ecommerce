<button type="button" data-testid="cart-open-button" x-on:click="window.dispatchEvent(new CustomEvent('cart-opening'))" wire:click="$dispatchTo('shop.cart-manager', 'cart:open')" class="{{ $buttonClass }}">
    {{ __('home.cart.title') }}
</button>
