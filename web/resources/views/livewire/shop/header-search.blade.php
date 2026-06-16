<form wire:submit.prevent="search" class="{{ $formClass }}">
    <label class="sr-only" for="{{ $inputId }}">{{ __('home.filters.search') }}</label>
    <input id="{{ $inputId }}" wire:model="q" name="q" placeholder="{{ __('home.filters.search_placeholder') }}" class="min-w-0 flex-1 bg-transparent px-5 py-3 text-sm text-cocoa outline-none placeholder:text-cocoa/40 dark:text-cream dark:placeholder:text-cream/40">
    <button type="submit" class="min-h-[44px] rounded-full bg-terracotta px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-clay">{{ __('home.filters.search') }}</button>
</form>
