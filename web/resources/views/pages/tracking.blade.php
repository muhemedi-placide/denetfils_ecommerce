@extends('layouts.shop')

@section('title', ($locale ?? 'fr') === 'en' ? 'Track parcel' : 'Suivi de colis')

@section('content')
@php
    $currentLocale = $locale ?? app()->getLocale();
    $isFr = $currentLocale === 'fr';
    $data = is_array($tracking ?? null) ? ($tracking['data'] ?? []) : [];
    $events = is_array($data['events'] ?? null) ? $data['events'] : [];
@endphp

<section class="px-4 py-14 sm:px-8 lg:py-20">
    <div class="mx-auto max-w-5xl">
        <div class="rounded-[2rem] border border-forest/10 bg-white p-6 shadow-xl dark:border-white/10 dark:bg-white/5 sm:p-10">
            <p class="text-xs font-black uppercase tracking-[0.28em] text-coral">{{ $isFr ? 'Logistique' : 'Logistics' }}</p>
            <h1 class="mt-4 text-4xl font-black leading-none tracking-tight text-forest dark:text-cream sm:text-6xl">
                {{ $isFr ? 'Suivi de colis' : 'Track your parcel' }}
            </h1>
            <p class="mt-5 max-w-2xl text-base font-semibold leading-7 text-cocoa/70 dark:text-cream/70">
                {{ $isFr ? 'Entrez votre numéro de suivi Mondial Relay pour consulter les dernières informations retournées par le WebService.' : 'Enter your Mondial Relay tracking number to view the latest information returned by the WebService.' }}
            </p>

            <form method="GET" action="{{ route('pages.tracking', ['locale' => $currentLocale]) }}" class="mt-8 grid gap-3 sm:grid-cols-[1fr_auto]">
                <label class="sr-only" for="tracking_number">{{ $isFr ? 'Numéro de suivi' : 'Tracking number' }}</label>
                <input
                    id="tracking_number"
                    name="tracking_number"
                    value="{{ $trackingNumber }}"
                    placeholder="{{ $isFr ? 'Ex : 123456789012' : 'Example: 123456789012' }}"
                    class="h-14 rounded-2xl border-2 border-forest/15 bg-linen px-5 text-base font-black text-forest outline-none transition placeholder:text-forest/40 focus:border-leaf focus:ring-4 focus:ring-leaf/10 dark:border-white/10 dark:bg-white/10 dark:text-cream dark:placeholder:text-cream/40"
                >
                <button class="h-14 rounded-2xl bg-forest px-7 text-sm font-black uppercase tracking-wide text-cream transition hover:bg-leaf" type="submit">
                    {{ $isFr ? 'Suivre' : 'Track' }}
                </button>
            </form>
        </div>

        @if (($trackingNumber ?? '') !== '')
            <div class="mt-8 rounded-[2rem] border border-forest/10 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-8">
                @if (($tracking['ok'] ?? false) && $data)
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-leaf dark:text-meadow">{{ $data['carrier_code'] ?? 'mondial_relay' }}</p>
                            <h2 class="mt-2 text-2xl font-black text-forest dark:text-cream">{{ $trackingNumber }}</h2>
                            <p class="mt-2 text-sm font-semibold text-cocoa/65 dark:text-cream/65">
                                {{ $data['status_label'] ?? ($isFr ? 'Statut retourné par Mondial Relay' : 'Status returned by Mondial Relay') }}
                            </p>
                        </div>
                        <span class="inline-flex rounded-full bg-mint px-4 py-2 text-xs font-black uppercase tracking-wide text-leaf dark:bg-white/10 dark:text-meadow">
                            {{ ! empty($data['delivered']) ? ($isFr ? 'Livré' : 'Delivered') : ($isFr ? 'En cours' : 'In progress') }}
                        </span>
                    </div>

                    <div class="mt-8 space-y-4">
                        @forelse ($events as $event)
                            <article class="rounded-2xl border border-leaf/10 bg-linen/70 p-5 dark:border-white/10 dark:bg-white/5">
                                <p class="font-black text-forest dark:text-cream">{{ $event['label'] ?? ($isFr ? 'Événement logistique' : 'Logistics event') }}</p>
                                <p class="mt-2 text-sm font-semibold text-cocoa/60 dark:text-cream/60">
                                    {{ trim(($event['date'] ?? '').' '.($event['time'] ?? '')) ?: ($isFr ? 'Date non précisée' : 'Date unavailable') }}
                                    @if (! empty($event['location']))
                                        · {{ $event['location'] }}
                                    @endif
                                </p>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-leaf/10 bg-linen/70 p-5 text-sm font-semibold text-cocoa/65 dark:border-white/10 dark:bg-white/5 dark:text-cream/65">
                                {{ $isFr ? 'Mondial Relay a répondu, mais aucun historique détaillé n’a été retourné pour ce numéro.' : 'Mondial Relay responded, but no detailed tracking history was returned for this number.' }}
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm font-bold text-red-800 dark:border-red-400/20 dark:bg-red-400/10 dark:text-red-200">
                        {{ $tracking['message'] ?? ($isFr ? 'Suivi indisponible pour ce numéro.' : 'Tracking is unavailable for this number.') }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
@endsection
