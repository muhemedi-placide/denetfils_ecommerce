<div class="form-card">
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

    <div class="mt-6 flex flex-col gap-2 border-t border-leaf/10 pt-5 text-sm text-cocoa/70 dark:border-white/10 dark:text-cream/70 sm:flex-row sm:items-center sm:justify-between">
        <span>{{ __('home.account.auth.no_account') }}</span>
        <a wire:navigate class="font-bold text-leaf transition hover:text-forest dark:text-meadow" href="{{ route('account.register', ['locale' => $locale]) }}">{{ __('home.account.auth.go_register') }}</a>
    </div>
</div>
