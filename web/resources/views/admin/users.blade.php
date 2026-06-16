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
                        <h2 class="text-xl font-black text-ink dark:text-cream">Liste des comptes</h2>
                        <p class="text-sm text-cocoa/55 dark:text-cream/55">{{ count($rows) }} resultat(s) affiche(s).</p>
                    </div>
                    <span class="admin-pill">Table API utilisateurs</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">Utilisateur</th>
                                <th class="px-4 py-3">Roles</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3">Pays</th>
                                <th class="px-4 py-3">Langue</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $user)
                                @php
                                    $status = $user['status'] ?? 'active';
                                    $userId = $user['id'] ?? null;
                                    $userRoles = collect($user['roles'] ?? [])->map(fn ($role) => is_array($role) ? ($role['name'] ?? null) : $role)->filter()->values();
                                @endphp
                                <tr class="transition hover:bg-linen dark:hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-forest text-xs font-black uppercase text-white dark:bg-meadow dark:text-ink">{{ Str::of($user['name'] ?? $user['email'] ?? 'U')->substr(0, 2) }}</div>
                                            <div class="min-w-0">
                                                <p class="truncate font-black text-ink dark:text-cream">{{ $user['name'] ?? 'Utilisateur sans nom' }}</p>
                                                <p class="truncate text-sm text-cocoa/55 dark:text-cream/55">{{ $user['email'] ?? 'Email non renseigne' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse($userRoles as $roleName)
                                                <span class="rounded-full bg-linen px-2.5 py-1 text-xs font-bold text-cocoa/70 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/70 dark:ring-white/10">{{ $roleName }}</span>
                                            @empty
                                                <span class="rounded-full bg-linen px-2.5 py-1 text-xs font-bold text-cocoa/50 dark:bg-white/10 dark:text-cream/50">Sans role</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $statusClasses[$status] ?? 'bg-linen text-cocoa/60 dark:bg-white/10 dark:text-cream/60' }}">{{ $statusLabels[$status] ?? $status }}</span></td>
                                    <td class="px-4 py-3 text-sm font-bold text-cocoa/65 dark:text-cream/65">{{ $user['country_code'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-cocoa/65 dark:text-cream/65">{{ strtoupper($user['preferred_locale'] ?? $locale) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            @if ($userId)
                                                <button type="button" data-dialog-target="user-show-{{ $userId }}" class="admin-btn-secondary min-h-0 px-3 py-2 text-xs">Voir</button>
                                                <button type="button" data-dialog-target="user-roles-{{ $userId }}" class="admin-btn-secondary min-h-0 px-3 py-2 text-xs">Roles</button>
                                                @if ($status !== 'suspended')
                                                    <button type="button" data-dialog-target="user-suspend-{{ $userId }}" class="admin-btn-danger min-h-0 px-3 py-2 text-xs">Suspendre</button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-cocoa/55 dark:text-cream/55">Aucun utilisateur ne correspond aux filtres.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside class="space-y-5">
            <div class="admin-card p-4"><div class="flex items-center justify-between gap-3"><h2 class="text-lg font-black text-ink dark:text-cream">Roles disponibles</h2><span class="admin-pill">{{ count($roleRows) }}</span></div><div class="mt-4 space-y-3">@forelse($roleRows as $role)@php $roleName = is_array($role) ? ($role['name'] ?? 'Role') : $role; @endphp<div class="rounded-xl bg-linen p-3 dark:bg-white/5"><div class="flex items-center justify-between gap-3"><p class="font-black text-ink dark:text-cream">{{ $roleName ?: 'Role' }}</p><span class="rounded-full bg-white px-2 py-1 text-xs font-bold text-cocoa/55 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/55 dark:ring-white/10">{{ is_array($role) ? count($role['permissions'] ?? []) : 0 }} droits</span></div></div>@empty<p class="text-sm text-cocoa/55 dark:text-cream/55">Aucun role retourne par l API.</p>@endforelse</div></div>
            <div class="rounded-2xl border border-leaf/10 bg-forest p-5 text-white shadow-sm dark:border-white/10 dark:bg-white/5"><p class="text-xs font-bold uppercase tracking-[0.18em] text-meadow">Controle</p><h2 class="mt-2 text-lg font-black">Roles et suspensions</h2><p class="mt-2 text-sm leading-6 text-white/70">Les changements sensibles passent par confirmation modale.</p></div>
        </aside>
    </section>
@endsection

@push('admin_modals')
    {{-- Modales a restaurer dans une passe separee si necessaire. --}}
@endpush
