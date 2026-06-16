@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('page_title', 'Utilisateurs')
@section('page_subtitle', 'Clients, comptes staff et roles associes depuis l API.')

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
                        <h2 class="text-xl font-black text-ink dark:text-cream">Comptes</h2>
                        <p class="text-sm text-cocoa/55 dark:text-cream/55">{{ count($rows) }} resultat(s) affiche(s).</p>
                    </div>
                    <span class="admin-pill">API utilisateurs</span>
                </div>

                <div class="divide-y divide-leaf/10 dark:divide-white/10">
                    @forelse($rows as $user)
                        @php
                            $status = $user['status'] ?? 'active';
                            $userId = $user['id'] ?? null;
                            $userRoles = collect($user['roles'] ?? [])
                                ->map(fn ($role) => is_array($role) ? ($role['name'] ?? null) : $role)
                                ->filter()
                                ->values();
                        @endphp
                        <article class="grid gap-3 p-4 transition hover:bg-linen dark:hover:bg-white/5 lg:grid-cols-[minmax(0,1.2fr)_220px_130px_120px_180px] lg:items-center">
                            <div class="min-w-0">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-forest text-sm font-black uppercase text-white dark:bg-meadow dark:text-ink">
                                        {{ Str::of($user['name'] ?? $user['email'] ?? 'U')->substr(0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="truncate font-black text-ink dark:text-cream">{{ $user['name'] ?? 'Utilisateur sans nom' }}</h3>
                                        <p class="truncate text-sm text-cocoa/55 dark:text-cream/55">{{ $user['email'] ?? 'Email non renseigne' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($userRoles as $roleName)
                                    <span class="rounded-full bg-linen px-2.5 py-1 text-xs font-bold text-cocoa/70 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/70 dark:ring-white/10">{{ $roleName }}</span>
                                @empty
                                    <span class="rounded-full bg-linen px-2.5 py-1 text-xs font-bold text-cocoa/50 dark:bg-white/10 dark:text-cream/50">Sans role</span>
                                @endforelse
                            </div>
                            <div>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $statusClasses[$status] ?? 'bg-linen text-cocoa/60 dark:bg-white/10 dark:text-cream/60' }}">{{ $statusLabels[$status] ?? $status }}</span>
                            </div>
                            <div class="text-sm font-bold text-cocoa/65 dark:text-cream/65">{{ $user['country_code'] ?? 'N/A' }}</div>
                            <div class="flex justify-end gap-2">
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
                        <div class="p-8 text-center text-sm text-cocoa/55 dark:text-cream/55">Aucun utilisateur ne correspond aux filtres.</div>
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
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-meadow">Controle</p>
                <h2 class="mt-2 text-lg font-black">Roles et suspensions</h2>
                <p class="mt-2 text-sm leading-6 text-white/70">Les changements sensibles passent par confirmation modale.</p>
            </div>
        </aside>
    </section>
@endsection

@push('admin_modals')
    <dialog id="user-create-modal" class="admin-dialog admin-dialog-wide" @if(session('admin_modal') === 'user-create') data-open-on-load @endif>
        <form method="POST" action="{{ route('admin.users.store', ['locale' => $locale]) }}" class="admin-modal-card">
            @csrf
            <div class="flex items-start justify-between border-b border-leaf/10 p-5 dark:border-white/10 sm:p-6">
                <div>
                    <p class="admin-kicker">Utilisateur</p>
                    <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">Inviter un membre</h2>
                </div>
                <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                </button>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                @if(session('admin_modal') === 'user-create' && $errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700 sm:col-span-2">{{ $errors->first() }}</div>
                @endif
                <label class="block">
                    <span class="admin-kicker mb-2 block">Prenom</span>
                    <input name="first_name" value="{{ old('first_name') }}" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Nom</span>
                    <input name="last_name" value="{{ old('last_name') }}" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Email</span>
                    <input name="email" value="{{ old('email') }}" type="email" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Telephone</span>
                    <input name="phone" value="{{ old('phone') }}" class="admin-input">
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Mot de passe</span>
                    <input name="password" type="password" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Confirmation</span>
                    <input name="password_confirmation" type="password" class="admin-input" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Pays</span>
                    <input name="country_code" value="{{ old('country_code', 'FR') }}" maxlength="2" class="admin-input uppercase" required>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Statut</span>
                    <select name="status" class="admin-select">
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'active') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Langue</span>
                    <select name="preferred_locale" class="admin-select">
                        <option value="fr" @selected(old('preferred_locale', $locale) === 'fr')>FR</option>
                        <option value="en" @selected(old('preferred_locale', $locale) === 'en')>EN</option>
                    </select>
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Poste</span>
                    <input name="position" value="{{ old('position') }}" class="admin-input">
                </label>
                <div class="sm:col-span-2">
                    <span class="admin-kicker mb-2 block">Roles</span>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach($roleRows as $role)
                            @php $roleName = is_array($role) ? ($role['name'] ?? '') : $role; @endphp
                            @continue(! $roleName)
                            <label class="flex items-center gap-3 rounded-xl border border-leaf/10 bg-linen px-3 py-2 dark:border-white/10 dark:bg-white/5">
                                <input type="checkbox" name="roles[]" value="{{ $roleName }}" @checked(in_array($roleName, old('roles', []), true)) class="h-4 w-4 rounded border-leaf/20 text-leaf focus:ring-leaf">
                                <span class="text-sm font-bold">{{ $roleName }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <label class="block sm:col-span-2">
                    <span class="admin-kicker mb-2 block">Notes admin</span>
                    <textarea name="admin_notes" class="admin-textarea">{{ old('admin_notes') }}</textarea>
                </label>
            </div>
            <div class="flex justify-end gap-3 border-t border-leaf/10 p-5 dark:border-white/10 sm:p-6">
                <button type="button" data-dialog-close class="admin-btn-secondary">Annuler</button>
                <button class="admin-btn">Creer le compte</button>
            </div>
        </form>
    </dialog>

    @foreach($rows as $user)
        @php
            $userId = $user['id'] ?? null;
            $status = $user['status'] ?? 'active';
            $userRoles = collect($user['roles'] ?? [])
                ->map(fn ($role) => is_array($role) ? ($role['name'] ?? null) : $role)
                ->filter()
                ->values();
        @endphp
        @continue(! $userId)

        <dialog id="user-show-{{ $userId }}" class="admin-dialog">
            <div class="admin-modal-card p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="admin-kicker">Utilisateur</p>
                        <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $user['name'] ?? 'Utilisateur' }}</h2>
                        <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">{{ $user['email'] ?? '-' }}</p>
                    </div>
                    <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                    </button>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="admin-panel p-4"><p class="admin-kicker">Statut</p><p class="mt-2 text-lg font-black">{{ $statusLabels[$status] ?? $status }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Pays</p><p class="mt-2 text-lg font-black">{{ $user['country_code'] ?? 'N/A' }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Langue</p><p class="mt-2 text-lg font-black">{{ $user['preferred_locale'] ?? app()->getLocale() }}</p></div>
                </div>
                <div class="mt-5 admin-panel p-4">
                    <p class="admin-kicker">Roles</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse($userRoles as $roleName)
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-cocoa ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream dark:ring-white/10">{{ $roleName }}</span>
                        @empty
                            <span class="text-sm text-cocoa/55 dark:text-cream/55">Aucun role.</span>
                        @endforelse
                    </div>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="admin-panel p-4"><p class="admin-kicker">Telephone</p><p class="mt-2 text-sm font-bold">{{ $user['phone'] ?? 'Non renseigne' }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Timezone</p><p class="mt-2 text-sm font-bold">{{ $user['timezone'] ?? 'Non renseignee' }}</p></div>
                </div>
            </div>
        </dialog>

        <dialog id="user-roles-{{ $userId }}" class="admin-dialog" @if(session('admin_modal') === "user-roles-{$userId}") data-open-on-load @endif>
            <form method="POST" action="{{ route('admin.users.roles', ['locale' => $locale, 'user' => $userId]) }}" class="admin-modal-card p-5 sm:p-6">
                @csrf
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="admin-kicker">Roles</p>
                        <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $user['name'] ?? $user['email'] ?? 'Utilisateur' }}</h2>
                    </div>
                    <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                    </button>
                </div>
                <div class="mt-5 grid gap-2 sm:grid-cols-2">
                    @foreach($roleRows as $role)
                        @php $roleName = is_array($role) ? ($role['name'] ?? '') : $role; @endphp
                        @continue(! $roleName)
                        <label class="flex items-center gap-3 rounded-xl border border-leaf/10 bg-linen px-3 py-2 dark:border-white/10 dark:bg-white/5">
                            <input type="checkbox" name="roles[]" value="{{ $roleName }}" @checked($userRoles->contains($roleName)) class="h-4 w-4 rounded border-leaf/20 text-leaf focus:ring-leaf">
                            <span class="text-sm font-bold">{{ $roleName }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" data-dialog-close class="admin-btn-secondary">Annuler</button>
                    <button class="admin-btn">Enregistrer</button>
                </div>
            </form>
        </dialog>

        <dialog id="user-suspend-{{ $userId }}" class="admin-dialog" @if(session('admin_modal') === "user-suspend-{$userId}") data-open-on-load @endif>
            <form method="POST" action="{{ route('admin.users.suspend', ['locale' => $locale, 'user' => $userId]) }}" class="admin-modal-card p-5 sm:p-6">
                @csrf
                <p class="admin-kicker">Suspension</p>
                <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">Suspendre ce compte</h2>
                <p class="mt-2 text-sm leading-6 text-cocoa/60 dark:text-cream/60">{{ $user['name'] ?? $user['email'] ?? 'Utilisateur' }}</p>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" data-dialog-close class="admin-btn-secondary">Annuler</button>
                    <button class="admin-btn-danger">Suspendre</button>
                </div>
            </form>
        </dialog>
    @endforeach
@endpush
