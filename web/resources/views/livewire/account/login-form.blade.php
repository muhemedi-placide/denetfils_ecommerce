<div class="form-card p-5 sm:p-6">
    @if (session('status'))
        <div class="mb-5 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-leaf dark:border-white/10 dark:bg-white/10 dark:text-meadow">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-coral/25 bg-coral/10 px-4 py-3 text-sm text-cocoa dark:text-cream">
            {{ $errors->first() }}
        </div>
    @endif

    <form wire:submit.prevent="login" class="space-y-5">
        <div>
            <label for="email" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.email') }}</label>
            <input id="email" wire:model="email" type="email" autocomplete="email" required class="input-premium mt-2 w-full">
        </div>

        <div>
            <label for="password" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.password') }}</label>
            <input id="password" wire:model="password" type="password" autocomplete="current-password" required class="input-premium mt-2 w-full">
        </div>

        <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login">{{ __('home.account.auth.sign_in') }}</span>
            <span wire:loading wire:target="login">{{ __('home.cart.loading') }}</span>
        </button>
    </form>
</div>
