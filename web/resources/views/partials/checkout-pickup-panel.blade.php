@php
    $panelTitle = $panelTitle ?? ($locale === 'fr' ? 'Points de retrait les plus proches' : 'Nearest pickup points');
    $isModal = $isModal ?? false;
    $formatDistance = static function (array $point): string {
        $meters = $point['distance_meters'] ?? null;
        if (! is_numeric($meters)) return '';

        return (int) $meters >= 1000
            ? number_format(((int) $meters) / 1000, 1, ',', ' ').' km'
            : ((int) $meters).' m';
    };
@endphp

<div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-lg font-black text-cocoa dark:text-cream">{{ $panelTitle }}</h3>
            <p class="mt-1 text-sm text-cocoa/60 dark:text-cream/60">
                {{ $locale === 'fr' ? 'Sélectionnez simplement l’adresse de retrait qui vous convient.' : 'Simply select the pickup address that suits you.' }}
            </p>
        </div>

        <label class="block w-full sm:max-w-xs">
            <span class="sr-only">{{ $locale === 'fr' ? 'Rechercher une ville ou un code postal' : 'Search city or postcode' }}</span>
            <input
                class="w-full rounded-xl border border-cocoa/15 bg-white px-4 py-3 text-base outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 dark:border-white/10 dark:bg-white/5 dark:text-cream"
                type="search"
                wire:model.live.debounce.500ms="pickupQuery"
                placeholder="{{ $locale === 'fr' ? 'Ville ou code postal' : 'City or postcode' }}"
            >
        </label>
    </div>

    <div class="mt-4 grid max-h-[34rem] gap-2 overflow-y-auto pr-1 md:grid-cols-2">
        @forelse ($pickupPoints as $point)
            <button
                type="button"
                class="w-full rounded-xl border px-4 py-3 text-left transition {{ $selectedPickupPoint === $point['code'] ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/15 dark:bg-orange-500/10' : 'border-cocoa/10 bg-white hover:border-orange-400 hover:bg-orange-50/50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10' }}"
                wire:click="selectPickupPoint('{{ $point['code'] }}')"
                wire:key="{{ $isModal ? 'modal' : 'inline' }}-pickup-row-{{ $point['code'] }}"
            >
                <span class="flex items-start justify-between gap-3">
                    <span class="min-w-0">
                        <span class="block text-base font-black text-cocoa dark:text-cream">{{ $point['name'] }}</span>
                        <span class="mt-1 block text-sm leading-6 text-cocoa/70 dark:text-cream/70">{{ $point['address'] }}</span>
                    </span>

                    @if ($formatDistance($point) !== '')
                        <span class="shrink-0 rounded-full bg-orange-100 px-2.5 py-1 text-xs font-bold text-orange-700 dark:bg-orange-500/15 dark:text-orange-200">
                            {{ $formatDistance($point) }}
                        </span>
                    @endif
                </span>
            </button>
        @empty
            <div class="rounded-xl border border-dashed border-cocoa/15 px-4 py-6 text-base text-cocoa/65 dark:border-white/10 dark:text-cream/65 md:col-span-2">
                {{ $locale === 'fr' ? 'Aucun point de retrait trouvé près de cette adresse.' : 'No pickup point found near this address.' }}
            </div>
        @endforelse
    </div>
</div>
