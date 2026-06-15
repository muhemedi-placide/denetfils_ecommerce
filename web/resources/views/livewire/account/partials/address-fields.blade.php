<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="{{ $prefix }}-type" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.type') }}</label>
        <select id="{{ $prefix }}-type" wire:model="{{ $model }}.type" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
            <option value="shipping">{{ __('home.account.addresses.shipping') }}</option>
            <option value="billing">{{ __('home.account.addresses.billing') }}</option>
        </select>
    </div>
    <div>
        <label for="{{ $prefix }}-label" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.label') }}</label>
        <input id="{{ $prefix }}-label" wire:model="{{ $model }}.label" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
    </div>
</div>

<div>
    <label for="{{ $prefix }}-recipient-name" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.recipient_name') }}</label>
    <input id="{{ $prefix }}-recipient-name" wire:model="{{ $model }}.recipient_name" required autocomplete="name" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
</div>

<div>
    <label for="{{ $prefix }}-company" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.company') }}</label>
    <input id="{{ $prefix }}-company" wire:model="{{ $model }}.company" autocomplete="organization" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
</div>

<div>
    <label for="{{ $prefix }}-street-line-1" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.street_line_1') }}</label>
    <input id="{{ $prefix }}-street-line-1" wire:model="{{ $model }}.street_line_1" required autocomplete="address-line1" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
</div>

<div>
    <label for="{{ $prefix }}-street-line-2" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.street_line_2') }}</label>
    <input id="{{ $prefix }}-street-line-2" wire:model="{{ $model }}.street_line_2" autocomplete="address-line2" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label for="{{ $prefix }}-postal-code" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.postal_code') }}</label>
        <input id="{{ $prefix }}-postal-code" wire:model="{{ $model }}.postal_code" required autocomplete="postal-code" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
    </div>
    <div>
        <label for="{{ $prefix }}-city" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.city') }}</label>
        <input id="{{ $prefix }}-city" wire:model="{{ $model }}.city" required autocomplete="address-level2" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
    </div>
    <div>
        <label for="{{ $prefix }}-region" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.region') }}</label>
        <input id="{{ $prefix }}-region" wire:model="{{ $model }}.region" autocomplete="address-level1" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="{{ $prefix }}-country-code" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.country_code') }}</label>
        <select id="{{ $prefix }}-country-code" wire:model="{{ $model }}.country_code" required autocomplete="country" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
            @foreach ($countries as $country)
                <option value="{{ $country['code'] }}">{{ $country['name'] }} &middot; {{ $country['currency'] }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="{{ $prefix }}-phone" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.phone') }}</label>
        <input id="{{ $prefix }}-phone" wire:model="{{ $model }}.phone" autocomplete="tel" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
    </div>
</div>

<label class="flex items-center gap-3 rounded-2xl border border-leaf/10 bg-mint/50 p-4 text-sm font-semibold text-cocoa/75 dark:border-white/10 dark:bg-white/5 dark:text-cream/75">
    <input type="checkbox" wire:model="{{ $model }}.is_default">
    <span>{{ __('home.account.addresses.is_default') }}</span>
</label>
