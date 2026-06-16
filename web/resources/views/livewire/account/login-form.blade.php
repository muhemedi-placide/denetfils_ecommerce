<div class="rounded-[1.25rem] border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-7">
    @if (session('status'))
        <div class="mb-5 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-semibold text-leaf dark:border-white/10 dark:bg-white/10 dark:text-meadow">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-terracotta/25 bg-terracotta/10 px-4 py-3 text-sm text-cocoa dark:text-cream">
            {{ $errors->first() }}
        </div>
    @endif

    <form wire:submit.prevent="login" class="space-y-5">
        <div>
            <label for="email" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.email') }}</label>
            <input id="email" wire:model="email" type="email" autocomplete="email" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
        </div>

        <div>
            <label for="password" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.password') }}</label>
            <input id="password" wire:model="password" type="password" autocomplete="current-password" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
        </div>

        <button type="submit" class="w-full rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay disabled:cursor-not-allowed disabled:opacity-60" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login">{{ __('home.account.auth.sign_in') }}</span>
            <span wire:loading wire:target="login">{{ __('home.cart.loading') }}</span>
        </button>
    </form>

    <div class="mt-6 flex flex-col gap-2 border-t border-leaf/10 pt-5 text-sm text-cocoa/70 dark:border-white/10 dark:text-cream/70 sm:flex-row sm:items-center sm:justify-between">
        <span>{{ __('home.account.auth.no_account') }}</span>
        <a wire:navigate class="font-bold text-leaf transition hover:text-forest dark:text-meadow" href="{{ route('account.register', ['locale' => $locale]) }}">{{ __('home.account.auth.go_register') }}</a>
    </div>
</div>
