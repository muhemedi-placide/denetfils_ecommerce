<div class="form-card">
    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-coral/25 bg-coral/10 px-4 py-3 text-sm text-cocoa dark:text-cream">
            {{ $errors->first() }}
        </div>
    @endif

    <form wire:submit.prevent="register" class="space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="first_name" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.first_name') }}</label>
                <input id="first_name" wire:model="first_name" autocomplete="given-name" required class="input-premium mt-2 w-full">
            </div>
            <div>
                <label for="last_name" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.last_name') }}</label>
                <input id="last_name" wire:model="last_name" autocomplete="family-name" required class="input-premium mt-2 w-full">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="email" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.email') }}</label>
                <input id="email" wire:model="email" type="email" autocomplete="email" required class="input-premium mt-2 w-full">
            </div>
            <div>
                <label for="phone" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.phone') }}</label>
                <input id="phone" wire:model="phone" autocomplete="tel" class="input-premium mt-2 w-full">
            </div>
        </div>

        <div>
            <label for="country_code" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.country') }}</label>
            <select id="country_code" wire:model="country_code" required class="input-premium mt-2 w-full">
                @foreach ($countries as $country)
                    <option value="{{ $country['code'] }}">{{ $country['name'] }} &middot; {{ $country['currency'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.password') }}</label>
                <input id="password" wire:model="password" type="password" autocomplete="new-password" required class="input-premium mt-2 w-full">
            </div>
            <div>
                <label for="password_confirmation" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.auth.password_confirmation') }}</label>
                <input id="password_confirmation" wire:model="password_confirmation" type="password" autocomplete="new-password" required class="input-premium mt-2 w-full">
            </div>
        </div>

        <div class="space-y-3 rounded-2xl border border-leaf/10 bg-mint/50 p-4 text-sm text-cocoa/75 dark:border-white/10 dark:bg-white/5 dark:text-cream/75">
            <label class="flex gap-3">
                <input type="checkbox" wire:model="privacy_policy_consent" class="mt-1">
                <span>{{ __('home.account.auth.privacy_consent') }}</span>
            </label>
            <label class="flex gap-3">
                <input type="checkbox" wire:model="terms_consent" class="mt-1">
                <span>{{ __('home.account.auth.terms_consent') }}</span>
            </label>
            <label class="flex gap-3">
                <input type="checkbox" wire:model="marketing_consent" class="mt-1">
                <span>{{ __('home.account.auth.marketing_consent') }}</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="register">
            <span wire:loading.remove wire:target="register">{{ __('home.account.auth.create_account') }}</span>
            <span wire:loading wire:target="register">{{ __('home.cart.loading') }}</span>
        </button>
    </form>

    <div class="mt-6 flex flex-col gap-2 border-t border-leaf/10 pt-5 text-sm text-cocoa/70 dark:border-white/10 dark:text-cream/70 sm:flex-row sm:items-center sm:justify-between">
        <span>{{ __('home.account.auth.has_account') }}</span>
        <a wire:navigate class="font-bold text-leaf transition hover:text-forest dark:text-meadow" href="{{ route('account.login', ['locale' => $locale]) }}">{{ __('home.account.auth.go_login') }}</a>
    </div>
</div>
