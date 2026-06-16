@extends('layouts.admin')

@php
    $isEnglish = ($locale ?? 'fr') === 'en';
@endphp

@section('title', $isEnglish ? 'Delivery' : 'Livraison')
@section('page_title', $isEnglish ? 'Delivery' : 'Livraison')
@section('page_subtitle', $isEnglish ? 'Configure carriers, zones and pickup points.' : 'Configurez les transporteurs, les zones et les points relais.')

@section('content')
    <section class="space-y-5">
        @if (session('admin_success'))
            <div class="rounded-2xl border border-leaf/15 bg-mint px-5 py-4 text-sm font-black text-leaf">
                {{ session('admin_success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <article class="admin-card overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-leaf/10 p-5 dark:border-white/10 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                <div>
                    <p class="admin-kicker">{{ $isEnglish ? 'Customize' : 'Personnaliser' }}</p>
                    <h2 class="mt-2 text-3xl font-black text-ink dark:text-cream">{{ $isEnglish ? 'Carriers' : 'Transporteurs' }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-cocoa/65 dark:text-cream/65">
                        {{ $isEnglish ? 'First carrier prepared for Mondial Relay: pickup points, lockers, credentials and tracking.' : 'Premier transporteur préparé pour Mondial Relay : points relais, lockers, identifiants et suivi colis.' }}
                    </p>
                </div>
                <button type="button" class="admin-btn" onclick="document.getElementById('carrier-create-modal').showModal()">
                    {{ $isEnglish ? 'Add a carrier' : 'Ajouter un transporteur' }}
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Carrier' : 'Transporteur' }}</th>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Provider' : 'Prestataire' }}</th>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Environment' : 'Environnement' }}</th>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Countries' : 'Pays' }}</th>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Modes' : 'Modes' }}</th>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Status' : 'Statut' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (data_get($carriers ?? [], 'data', []) as $carrier)
                            <tr>
                                <td class="px-5 py-4 font-black text-ink dark:text-cream">
                                    {{ data_get($carrier, 'display_name.'.($locale ?? 'fr'), data_get($carrier, 'code')) }}
                                    <p class="mt-1 text-xs font-semibold text-cocoa/45 dark:text-cream/45">{{ data_get($carrier, 'code') }}</p>
                                </td>
                                <td class="px-5 py-4">{{ data_get($carrier, 'provider_name', 'Mondial Relay') }}</td>
                                <td class="px-5 py-4"><span class="admin-pill">{{ data_get($carrier, 'environment') }}</span></td>
                                <td class="px-5 py-4">{{ implode(', ', data_get($carrier, 'countries', [])) ?: '—' }}</td>
                                <td class="px-5 py-4">{{ implode(', ', data_get($carrier, 'delivery_modes', [])) ?: '—' }}</td>
                                <td class="px-5 py-4"><span class="admin-pill">{{ data_get($carrier, 'status') }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-10 text-center text-sm font-semibold text-cocoa/55 dark:text-cream/55" colspan="6">
                                    {{ $isEnglish ? 'No carrier configured yet.' : 'Aucun transporteur configuré pour le moment.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <dialog id="carrier-create-modal" class="admin-dialog admin-dialog-wide">
        <div class="admin-modal-card">
            <form method="POST" action="{{ route('admin.delivery.carriers.store', ['locale' => $locale]) }}" class="p-5 sm:p-6">
                @csrf
                <input type="hidden" name="provider" value="mondial_relay">
                <input type="hidden" name="status" value="draft">
                <input type="hidden" name="is_enabled" value="0">
                <input type="hidden" name="supports_relay_points" value="1">
                <input type="hidden" name="supports_home_delivery" value="0">

                <div class="flex items-start justify-between gap-4 border-b border-leaf/10 pb-5 dark:border-white/10">
                    <div>
                        <p class="admin-kicker">Mondial Relay</p>
                        <h2 class="mt-2 text-2xl font-black text-ink dark:text-cream">{{ $isEnglish ? 'Add a carrier' : 'Ajouter un transporteur' }}</h2>
                        <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">
                            {{ $isEnglish ? 'Fill in the Mondial Relay credentials, then save the carrier.' : 'Renseignez les identifiants Mondial Relay, puis enregistrez le transporteur.' }}
                        </p>
                    </div>
                    <button type="button" class="admin-icon-btn" onclick="document.getElementById('carrier-create-modal').close()">×</button>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Code</span>
                        <input class="admin-input" name="code" value="{{ old('code', 'mondial_relay') }}" required>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Environment' : 'Environnement' }}</span>
                        <select class="admin-select" name="environment" required>
                            <option value="sandbox" @selected(old('environment', 'sandbox') === 'sandbox')>Sandbox</option>
                            <option value="live" @selected(old('environment') === 'live')>Live</option>
                        </select>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'French name' : 'Nom français' }}</span>
                        <input class="admin-input" name="display_name[fr]" value="{{ old('display_name.fr', 'Mondial Relay') }}" required>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'English name' : 'Nom anglais' }}</span>
                        <input class="admin-input" name="display_name[en]" value="{{ old('display_name.en', 'Mondial Relay') }}">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Enseigne</span>
                        <input class="admin-input" name="credentials[enseigne]" value="{{ old('credentials.enseigne') }}" required>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Private key' : 'Clé privée' }}</span>
                        <input class="admin-input" name="credentials[private_key]" type="password" required>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Brand code' : 'Code marque' }}</span>
                        <input class="admin-input" name="credentials[brand_code]" value="{{ old('credentials.brand_code') }}">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Account number' : 'Numéro de compte' }}</span>
                        <input class="admin-input" name="credentials[account_number]" value="{{ old('credentials.account_number') }}">
                    </label>

                    <label class="space-y-2 lg:col-span-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Endpoint API</span>
                        <input class="admin-input" name="credentials[api_endpoint]" value="{{ old('credentials.api_endpoint', 'https://api.mondialrelay.com/Web_Services.asmx') }}">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Countries' : 'Pays' }}</span>
                        <input class="admin-input" name="countries" value="{{ old('countries', 'FR, BE, LU, ES, NL') }}">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Max weight, grams' : 'Poids max, grammes' }}</span>
                        <input class="admin-input" name="max_weight_grams" type="number" min="1" max="70000" value="{{ old('max_weight_grams', 30000) }}">
                    </label>

                    <div class="space-y-2 lg:col-span-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Delivery modes' : 'Modes de livraison' }}</span>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <label class="rounded-xl border border-leaf/10 bg-linen p-3 text-sm font-bold dark:border-white/10 dark:bg-white/5"><input type="checkbox" name="delivery_modes[]" value="24R" checked> Point Relais</label>
                            <label class="rounded-xl border border-leaf/10 bg-linen p-3 text-sm font-bold dark:border-white/10 dark:bg-white/5"><input type="checkbox" name="delivery_modes[]" value="24L" checked> Locker</label>
                            <label class="rounded-xl border border-leaf/10 bg-linen p-3 text-sm font-bold dark:border-white/10 dark:bg-white/5"><input type="checkbox" name="delivery_modes[]" value="HOM"> Home</label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-leaf/10 pt-5 dark:border-white/10">
                    <button type="button" class="admin-btn-secondary" onclick="document.getElementById('carrier-create-modal').close()">{{ $isEnglish ? 'Cancel' : 'Annuler' }}</button>
                    <button type="submit" class="admin-btn">{{ $isEnglish ? 'Save carrier' : 'Enregistrer le transporteur' }}</button>
                </div>
            </form>
        </div>
    </dialog>
@endsection
