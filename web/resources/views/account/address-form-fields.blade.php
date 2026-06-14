@php
    $address = $address ?? [];
    $prefix = $prefix ?? 'address';
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="{{ $prefix }}-type" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.type') }}</label>
        <select id="{{ $prefix }}-type" name="type" required class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
            <option value="shipping" @selected(old('type', $address['type'] ?? 'shipping') === 'shipping')>{{ __('home.account.addresses.shipping') }}</option>
            <option value="billing" @selected(old('type', $address['type'] ?? 'shipping') === 'billing')>{{ __('home.account.addresses.billing') }}</option>
        </select>
    </div>
    <div>
        <label for="{{ $prefix }}-label" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.label') }}</label>
        <input id="{{ $prefix }}-label" name="label" value="{{ old('label', $address['label'] ?? '') }}" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
    </div>
</div>

<div>
    <label for="{{ $prefix }}-recipient-name" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.recipient_name') }}</label>
    <input id="{{ $prefix }}-recipient-name" name="recipient_name" value="{{ old('recipient_name', $address['recipient_name'] ?? '') }}" required autocomplete="name" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
</div>

<div>
    <label for="{{ $prefix }}-company" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.company') }}</label>
    <input id="{{ $prefix }}-company" name="company" value="{{ old('company', $address['company'] ?? '') }}" autocomplete="organization" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
</div>

<div>
    <label for="{{ $prefix }}-street-line-1" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.street_line_1') }}</label>
    <input id="{{ $prefix }}-street-line-1" name="street_line_1" value="{{ old('street_line_1', $address['street_line_1'] ?? '') }}" required autocomplete="address-line1" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
</div>

<div>
    <label for="{{ $prefix }}-street-line-2" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.street_line_2') }}</label>
    <input id="{{ $prefix }}-street-line-2" name="street_line_2" value="{{ old('street_line_2', $address['street_line_2'] ?? '') }}" autocomplete="address-line2" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label for="{{ $prefix }}-postal-code" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.postal_code') }}</label>
        <input id="{{ $prefix }}-postal-code" name="postal_code" value="{{ old('postal_code', $address['postal_code'] ?? '') }}" required autocomplete="postal-code" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
    </div>
    <div>
        <label for="{{ $prefix }}-city" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.city') }}</label>
        <input id="{{ $prefix }}-city" name="city" value="{{ old('city', $address['city'] ?? '') }}" required autocomplete="address-level2" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
    </div>
    <div>
        <label for="{{ $prefix }}-region" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.region') }}</label>
        <input id="{{ $prefix }}-region" name="region" value="{{ old('region', $address['region'] ?? '') }}" autocomplete="address-level1" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="{{ $prefix }}-country-code" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.country_code') }}</label>
        <select id="{{ $prefix }}-country-code" name="country_code" required autocomplete="country" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
            @foreach ($countries as $country)
                <option value="{{ $country['code'] }}" @selected(old('country_code', $address['country_code'] ?? 'FR') === $country['code'])>{{ $country['name'] }} · {{ $country['currency'] }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="{{ $prefix }}-phone" class="text-sm font-bold text-cocoa dark:text-cream">{{ __('home.account.addresses.phone') }}</label>
        <input id="{{ $prefix }}-phone" name="phone" value="{{ old('phone', $address['phone'] ?? '') }}" autocomplete="tel" class="mt-2 min-h-[48px] w-full rounded-2xl border border-leaf/15 bg-linen px-4 text-sm text-cocoa outline-none transition focus:border-leaf dark:border-white/10 dark:bg-ink dark:text-cream">
    </div>
</div>

<label class="flex items-center gap-3 rounded-2xl border border-leaf/10 bg-mint/50 p-4 text-sm font-semibold text-cocoa/75 dark:border-white/10 dark:bg-white/5 dark:text-cream/75">
    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address['is_default'] ?? false))>
    <span>{{ __('home.account.addresses.is_default') }}</span>
</label>
