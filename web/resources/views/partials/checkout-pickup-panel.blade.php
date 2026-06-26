@php
    $buttonLabel = $buttonLabel ?? ($locale === 'fr' ? 'Utiliser ce point' : 'Use this point');
    $panelTitle = $panelTitle ?? ($locale === 'fr' ? 'Sélectionnez votre point de retrait' : 'Select your pickup point');
    $isModal = $isModal ?? false;
    $selectedPoint = collect($pickupPoints)->firstWhere('code', $selectedPickupPoint);
    $formatDistance = static function (array $point): string {
        $meters = $point['distance_meters'] ?? null;
        if (! is_numeric($meters)) return '';
        return (int) $meters >= 1000
            ? number_format(((int) $meters) / 1000, 1, ',', ' ').' km'
            : ((int) $meters).' m';
    };
@endphp

<div class="overflow-hidden rounded-[1rem] border border-cocoa/10 bg-white dark:border-white/10 dark:bg-white/5">
    <div class="border-b border-cocoa/10 bg-neutral-100 px-4 py-3 text-center text-sm font-black text-cocoa/70 dark:border-white/10 dark:bg-white/10 dark:text-cream/80">
        {{ $panelTitle }}
        <span class="ml-2 inline-flex rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-black uppercase tracking-wide text-emerald-700">{{ $locale === 'fr' ? 'Données API en direct' : 'Live API data' }}</span>
    </div>

    <div class="grid lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="border-r border-cocoa/10 p-4 dark:border-white/10">
            <p class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold leading-5 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ $locale === 'fr'
                    ? 'Les points les plus proches sont proposés automatiquement à partir de votre adresse de livraison. Utilisez la recherche seulement pour changer de secteur.'
                    : 'The nearest pickup points are suggested automatically from your delivery address. Use search only to change area.' }}
            </p>
            <label class="block">
                <span class="text-xs font-black uppercase tracking-wide text-cocoa/50 dark:text-cream/50">{{ $locale === 'fr' ? 'Recherche Mondial Relay' : 'Mondial Relay search' }}</span>
                <input class="mt-2 w-full rounded-lg border border-cocoa/10 bg-white px-3 py-2 text-sm font-semibold outline-none transition focus:border-[#a01455] focus:ring-4 focus:ring-[#a01455]/10 dark:border-white/10 dark:bg-white/5 dark:text-cream" type="search" wire:model.live.debounce.500ms="pickupQuery" placeholder="{{ $locale === 'fr' ? 'Ville ou code postal' : 'City or postcode' }}">
            </label>

            <div class="mt-4 max-h-[430px] space-y-2 overflow-y-auto">
                @forelse ($pickupPoints as $point)
                    <button type="button" class="w-full rounded-xl border p-4 text-left transition {{ $selectedPickupPoint === $point['code'] ? 'border-[#a01455] bg-[#a01455]/5 ring-2 ring-[#a01455]/15' : 'border-cocoa/10 hover:border-[#a01455]/40 dark:border-white/10' }}" wire:click="selectPickupPoint('{{ $point['code'] }}')" wire:key="{{ $isModal ? 'modal' : 'inline' }}-pickup-row-{{ $point['code'] }}">
                        <span class="block text-sm font-black uppercase text-[#a01455]">{{ $point['name'] }}</span>
                        <span class="mt-1 block text-sm leading-5 text-cocoa/75 dark:text-cream/75">{{ $point['address'] }}</span>
                        @if ($formatDistance($point) !== '')
                            <span class="mt-2 inline-flex rounded-full bg-linen px-2 py-1 text-xs font-black text-cocoa/60 dark:bg-white/10 dark:text-cream/60">{{ $formatDistance($point) }}</span>
                        @endif
                    </button>
                @empty
                    <div class="rounded-xl border border-dashed border-cocoa/15 bg-linen px-4 py-5 text-sm text-cocoa/65 dark:border-white/10 dark:bg-white/5 dark:text-cream/65">
                        {{ $locale === 'fr' ? 'Aucun point retourné par Mondial Relay pour cette recherche.' : 'Mondial Relay returned no pickup point for this search.' }}
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="bg-linen p-5 dark:bg-ink/60">
            @if ($selectedPoint)
                <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ $locale === 'fr' ? 'Point sélectionné' : 'Selected point' }}</p>
                <h3 class="mt-2 text-xl font-black text-forest dark:text-meadow">{{ $selectedPoint['name'] }}</h3>
                <p class="mt-3 text-sm leading-6 text-cocoa/75 dark:text-cream/75">{{ $selectedPoint['address'] }}</p>
                @if (isset($selectedPoint['latitude'], $selectedPoint['longitude']))
                    <p class="mt-4 text-xs font-semibold text-cocoa/50 dark:text-cream/50">
                        {{ number_format((float) $selectedPoint['latitude'], 5) }}, {{ number_format((float) $selectedPoint['longitude'], 5) }}
                    </p>
                @endif
                @if (! empty($selectedPoint['opening_hours']))
                    <p class="mt-4 rounded-lg bg-white px-3 py-2 text-xs font-semibold text-cocoa/60 dark:bg-white/10 dark:text-cream/60">
                        {{ $locale === 'fr' ? 'Horaires fournis par Mondial Relay.' : 'Opening hours provided by Mondial Relay.' }}
                    </p>
                @endif
                @if (! $isModal)
                    <button type="button" class="mt-5 w-full rounded-full bg-[#45ad4d] px-6 py-3 text-sm font-black text-white" wire:click="selectPickupPoint('{{ $selectedPickupPoint }}')">{{ $buttonLabel }}</button>
                @endif
            @else
                <p class="text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $locale === 'fr' ? 'Choisissez un point dans la liste.' : 'Choose a point from the list.' }}</p>
            @endif
        </aside>
    </div>
</div>
