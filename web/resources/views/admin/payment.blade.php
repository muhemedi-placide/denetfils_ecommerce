@extends('layouts.admin')

@php
    $isEnglish = ($locale ?? 'fr') === 'en';
    $methods = data_get($paymentMethods ?? [], 'data', []);
    $schemas = data_get($paymentSchemas ?? [], 'data', []);
    $providerLabels = collect($schemas)->mapWithKeys(fn ($schema, $key) => [$key => $schema['name'] ?? $key])->all();
@endphp

@section('title', $isEnglish ? 'Payment' : 'Paiement')
@section('page_title', $isEnglish ? 'Payment' : 'Paiement')
@section('page_subtitle', $isEnglish ? 'Configure checkout payment methods and provider credentials.' : 'Configurez les moyens de paiement du checkout et les identifiants prestataires.')

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
                    <h2 class="mt-2 text-3xl font-black text-ink dark:text-cream">{{ $isEnglish ? 'Payment methods' : 'Modes de paiement' }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-cocoa/65 dark:text-cream/65">
                        {{ $isEnglish ? 'Enable Stripe, PayPal and manual payment methods for checkout. Secrets stay stored in the API and are only shown masked.' : 'Activez Stripe, PayPal et les modes manuels du checkout. Les secrets restent stockes cote API et ne sont affiches que masques.' }}
                    </p>
                </div>
                <button type="button" class="admin-btn" onclick="document.getElementById('payment-create-modal').showModal()">
                    {{ $isEnglish ? 'Add method' : 'Ajouter un mode' }}
                </button>
            </div>

            <form method="GET" action="{{ route('admin.payment', ['locale' => $locale]) }}" class="grid gap-3 border-b border-leaf/10 p-5 dark:border-white/10 md:grid-cols-5">
                <input class="admin-input md:col-span-2" name="q" value="{{ data_get($filters ?? [], 'q') }}" placeholder="{{ $isEnglish ? 'Search code or label' : 'Chercher code ou libelle' }}">
                <select class="admin-select" name="provider">
                    <option value="">{{ $isEnglish ? 'All providers' : 'Tous prestataires' }}</option>
                    @foreach (['stripe', 'paypal', 'bank_transfer', 'cash_on_delivery'] as $provider)
                        <option value="{{ $provider }}" @selected(data_get($filters ?? [], 'provider') === $provider)>{{ $providerLabels[$provider] ?? $provider }}</option>
                    @endforeach
                </select>
                <select class="admin-select" name="status">
                    <option value="">{{ $isEnglish ? 'All statuses' : 'Tous statuts' }}</option>
                    @foreach (['draft', 'active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(data_get($filters ?? [], 'status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button class="admin-btn-secondary" type="submit">{{ $isEnglish ? 'Filter' : 'Filtrer' }}</button>
            </form>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Method' : 'Mode' }}</th>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Provider' : 'Prestataire' }}</th>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Scope' : 'Portee' }}</th>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Credentials' : 'Identifiants' }}</th>
                            <th class="px-5 py-3">{{ $isEnglish ? 'Status' : 'Statut' }}</th>
                            <th class="px-5 py-3 text-right">{{ $isEnglish ? 'Actions' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($methods as $method)
                            @php
                                $provider = data_get($method, 'provider');
                                $missing = data_get($method, 'credentials.missing_required', []);
                                $configured = data_get($method, 'credentials.configured', []);
                            @endphp
                            <tr>
                                <td class="px-5 py-4 font-black text-ink dark:text-cream">
                                    {{ data_get($method, 'display_name.'.($locale ?? 'fr'), data_get($method, 'code')) }}
                                    <p class="mt-1 text-xs font-semibold text-cocoa/45 dark:text-cream/45">{{ data_get($method, 'code') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="admin-pill">{{ data_get($method, 'provider_name', $provider) }}</span>
                                    <p class="mt-2 text-xs font-semibold text-cocoa/55 dark:text-cream/55">{{ data_get($method, 'environment') }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    <p>{{ implode(', ', data_get($method, 'countries', [])) ?: 'FR' }}</p>
                                    <p class="mt-1 text-cocoa/55 dark:text-cream/55">{{ implode(', ', data_get($method, 'currencies', [])) ?: 'EUR' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-cocoa/70 dark:text-cream/70">{{ implode(', ', $configured) ?: ($isEnglish ? 'None' : 'Aucun') }}</p>
                                    @if (count($missing))
                                        <p class="mt-2 rounded-lg bg-red-50 px-2 py-1 text-xs font-black text-red-700">{{ $isEnglish ? 'Missing:' : 'Manquant :' }} {{ implode(', ', $missing) }}</p>
                                    @elseif (count($configured))
                                        <p class="mt-2 rounded-lg bg-mint px-2 py-1 text-xs font-black text-leaf">{{ $isEnglish ? 'Ready' : 'Pret' }}</p>
                                    @endif
                                    @if (data_get($method, 'last_test_message'))
                                        <p class="mt-2 max-w-xs text-xs font-semibold text-cocoa/55 dark:text-cream/55">{{ data_get($method, 'last_test_message') }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="admin-pill">{{ data_get($method, 'status') }}</span>
                                    <p class="mt-2 text-xs font-semibold text-cocoa/55 dark:text-cream/55">{{ data_get($method, 'is_enabled') ? ($isEnglish ? 'Enabled' : 'Actif checkout') : ($isEnglish ? 'Disabled' : 'Inactif checkout') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.payment.methods.action', ['locale' => $locale, 'paymentMethod' => data_get($method, 'id'), 'action' => 'test-connection']) }}">
                                            @csrf
                                            <button class="admin-btn-secondary" type="submit">{{ $isEnglish ? 'Test' : 'Tester' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.payment.methods.action', ['locale' => $locale, 'paymentMethod' => data_get($method, 'id'), 'action' => data_get($method, 'is_enabled') ? 'deactivate' : 'activate']) }}">
                                            @csrf
                                            <button class="admin-btn-secondary" type="submit">{{ data_get($method, 'is_enabled') ? ($isEnglish ? 'Disable' : 'Desactiver') : ($isEnglish ? 'Enable' : 'Activer') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-10 text-center text-sm font-semibold text-cocoa/55 dark:text-cream/55" colspan="6">
                                    {{ $isEnglish ? 'No payment method configured yet.' : 'Aucun mode de paiement configure pour le moment.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="grid gap-4 md:grid-cols-3">
            <div class="admin-card p-5">
                <p class="admin-kicker">Stripe</p>
                <h3 class="mt-2 text-xl font-black text-ink dark:text-cream">{{ $isEnglish ? 'Card payments' : 'Paiement carte' }}</h3>
                <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $isEnglish ? 'Used by the checkout Payment Element.' : 'Utilise par le Payment Element du checkout.' }}</p>
            </div>
            <div class="admin-card p-5">
                <p class="admin-kicker">PayPal</p>
                <h3 class="mt-2 text-xl font-black text-ink dark:text-cream">Wallet</h3>
                <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $isEnglish ? 'Prepared for PayPal Orders and capture.' : 'Prepare pour PayPal Orders et capture.' }}</p>
            </div>
            <div class="admin-card p-5">
                <p class="admin-kicker">{{ $isEnglish ? 'Manual' : 'Manuel' }}</p>
                <h3 class="mt-2 text-xl font-black text-ink dark:text-cream">{{ $isEnglish ? 'Offline methods' : 'Modes hors ligne' }}</h3>
                <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $isEnglish ? 'Bank transfer and cash on delivery.' : 'Virement bancaire et paiement a la livraison.' }}</p>
            </div>
        </article>
    </section>

    <dialog id="payment-create-modal" class="admin-dialog admin-dialog-wide">
        <div class="admin-modal-card">
            <form
                method="POST"
                action="{{ route('admin.payment.methods.store', ['locale' => $locale]) }}"
                class="p-5 sm:p-6"
                x-data="{
                    provider: '{{ old('provider', 'stripe') }}',
                    setProvider(value) {
                        this.provider = value;
                        const presets = {
                            stripe: { code: 'stripe_cards', nameFr: 'Carte bancaire', nameEn: 'Card', env: 'sandbox' },
                            paypal: { code: 'paypal_wallet', nameFr: 'PayPal', nameEn: 'PayPal', env: 'sandbox' },
                            bank_transfer: { code: 'bank_transfer', nameFr: 'Virement bancaire', nameEn: 'Bank transfer', env: 'manual' },
                            cash_on_delivery: { code: 'cash_on_delivery', nameFr: 'Paiement a la livraison', nameEn: 'Cash on delivery', env: 'manual' },
                        };
                        const preset = presets[value] || presets.stripe;
                        this.$refs.code.value = preset.code;
                        this.$refs.nameFr.value = preset.nameFr;
                        this.$refs.nameEn.value = preset.nameEn;
                        this.$refs.environment.value = preset.env;
                    }
                }"
            >
                @csrf

                <div class="flex items-start justify-between gap-4 border-b border-leaf/10 pb-5 dark:border-white/10">
                    <div>
                        <p class="admin-kicker">{{ $isEnglish ? 'Payment provider' : 'Prestataire paiement' }}</p>
                        <h2 class="mt-2 text-2xl font-black text-ink dark:text-cream">{{ $isEnglish ? 'Add payment method' : 'Ajouter un mode de paiement' }}</h2>
                        <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">
                            {{ $isEnglish ? 'Start in sandbox, test the configuration, then enable it for checkout.' : 'Commencez en sandbox, testez la configuration, puis activez le mode pour le checkout.' }}
                        </p>
                    </div>
                    <button type="button" class="admin-icon-btn" onclick="document.getElementById('payment-create-modal').close()">x</button>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Provider' : 'Prestataire' }}</span>
                        <select class="admin-select" name="provider" x-model="provider" x-on:change="setProvider(provider)" required>
                            <option value="stripe">Stripe</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank_transfer">{{ $isEnglish ? 'Bank transfer' : 'Virement bancaire' }}</option>
                            <option value="cash_on_delivery">{{ $isEnglish ? 'Cash on delivery' : 'Paiement a la livraison' }}</option>
                        </select>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Code</span>
                        <input x-ref="code" class="admin-input" name="code" value="{{ old('code', 'stripe_cards') }}" required>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'French label' : 'Libelle francais' }}</span>
                        <input x-ref="nameFr" class="admin-input" name="display_name[fr]" value="{{ old('display_name.fr', 'Carte bancaire') }}" required>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'English label' : 'Libelle anglais' }}</span>
                        <input x-ref="nameEn" class="admin-input" name="display_name[en]" value="{{ old('display_name.en', 'Card') }}">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Environment' : 'Environnement' }}</span>
                        <select x-ref="environment" class="admin-select" name="environment" required>
                            <option value="sandbox">Sandbox</option>
                            <option value="live">Live</option>
                            <option value="manual">Manual</option>
                        </select>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Sort order' : 'Ordre' }}</span>
                        <input class="admin-input" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', 10) }}">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Countries' : 'Pays' }}</span>
                        <input class="admin-input" name="countries" value="{{ old('countries', 'FR, BE, LU') }}">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Currencies' : 'Devises' }}</span>
                        <input class="admin-input" name="currencies" value="{{ old('currencies', 'EUR') }}">
                    </label>

                    <label class="space-y-2 lg:col-span-2">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Description' : 'Description' }}</span>
                        <input class="admin-input" name="description[fr]" value="{{ old('description.fr') }}" placeholder="{{ $isEnglish ? 'Optional customer-facing note' : 'Note optionnelle affichee au client' }}">
                    </label>

                    <div class="lg:col-span-2 border-t border-leaf/10 pt-5 dark:border-white/10" x-show="provider === 'stripe'">
                        <h3 class="text-lg font-black text-ink dark:text-cream">Stripe</h3>
                        <p class="mt-2 text-xs font-semibold leading-5 text-cocoa/55 dark:text-cream/55">pk_test_..., sk_test_... ou rk_test_..., whsec_...</p>
                    </div>
                    <label class="space-y-2" x-show="provider === 'stripe'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Publishable key</span>
                        <input class="admin-input" name="credentials[publishable_key]" x-bind:disabled="provider !== 'stripe'" x-bind:required="provider === 'stripe'" value="{{ old('credentials.publishable_key') }}">
                    </label>
                    <label class="space-y-2" x-show="provider === 'stripe'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Secret key</span>
                        <input class="admin-input" name="credentials[secret_key]" type="password" x-bind:disabled="provider !== 'stripe'" value="{{ old('credentials.secret_key') }}">
                    </label>
                    <label class="space-y-2" x-show="provider === 'stripe'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Restricted key</span>
                        <input class="admin-input" name="credentials[restricted_key]" type="password" x-bind:disabled="provider !== 'stripe'" value="{{ old('credentials.restricted_key') }}">
                    </label>
                    <label class="space-y-2" x-show="provider === 'stripe'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Webhook secret</span>
                        <input class="admin-input" name="credentials[webhook_signing_secret]" type="password" x-bind:disabled="provider !== 'stripe'" value="{{ old('credentials.webhook_signing_secret') }}">
                    </label>

                    <div class="lg:col-span-2 border-t border-leaf/10 pt-5 dark:border-white/10" x-show="provider === 'paypal'">
                        <h3 class="text-lg font-black text-ink dark:text-cream">PayPal</h3>
                        <p class="mt-2 text-xs font-semibold leading-5 text-cocoa/55 dark:text-cream/55">{{ $isEnglish ? 'Client ID, secret and optional webhook ID from PayPal Developer.' : 'Client ID, secret et webhook ID optionnel depuis PayPal Developer.' }}</p>
                    </div>
                    <label class="space-y-2" x-show="provider === 'paypal'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Client ID</span>
                        <input class="admin-input" name="credentials[client_id]" x-bind:disabled="provider !== 'paypal'" x-bind:required="provider === 'paypal'" value="{{ old('credentials.client_id') }}">
                    </label>
                    <label class="space-y-2" x-show="provider === 'paypal'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Client secret</span>
                        <input class="admin-input" name="credentials[client_secret]" type="password" x-bind:disabled="provider !== 'paypal'" x-bind:required="provider === 'paypal'" value="{{ old('credentials.client_secret') }}">
                    </label>
                    <label class="space-y-2" x-show="provider === 'paypal'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Webhook ID</span>
                        <input class="admin-input" name="credentials[webhook_id]" x-bind:disabled="provider !== 'paypal'" value="{{ old('credentials.webhook_id') }}">
                    </label>
                    <label class="space-y-2" x-show="provider === 'paypal'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Merchant ID</span>
                        <input class="admin-input" name="credentials[merchant_id]" x-bind:disabled="provider !== 'paypal'" value="{{ old('credentials.merchant_id') }}">
                    </label>

                    <div class="lg:col-span-2 border-t border-leaf/10 pt-5 dark:border-white/10" x-show="provider === 'bank_transfer'">
                        <h3 class="text-lg font-black text-ink dark:text-cream">{{ $isEnglish ? 'Bank transfer' : 'Virement bancaire' }}</h3>
                    </div>
                    <label class="space-y-2" x-show="provider === 'bank_transfer'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Account holder' : 'Titulaire' }}</span>
                        <input class="admin-input" name="credentials[account_holder]" x-bind:disabled="provider !== 'bank_transfer'" x-bind:required="provider === 'bank_transfer'" value="{{ old('credentials.account_holder') }}">
                    </label>
                    <label class="space-y-2" x-show="provider === 'bank_transfer'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">IBAN</span>
                        <input class="admin-input" name="credentials[iban]" x-bind:disabled="provider !== 'bank_transfer'" x-bind:required="provider === 'bank_transfer'" value="{{ old('credentials.iban') }}">
                    </label>
                    <label class="space-y-2" x-show="provider === 'bank_transfer'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">BIC</span>
                        <input class="admin-input" name="credentials[bic]" x-bind:disabled="provider !== 'bank_transfer'" value="{{ old('credentials.bic') }}">
                    </label>
                    <label class="space-y-2" x-show="provider === 'bank_transfer'">
                        <span class="text-xs font-black uppercase tracking-wide text-cocoa/50">{{ $isEnglish ? 'Bank name' : 'Banque' }}</span>
                        <input class="admin-input" name="credentials[bank_name]" x-bind:disabled="provider !== 'bank_transfer'" value="{{ old('credentials.bank_name') }}">
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-leaf/10 pt-5 dark:border-white/10">
                    <button type="button" class="admin-btn-secondary" onclick="document.getElementById('payment-create-modal').close()">{{ $isEnglish ? 'Cancel' : 'Annuler' }}</button>
                    <button type="submit" class="admin-btn">{{ $isEnglish ? 'Save method' : 'Enregistrer le mode' }}</button>
                </div>
            </form>
        </div>
    </dialog>
@endsection
