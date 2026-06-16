@extends('layouts.admin')

@section('title', ($locale ?? 'fr') === 'en' ? 'Delivery' : 'Livraison')
@section('page_title', ($locale ?? 'fr') === 'en' ? 'Delivery' : 'Livraison')
@section('page_subtitle', ($locale ?? 'fr') === 'en' ? 'Configure carriers, zones and pickup points.' : 'Configurez les transporteurs, les zones et les points relais.')

@section('content')
    <section class="space-y-5">
        <article class="admin-card overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-leaf/10 p-5 dark:border-white/10 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                <div>
                    <p class="admin-kicker">{{ ($locale ?? 'fr') === 'en' ? 'Customize' : 'Personnaliser' }}</p>
                    <h2 class="mt-2 text-3xl font-black text-ink dark:text-cream">{{ ($locale ?? 'fr') === 'en' ? 'Carriers' : 'Transporteurs' }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ ($locale ?? 'fr') === 'en' ? 'First carrier prepared for Mondial Relay: pickup points, lockers, credentials and tracking.' : 'Premier transporteur prepare pour Mondial Relay : points relais, lockers, identifiants et suivi colis.' }}</p>
                </div>
                <button type="button" class="admin-btn" onclick="document.getElementById('carrier-create-modal').showModal()">{{ ($locale ?? 'fr') === 'en' ? 'Add a carrier' : 'Ajouter un transporteur' }}</button>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3">{{ ($locale ?? 'fr') === 'en' ? 'Carrier' : 'Transporteur' }}</th>
                            <th class="px-5 py-3">{{ ($locale ?? 'fr') === 'en' ? 'Provider' : 'Prestataire' }}</th>
                            <th class="px-5 py-3">{{ ($locale ?? 'fr') === 'en' ? 'Environment' : 'Environnement' }}</th>
                            <th class="px-5 py-3">{{ ($locale ?? 'fr') === 'en' ? 'Countries' : 'Pays' }}</th>
                            <th class="px-5 py-3">{{ ($locale ?? 'fr') === 'en' ? 'Modes' : 'Modes' }}</th>
                            <th class="px-5 py-3">{{ ($locale ?? 'fr') === 'en' ? 'Status' : 'Statut' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (data_get($carriers ?? [], 'data', []) as $carrier)
                            <tr>
                                <td class="px-5 py-4 font-black text-ink dark:text-cream">{{ data_get($carrier, 'display_name.'.($locale ?? 'fr'), data_get($carrier, 'code')) }}</td>
                                <td class="px-5 py-4">{{ data_get($carrier, 'provider_name', 'Mondial Relay') }}</td>
                                <td class="px-5 py-4"><span class="admin-pill">{{ data_get($carrier, 'environment') }}</span></td>
                                <td class="px-5 py-4">{{ implode(', ', data_get($carrier, 'countries', [])) ?: '—' }}</td>
                                <td class="px-5 py-4">{{ implode(', ', data_get($carrier, 'delivery_modes', [])) ?: '—' }}</td>
                                <td class="px-5 py-4"><span class="admin-pill">{{ data_get($carrier, 'status') }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-10 text-center text-sm font-semibold text-cocoa/55 dark:text-cream/55" colspan="6">{{ ($locale ?? 'fr') === 'en' ? 'No carrier configured yet.' : 'Aucun transporteur configure pour le moment.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <dialog id="carrier-create-modal" class="admin-dialog admin-dialog-wide">
        <div class="admin-modal-card">
            <form method="POST" action="#" class="p-5 sm:p-6">
                @csrf
                <div class="flex items-start justify-between gap-4 border-b border-leaf/10 pb-5 dark:border-white/10">
                    <div>
                        <p class="admin-kicker">Mondial Relay</p>
                        <h2 class="mt-2 text-2xl font-black text-ink dark:text-cream">{{ ($locale ?? 'fr') === 'en' ? 'Add a carrier' : 'Ajouter un transporteur' }}</h2>
                        <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ ($locale ?? 'fr') === 'en' ? 'The backend API is ready to receive carrier credentials.' : 'L API backend est prete a recevoir les identifiants du transporteur.' }}</p>
                    </div>
                    <button type="button" class="admin-icon-btn" onclick="document.getElementById('carrier-create-modal').close()">×</button>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Code</span><input class="admin-input" value="mondial_relay"></label>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Environnement</span><select class="admin-select"><option>Sandbox</option><option>Live</option></select></label>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Enseigne</span><input class="admin-input"></label>
                    <label class="space-y-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Cle privee</span><input class="admin-input" type="password"></label>
                    <label class="space-y-2 lg:col-span-2"><span class="text-xs font-black uppercase tracking-wide text-cocoa/50">Pays</span><input class="admin-input" value="FR, BE, LU, ES, NL"></label>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-leaf/10 pt-5 dark:border-white/10">
                    <button type="button" class="admin-btn-secondary" onclick="document.getElementById('carrier-create-modal').close()">{{ ($locale ?? 'fr') === 'en' ? 'Cancel' : 'Annuler' }}</button>
                    <button type="button" class="admin-btn">{{ ($locale ?? 'fr') === 'en' ? 'Save carrier' : 'Enregistrer le transporteur' }}</button>
                </div>
            </form>
        </div>
    </dialog>
@endsection
