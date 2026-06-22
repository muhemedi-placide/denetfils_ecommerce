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
    <ol class="grid grid-cols-4 gap-2 rounded-[1.25rem] border border-leaf/10 bg-white/85 p-2 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
        @foreach ($steps as $index => $step)
            @php
                $isDone = $index < $activeIndex;
                $isActive = $index === $activeIndex;
                $isLocked = $step['key'] === 'success' && ! $isActive;
            @endphp
            <li class="min-w-0">
                @if (! $isLocked)
                    <a href="{{ $step['url'] }}" class="flex min-h-[52px] flex-col items-center justify-center rounded-[1rem] px-2 py-2 text-center transition {{ $isActive ? 'bg-mint text-forest dark:bg-white/10 dark:text-meadow' : 'text-cocoa/55 hover:bg-linen hover:text-forest dark:text-cream/55 dark:hover:bg-white/10' }}" @if ($isActive) aria-current="step" @endif wire:navigate>
                @else
                    <span class="flex min-h-[52px] flex-col items-center justify-center rounded-[1rem] px-2 py-2 text-center text-cocoa/35 dark:text-cream/35">
                @endif
                    <span class="grid h-6 w-6 place-items-center rounded-full text-[10px] font-black {{ $isActive ? 'bg-forest text-cream dark:bg-meadow dark:text-ink' : ($isDone ? 'bg-forest/80 text-cream dark:bg-meadow/80 dark:text-ink' : 'bg-cocoa/10 text-cocoa/50 dark:bg-cream/10 dark:text-cream/50') }}">
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
