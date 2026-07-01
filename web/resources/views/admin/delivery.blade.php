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
                            <th class="px-5 py-3 text-right">{{ $isEnglish ? 'Actions' : 'Actions' }}</th>
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
                                <td class="px-5 py-4">
                                    <span class="admin-pill">{{ data_get($carrier, 'status') }}</span>
                                    @if (data_get($carrier, 'last_test_message'))
                                        <p class="mt-2 max-w-xs text-xs font-semibold text-cocoa/55 dark:text-cream/55">{{ data_get($carrier, 'last_test_message') }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.delivery.carriers.action', ['locale' => $locale, 'carrier' => data_get($carrier, 'id'), 'action' => 'test-connection']) }}">@csrf<button class="admin-btn-secondary" type="submit">{{ $isEnglish ? 'Test' : 'Tester' }}</button></form>
                                        <form method="POST" action="{{ route('admin.delivery.carriers.action', ['locale' => $locale, 'carrier' => data_get($carrier, 'id'), 'action' => data_get($carrier, 'is_enabled') ? 'deactivate' : 'activate']) }}">@csrf<button class="admin-btn-secondary" type="submit">{{ data_get($carrier, 'is_enabled') ? ($isEnglish ? 'Disable' : 'Désactiver') : ($isEnglish ? 'Enable' : 'Activer') }}</button></form>
                                    </div>
                                </td>
                            </tr>
                            @if (data_get($carrier, 'provider') === 'mondial_relay')
                                <tr>
                                    <td class="px-5 py-4" colspan="7">
                                        <form method="POST" action="{{ route('admin.delivery.carriers.update', ['locale' => $locale, 'carrier' => data_get($carrier, 'id')]) }}" class="rounded-2xl border border-leaf/10 bg-linen/50 p-4 dark:border-white/10 dark:bg-white/5">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                                <div>
                                                    <h3 class="font-black text-ink dark:text-cream">{{ $isEnglish ? 'Sender used for labels' : 'Expediteur utilise pour les etiquettes' }}</h3>
                                                    <p class="mt-1 text-xs font-semibold text-cocoa/55 dark:text-cream/55">
                                                        {{ $isEnglish ? 'Required before creating a Mondial Relay shipment.' : 'Obligatoire avant la creation expedition Mondial Relay.' }}
                                                    </p>
                                                </div>
                                                @if (count(data_get($carrier, 'credentials.missing_required', [])))
                                                    <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-700">{{ $isEnglish ? 'Missing credentials' : 'Identifiants incomplets' }}</span>
                                                @endif
                                            </div>
                                            <div class="mt-4 grid gap-3 lg:grid-cols-4">
                                                <input class="admin-input" name="credentials[sender_name]" placeholder="{{ $isEnglish ? 'Sender name' : 'Nom expediteur' }}" value="{{ data_get($carrier, 'credentials.masked.sender_name') }}">
                                                <input class="admin-input" name="credentials[sender_phone]" placeholder="{{ $isEnglish ? 'Sender phone' : 'Telephone expediteur' }}" value="{{ data_get($carrier, 'credentials.masked.sender_phone') }}">
                                                <input class="admin-input lg:col-span-2" name="credentials[sender_email]" placeholder="{{ $isEnglish ? 'Sender email' : 'Email expediteur' }}" value="{{ data_get($carrier, 'credentials.masked.sender_email') }}">
                                                <input class="admin-input lg:col-span-2" name="credentials[sender_address]" placeholder="{{ $isEnglish ? 'Sender address' : 'Adresse expediteur' }}" value="{{ data_get($carrier, 'credentials.masked.sender_address') }}">
                                                <input class="admin-input" name="credentials[sender_address_2]" placeholder="{{ $isEnglish ? 'Address line 2' : 'Complement adresse' }}" value="{{ data_get($carrier, 'credentials.masked.sender_address_2') }}">
                                                <input class="admin-input" name="credentials[sender_postal_code]" placeholder="{{ $isEnglish ? 'Postal code' : 'Code postal' }}" value="{{ data_get($carrier, 'credentials.masked.sender_postal_code') }}">
                                                <input class="admin-input" name="credentials[sender_city]" placeholder="{{ $isEnglish ? 'City' : 'Ville' }}" value="{{ data_get($carrier, 'credentials.masked.sender_city') }}">
                                                <input class="admin-input" name="credentials[sender_country]" placeholder="{{ $isEnglish ? 'Country' : 'Pays' }}" maxlength="2" value="{{ data_get($carrier, 'credentials.masked.sender_country', 'FR') }}">
                                            </div>
                                            <div class="mt-4 flex justify-end">
                                                <button class="admin-btn-secondary" type="submit">{{ $isEnglish ? 'Save sender' : 'Enregistrer expediteur' }}</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td class="px-5 py-10 text-center text-sm font-semibold text-cocoa/55 dark:text-cream/55" colspan="7">
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
            <form method="POST" action="{{ route('admin.delivery.carriers.store', ['locale' => $locale]) }}" class="p-5 sm:p-6" x-data="{ provider: '{{ old('provider', 'mondial_relay') }}' }">
                @csrf
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
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Provider type' : 'Type de transporteur' }}</span>
                        <select class="admin-select" name="provider" x-model="provider" x-on:change="$nextTick(() => { if (provider === 'chronopost') { $refs.carrierCode.value = 'chronopost'; $refs.carrierNameFr.value = 'Chronopost'; $refs.carrierNameEn.value = 'Chronopost'; $refs.methodCode.value = 'chronopost_home'; $refs.serviceCode.value = 'HOME'; $refs.methodNameFr.value = 'Chronopost domicile'; $refs.methodNameEn.value = 'Chronopost home'; $refs.deliveryType.value = 'home'; } else { $refs.carrierCode.value = 'mondial_relay_custom'; $refs.carrierNameFr.value = 'Mondial Relay'; $refs.carrierNameEn.value = 'Mondial Relay'; $refs.methodCode.value = 'mondial_relay_point_relais_custom'; $refs.serviceCode.value = '24R'; $refs.methodNameFr.value = 'Point Relais®'; $refs.methodNameEn.value = 'Pickup Point'; $refs.deliveryType.value = 'pickup_point'; } })" required>
                            <option value="mondial_relay">Mondial Relay</option>
                            <option value="chronopost">Chronopost</option>
                        </select>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Code</span>
                        <input x-ref="carrierCode" class="admin-input" name="code" value="{{ old('code', 'mondial_relay_custom') }}" required>
                    </label>

                    <label class="space-y-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Environment' : 'Environnement' }}</span>
                        <select class="admin-select" name="environment" required>
                            <option value="sandbox" @selected(old('environment', 'sandbox') === 'sandbox')>Sandbox</option>
                            <option value="live" @selected(old('environment') === 'live')>Live</option>
                        </select>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'French name' : 'Nom français' }}</span>
                        <input x-ref="carrierNameFr" class="admin-input" name="display_name[fr]" value="{{ old('display_name.fr', 'Mondial Relay') }}" required>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'English name' : 'Nom anglais' }}</span>
                        <input x-ref="carrierNameEn" class="admin-input" name="display_name[en]" value="{{ old('display_name.en', 'Mondial Relay') }}">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Enseigne</span>
                        <input class="admin-input" name="credentials[enseigne]" value="{{ old('credentials.enseigne') }}" x-bind:required="provider === 'mondial_relay'">
                    </label>

                    <label class="space-y-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Private key' : 'Clé privée' }}</span>
                        <input class="admin-input" name="credentials[private_key]" type="password" x-bind:required="provider === 'mondial_relay'">
                    </label>

                    <label class="space-y-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Brand code' : 'Code marque' }}</span>
                        <input class="admin-input" name="credentials[brand_code]" value="{{ old('credentials.brand_code') }}">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Account number' : 'Numéro de compte' }}</span>
                        <input class="admin-input" name="credentials[account_number]" value="{{ old('credentials.account_number') }}">
                    </label>

                    <label class="space-y-2" x-show="provider === 'chronopost'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'API password (optional)' : 'Mot de passe API (optionnel)' }}</span>
                        <input class="admin-input" name="credentials[password]" type="password">
                    </label>

                    <label class="space-y-2 lg:col-span-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Endpoint API</span>
                        <input class="admin-input" name="credentials[api_endpoint]" value="{{ old('credentials.api_endpoint') }}" placeholder="https://…">
                    </label>

                    <div class="lg:col-span-2 border-t border-leaf/10 pt-5 dark:border-white/10" x-show="provider === 'mondial_relay'">
                        <h3 class="text-lg font-black text-ink dark:text-cream">{{ $isEnglish ? 'Mondial Relay sender' : 'Expediteur Mondial Relay' }}</h3>
                        <p class="mt-2 text-xs font-semibold leading-5 text-cocoa/55 dark:text-cream/55">
                            {{ $isEnglish ? 'Required for shipment creation and label generation.' : 'Obligatoire pour creer les expeditions et generer les etiquettes.' }}
                        </p>
                    </div>

                    <label class="space-y-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Sender name' : 'Nom expediteur' }}</span>
                        <input class="admin-input" name="credentials[sender_name]" value="{{ old('credentials.sender_name') }}" x-bind:required="provider === 'mondial_relay'">
                    </label>

                    <label class="space-y-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Sender phone' : 'Telephone expediteur' }}</span>
                        <input class="admin-input" name="credentials[sender_phone]" value="{{ old('credentials.sender_phone') }}" x-bind:required="provider === 'mondial_relay'">
                    </label>

                    <label class="space-y-2 lg:col-span-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Sender address' : 'Adresse expediteur' }}</span>
                        <input class="admin-input" name="credentials[sender_address]" value="{{ old('credentials.sender_address') }}" x-bind:required="provider === 'mondial_relay'">
                    </label>

                    <label class="space-y-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Sender address line 2' : 'Complement adresse' }}</span>
                        <input class="admin-input" name="credentials[sender_address_2]" value="{{ old('credentials.sender_address_2') }}">
                    </label>

                    <label class="space-y-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Sender email' : 'Email expediteur' }}</span>
                        <input class="admin-input" name="credentials[sender_email]" type="email" value="{{ old('credentials.sender_email') }}" x-bind:required="provider === 'mondial_relay'">
                    </label>

                    <label class="space-y-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Sender postal code' : 'Code postal expediteur' }}</span>
                        <input class="admin-input" name="credentials[sender_postal_code]" value="{{ old('credentials.sender_postal_code') }}" x-bind:required="provider === 'mondial_relay'">
                    </label>

                    <label class="space-y-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Sender city' : 'Ville expediteur' }}</span>
                        <input class="admin-input" name="credentials[sender_city]" value="{{ old('credentials.sender_city') }}" x-bind:required="provider === 'mondial_relay'">
                    </label>

                    <label class="space-y-2" x-show="provider === 'mondial_relay'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Sender country' : 'Pays expediteur' }}</span>
                        <input class="admin-input" name="credentials[sender_country]" value="{{ old('credentials.sender_country', 'FR') }}" maxlength="2" x-bind:required="provider === 'mondial_relay'">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Countries' : 'Pays' }}</span>
                        <input class="admin-input" name="countries" value="{{ old('countries', 'FR, BE, LU, ES, NL') }}">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Max weight, grams' : 'Poids max, grammes' }}</span>
                        <input class="admin-input" name="max_weight_grams" type="number" min="1" max="70000" value="{{ old('max_weight_grams', 30000) }}">
                    </label>

                    <div class="lg:col-span-2 border-t border-leaf/10 pt-5 dark:border-white/10">
                        <h3 class="text-lg font-black text-ink dark:text-cream">{{ $isEnglish ? 'Checkout delivery method' : 'Méthode affichée au checkout' }}</h3>
                    </div>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Method code' : 'Code méthode' }}</span><input x-ref="methodCode" class="admin-input" name="method[code]" value="{{ old('method.code', 'mondial_relay_point_relais_custom') }}" required></label>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Service code' : 'Code service' }}</span><input x-ref="serviceCode" class="admin-input" name="method[service_code]" value="{{ old('method.service_code', '24R') }}" required></label>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'French method name' : 'Nom méthode français' }}</span><input x-ref="methodNameFr" class="admin-input" name="method[name][fr]" value="{{ old('method.name.fr', 'Point Relais®') }}" required></label>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'English method name' : 'Nom méthode anglais' }}</span><input x-ref="methodNameEn" class="admin-input" name="method[name][en]" value="{{ old('method.name.en', 'Pickup Point') }}"></label>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Delivery type' : 'Type de livraison' }}</span><select x-ref="deliveryType" class="admin-select" name="method[delivery_type]" required><option value="pickup_point">Point Relais</option><option value="locker">Locker</option><option value="home">{{ $isEnglish ? 'Home' : 'Domicile' }}</option></select></label>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Price' : 'Prix' }}</span><div class="flex gap-2"><input class="admin-input" name="method[price]" type="number" min="0" step="0.01" value="{{ old('method.price', '9.90') }}" required><input class="admin-input max-w-24" name="method[currency]" value="{{ old('method.currency', 'EUR') }}" maxlength="3" required></div></label>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Minimum days' : 'Délai minimum' }}</span><input class="admin-input" name="method[min_delivery_days]" type="number" min="0" max="60" value="{{ old('method.min_delivery_days', 1) }}"></label>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Maximum days' : 'Délai maximum' }}</span><input class="admin-input" name="method[max_delivery_days]" type="number" min="0" max="90" value="{{ old('method.max_delivery_days', 3) }}"></label>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-leaf/10 pt-5 dark:border-white/10">
                    <button type="button" class="admin-btn-secondary" onclick="document.getElementById('carrier-create-modal').close()">{{ $isEnglish ? 'Cancel' : 'Annuler' }}</button>
                    <button type="submit" class="admin-btn">{{ $isEnglish ? 'Save carrier' : 'Enregistrer le transporteur' }}</button>
                </div>
            </form>
        </div>
    </dialog>
@endsection
