@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('page_title', 'Utilisateurs')
@section('page_subtitle', 'Clients, comptes staff et roles associes depuis l API.')

@section('content')
    @php
        $rows = data_get($users, 'data', []);
        $roleRows = data_get($roles, 'data', []);
        $statusLabels = [
            'active' => 'Actif',
            'pending' => 'En attente',
            'suspended' => 'Suspendu',
        ];
        $statusClasses = [
            'active' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'pending' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'suspended' => 'bg-rose-100 text-rose-700 ring-rose-200',
        ];
    @endphp

    <section class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-4">
            <form method="GET" class="rounded-2xl border border-stone-200 bg-white p-3 shadow-sm">
                <div class="grid gap-3 md:grid-cols-[minmax(0,1.3fr)_160px_160px_140px_auto]">
                    <label class="block">
                        <span class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Recherche</span>
                        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom, email, telephone..." class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm outline-none transition focus:border-[#f15b2a] focus:bg-white focus:ring-4 focus:ring-orange-100">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Statut</span>
                        <select name="status" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm outline-none transition focus:border-[#f15b2a] focus:bg-white focus:ring-4 focus:ring-orange-100">
                            <option value="">Tous</option>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Role</span>
                        <select name="role" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm outline-none transition focus:border-[#f15b2a] focus:bg-white focus:ring-4 focus:ring-orange-100">
                            <option value="">Tous</option>
                            @foreach($roleRows as $role)
                                <option value="{{ $role['name'] ?? '' }}" @selected(($filters['role'] ?? '') === ($role['name'] ?? null))>{{ $role['name'] ?? 'Role' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Pays</span>
                        <input name="country_code" value="{{ $filters['country_code'] ?? '' }}" placeholder="FR" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm uppercase outline-none transition focus:border-[#f15b2a] focus:bg-white focus:ring-4 focus:ring-orange-100">
                    </label>
                    <button class="self-end rounded-xl bg-[#12210f] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1c3517]">Filtrer</button>
                </div>
            </form>

            @if(!data_get($users, 'ok', true))
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">{{ data_get($users, 'message', 'Impossible de charger les utilisateurs pour le moment.') }}</div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-stone-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-[#12210f]">Comptes</h2>
                        <p class="text-sm text-stone-500">{{ count($rows) }} resultat(s) affiche(s).</p>
                    </div>
                    <button class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-bold text-stone-500 opacity-60" disabled title="A raccorder aux mutations API">Inviter un membre</button>
                </div>

                <div class="divide-y divide-stone-100">
                    @forelse($rows as $user)
                        @php
                            $status = $user['status'] ?? 'active';
                            $userRoles = collect($user['roles'] ?? [])->pluck('name')->filter()->values();
                        @endphp
                        <article class="grid gap-3 p-4 transition hover:bg-stone-50 lg:grid-cols-[minmax(0,1.2fr)_220px_130px_120px_150px] lg:items-center">
                            <div class="min-w-0">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#12210f] text-sm font-black uppercase text-white">
                                        {{ Str::of($user['name'] ?? $user['email'] ?? 'U')->substr(0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="truncate font-black text-[#12210f]">{{ $user['name'] ?? 'Utilisateur sans nom' }}</h3>
                                        <p class="truncate text-sm text-stone-500">{{ $user['email'] ?? 'Email non renseigne' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($userRoles as $roleName)
                                    <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-bold text-stone-700">{{ $roleName }}</span>
                                @empty
                                    <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-bold text-stone-500">Sans role</span>
                                @endforelse
                            </div>
                            <div>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClasses[$status] ?? 'bg-stone-100 text-stone-600 ring-stone-200' }}">{{ $statusLabels[$status] ?? $status }}</span>
                            </div>
                            <div class="text-sm font-bold text-stone-600">{{ $user['country_code'] ?? 'N/A' }}</div>
                            <div class="text-sm text-stone-500">
                                <p>{{ $user['phone'] ?? 'Telephone absent' }}</p>
                                <p class="text-xs">{{ $user['preferred_locale'] ?? app()->getLocale() }} · {{ $user['timezone'] ?? 'timezone inconnue' }}</p>
                            </div>
                        </article>
                    @empty
                        <div class="p-8 text-center text-sm text-stone-500">Aucun utilisateur ne correspond aux filtres.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                <h2 class="text-lg font-black text-[#12210f]">Roles disponibles</h2>
                <div class="mt-4 space-y-3">
                    @forelse($roleRows as $role)
                        <div class="rounded-2xl bg-stone-50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-black text-[#12210f]">{{ $role['name'] ?? 'Role' }}</p>
                                <span class="rounded-full bg-white px-2 py-1 text-xs font-bold text-stone-500 ring-1 ring-stone-200">{{ count($role['permissions'] ?? []) }} droits</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-stone-500">Aucun role retourne par l API.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl bg-[#12210f] p-4 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/50">UX admin</p>
                <h2 class="mt-2 text-lg font-black">Priorite au controle rapide</h2>
                <p class="mt-2 text-sm leading-6 text-white/70">La page reste lisible sur mobile: filtres empiles, cartes utilisateurs compactes, et informations critiques visibles sans tableau horizontal.</p>
            </div>
        </aside>
    </section>
@endsection
