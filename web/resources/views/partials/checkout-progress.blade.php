@php
    $labels = $currentLocale === 'fr'
        ? ['shop' => 'Boutique', 'cart' => 'Panier', 'checkout' => 'Livraison', 'success' => 'Validation']
        : ['shop' => 'Shop', 'cart' => 'Cart', 'checkout' => 'Delivery', 'success' => 'Confirmation'];

    $steps = [
        ['key' => 'shop', 'label' => $labels['shop'], 'url' => route('home.localized', ['locale' => $currentLocale]) . '#products'],
        ['key' => 'cart', 'label' => $labels['cart'], 'url' => route('cart.show', ['locale' => $currentLocale])],
        ['key' => 'checkout', 'label' => $labels['checkout'], 'url' => route('checkout.show', ['locale' => $currentLocale])],
        ['key' => 'success', 'label' => $labels['success'], 'url' => '#'],
    ];

    $activeIndex = collect($steps)->search(fn (array $step) => $step['key'] === $currentStep);
    $activeIndex = $activeIndex === false ? 0 : $activeIndex;
@endphp

<nav class="mb-6 w-full" aria-label="Checkout progress">
    <ol class="grid grid-cols-4 gap-2 rounded-[24px] border p-2 shadow-sm" style="border-color:var(--store-border);background:var(--store-card)">
        @foreach ($steps as $index => $step)
            @php
                $isDone = $index < $activeIndex;
                $isActive = $index === $activeIndex;
                $isLocked = $step['key'] === 'success' && ! $isActive;
            @endphp
            <li class="min-w-0">
                @if (! $isLocked)
                    <a href="{{ $step['url'] }}" class="flex min-h-[52px] flex-col items-center justify-center rounded-2xl px-2 py-2 text-center transition {{ $isActive ? 'bg-[#fff1e8] text-[#f97316] dark:bg-[#3b2e24]' : 'text-cocoa/55 hover:bg-[#fff7ed] hover:text-[#f97316] dark:text-white/55 dark:hover:bg-white/10' }}" @if ($isActive) aria-current="step" @endif wire:navigate>
                @else
                    <span class="flex min-h-[52px] flex-col items-center justify-center rounded-[1rem] px-2 py-2 text-center text-cocoa/35 dark:text-cream/35">
                @endif
                    <span class="grid h-6 w-6 place-items-center rounded-full text-[10px] font-bold {{ $isActive ? 'bg-[#f97316] text-white' : ($isDone ? 'bg-[#f97316]/80 text-white' : 'bg-cocoa/10 text-cocoa/50 dark:bg-white/10 dark:text-white/50') }}">
                        {{ $isDone ? '✓' : $index + 1 }}
                    </span>
                    <span class="mt-1 block max-w-full truncate text-[10px] font-black uppercase tracking-wide sm:text-xs">
                        {{ $step['label'] }}
                    </span>
                @if (! $isLocked)
                    </a>
                @else
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
