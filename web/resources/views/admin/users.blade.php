@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('page_title', 'Utilisateurs')
@section('page_subtitle', 'Cartes de visite des comptes, roles et provenance d acquisition.')

@php
    $rows = data_get($users, 'data', []);
    $roleRows = data_get($roles, 'data', []);
    $statusLabels = [
        'active' => 'Actif',
        'pending' => 'En attente',
        'suspended' => 'Suspendu',
    ];
    $statusClasses = [
        'active' => 'bg-mint text-leaf dark:bg-meadow/15 dark:text-meadow',
        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-300/15 dark:text-amber-200',
        'suspended' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-200',
    ];
@endphp

@section('content')
    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-5">
            <div class="admin-card p-4 sm:p-5">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="admin-kicker">Comptes</p>
                        <h2 class="mt-2 admin-heading">Recherche utilisateurs</h2>
                    </div>
                    <button type="button" data-dialog-target="user-create-modal" class="admin-btn">Inviter un membre</button>
                </div>

                <form method="GET" class="grid gap-3 md:grid-cols-[minmax(0,1.3fr)_160px_160px_140px_auto]">
                    <label class="block">
                        <span class="admin-kicker mb-2 block">Recherche</span>
                        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom, email, telephone..." class="admin-input">
                    </label>
                    <label class="block">
                        <span class="admin-kicker mb-2 block">Statut</span>
                        <select name="status" class="admin-select">
                            <option value="">Tous</option>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="admin-kicker mb-2 block">Role</span>
                        <select name="role" class="admin-select">
                            <option value="">Tous</option>
                            @foreach($roleRows as $role)
                                @php $roleName = is_array($role) ? ($role['name'] ?? '') : $role; @endphp
                                <option value="{{ $roleName }}" @selected(($filters['role'] ?? '') === $roleName)>{{ $roleName ?: 'Role' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="admin-kicker mb-2 block">Pays</span>
                        <input name="country_code" value="{{ $filters['country_code'] ?? '' }}" placeholder="FR" class="admin-input uppercase">
                    </label>
                    <button class="admin-btn self-end">Filtrer</button>
                </form>
            </div>

            @if(!data_get($users, 'ok', true))
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">{{ data_get($users, 'message', 'Impossible de charger les utilisateurs pour le moment.') }}</div>
            @endif

            <div class="admin-card overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-leaf/10 p-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-ink dark:text-cream">Cartes de visite utilisateurs</h2>
                        <p class="text-sm text-cocoa/55 dark:text-cream/55">{{ count($rows) }} resultat(s) affiche(s). Les champs provenance sont prevus pour la future API acquisition.</p>
                    </div>
                    <span class="admin-pill">API utilisateurs</span>
                </div>

                <div class="grid gap-4 p-4 xl:grid-cols-2">
                    @forelse($rows as $user)
                        @php
                            $status = $user['status'] ?? 'active';
                            $userId = $user['id'] ?? null;
                            $userRoles = collect($user['roles'] ?? [])
                                ->map(fn ($role) => is_array($role) ? ($role['name'] ?? null) : $role)
                                ->filter()
                                ->values();
                            $source = data_get($user, 'acquisition.source')
                                ?? data_get($user, 'source.name')
                                ?? $user['source']
                                ?? 'Source inconnue';
                            $platform = data_get($user, 'acquisition.platform')
                                ?? data_get($user, 'platform.name')
                                ?? $user['platform']
                                ?? 'Plateforme non renseignee';
                            $channel = data_get($user, 'acquisition.channel')
                                ?? $user['channel']
                                ?? 'Canal non renseigne';
                            $campaign = data_get($user, 'acquisition.campaign')
                                ?? $user['campaign']
                                ?? 'Campagne non rattachee';
                            $firstTouch = data_get($user, 'acquisition.first_touch_at')
                                ?? $user['created_at']
                                ?? 'Date non disponible';
                            $lastTouch = data_get($user, 'acquisition.last_touch_at')
                                ?? $user['updated_at']
                                ?? 'Non disponible';
                            $phone = $user['phone'] ?? 'Telephone non renseigne';
                            $localeLabel = strtoupper($user['preferred_locale'] ?? app()->getLocale());
                            $country = strtoupper($user['country_code'] ?? 'N/A');
                            $initials = Str::of($user['name'] ?? $user['email'] ?? 'U')->substr(0, 2);
                        @endphp
                        <article class="group relative overflow-hidden rounded-2xl border border-leaf/10 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-leaf/30 hover:shadow-xl dark:border-white/10 dark:bg-white/5">
                            <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-mint/60 transition group-hover:bg-meadow/40 dark:bg-white/5"></div>
                            <div class="relative flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-forest text-base font-black uppercase text-white shadow-sm dark:bg-meadow dark:text-ink">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-lg font-black text-ink dark:text-cream">{{ $user['name'] ?? 'Utilisateur sans nom' }}</h3>
                                        <p class="truncate text-sm font-semibold text-cocoa/55 dark:text-cream/55">{{ $user['email'] ?? 'Email non renseigne' }}</p>
                                        <p class="mt-1 text-xs font-bold text-cocoa/45 dark:text-cream/45">{{ $phone }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 rounded-full px-2.5 py-1 text-xs font-black {{ $statusClasses[$status] ?? 'bg-linen text-cocoa/60 dark:bg-white/10 dark:text-cream/60' }}">{{ $statusLabels[$status] ?? $status }}</span>
                            </div>

                            <div class="relative mt-4 flex flex-wrap gap-1.5">
                                @forelse($userRoles as $roleName)
                                    <span class="rounded-full bg-linen px-2.5 py-1 text-xs font-bold text-cocoa/70 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/70 dark:ring-white/10">{{ $roleName }}</span>
                                @empty
                                    <span class="rounded-full bg-linen px-2.5 py-1 text-xs font-bold text-cocoa/50 dark:bg-white/10 dark:text-cream/50">Sans role</span>
                                @endforelse
                            </div>

                            <div class="relative mt-4 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-xl bg-linen p-3 ring-1 ring-leaf/10 dark:bg-white/5 dark:ring-white/10">
                                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-cocoa/45 dark:text-cream/45">Pays</p>
                                    <p class="mt-1 text-sm font-black text-ink dark:text-cream">{{ $country }}</p>
                                </div>
                                <div class="rounded-xl bg-linen p-3 ring-1 ring-leaf/10 dark:bg-white/5 dark:ring-white/10">
                                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-cocoa/45 dark:text-cream/45">Langue</p>
                                    <p class="mt-1 text-sm font-black text-ink dark:text-cream">{{ $localeLabel }}</p>
                                </div>
                                <div class="rounded-xl bg-linen p-3 ring-1 ring-leaf/10 dark:bg-white/5 dark:ring-white/10">
                                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-cocoa/45 dark:text-cream/45">Inscription</p>
                                    <p class="mt-1 truncate text-sm font-black text-ink dark:text-cream">{{ $firstTouch }}</p>
                                </div>
                            </div>

                            <div class="relative mt-4 rounded-2xl border border-dashed border-leaf/20 bg-mint/35 p-4 dark:border-meadow/20 dark:bg-meadow/10">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-leaf dark:text-meadow">Provenance</p>
                                        <h4 class="mt-1 text-sm font-black text-ink dark:text-cream">Source et plateforme d acquisition</h4>
                                    </div>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-leaf ring-1 ring-leaf/10 dark:bg-white/10 dark:text-meadow dark:ring-white/10">PRET API</span>
                                </div>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-cocoa/45 dark:text-cream/45">Source</p>
                                        <p class="mt-1 text-sm font-black text-ink dark:text-cream">{{ $source }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-cocoa/45 dark:text-cream/45">Plateforme</p>
                                        <p class="mt-1 text-sm font-black text-ink dark:text-cream">{{ $platform }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-cocoa/45 dark:text-cream/45">Canal</p>
                                        <p class="mt-1 text-sm font-black text-ink dark:text-cream">{{ $channel }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-cocoa/45 dark:text-cream/45">Campagne</p>
                                        <p class="mt-1 text-sm font-black text-ink dark:text-cream">{{ $campaign }}</p>
                                    </div>
                                </div>
                                <p class="mt-3 text-xs font-semibold text-cocoa/55 dark:text-cream/55">Dernier contact attribue : {{ $lastTouch }}.</p>
                            </div>

                            <div class="relative mt-4 flex flex-wrap justify-end gap-2">
                                @if ($userId)
                                    <button type="button" data-dialog-target="user-show-{{ $userId }}" class="admin-btn-secondary min-h-0 px-3 py-2 text-xs">Voir</button>
                                    <button type="button" data-dialog-target="user-roles-{{ $userId }}" class="admin-btn-secondary min-h-0 px-3 py-2 text-xs">Roles</button>
                                    @if ($status !== 'suspended')
                                        <button type="button" data-dialog-target="user-suspend-{{ $userId }}" class="admin-btn-danger min-h-0 px-3 py-2 text-xs">Suspendre</button>
                                    @endif
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="p-8 text-center text-sm text-cocoa/55 dark:text-cream/55 xl:col-span-2">Aucun utilisateur ne correspond aux filtres.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="space-y-5">
            <div class="admin-card p-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-black text-ink dark:text-cream">Roles disponibles</h2>
                    <span class="admin-pill">{{ count($roleRows) }}</span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($roleRows as $role)
                        @php $roleName = is_array($role) ? ($role['name'] ?? 'Role') : $role; @endphp
                        <div class="rounded-xl bg-linen p-3 dark:bg-white/5">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-black text-ink dark:text-cream">{{ $roleName ?: 'Role' }}</p>
                                <span class="rounded-full bg-white px-2 py-1 text-xs font-bold text-cocoa/55 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/55 dark:ring-white/10">{{ is_array($role) ? count($role['permissions'] ?? []) : 0 }} droits</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-cocoa/55 dark:text-cream/55">Aucun role retourne par l API.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-leaf/10 bg-forest p-5 text-white shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-meadow">Prochaine API</p>
                <h2 class="mt-2 text-lg font-black">Acquisition utilisateur</h2>
                <p class="mt-2 text-sm leading-6 text-white/70">La carte attendra les champs : source, plateforme, canal, campagne, first_touch_at et last_touch_at.</p>
            </div>
        </aside>
    </section>
@endsection

@push('admin_modals')
    <dialog id="user-create-modal" class="admin-dialog admin-dialog-wide" @if(session('admin_modal') === 'user-create') data-open-on-load @endif>
{{ '' }}
    </dialog>
@endpush
