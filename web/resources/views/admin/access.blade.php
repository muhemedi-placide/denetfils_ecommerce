@extends('layouts.admin')

@section('title', 'Acces et permissions')
@section('page_title', 'Acces')
@section('page_subtitle', 'Lecture claire des roles et permissions disponibles dans l API.')

@section('content')
    @php
        $roleRows = data_get($roles, 'data', []);
        $permissionRows = collect(data_get($permissions, 'data', []))->map(function ($permission) {
            return is_array($permission) ? ($permission['name'] ?? 'permission') : $permission;
        })->filter()->values();
        $permissionGroups = $permissionRows->groupBy(function ($permission) {
            return Str::of($permission ?: 'general')->before('.')->headline()->toString();
        });
    @endphp

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-4">
            @if(!data_get($roles, 'ok', true) || !data_get($permissions, 'ok', true))
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">Certains droits n ont pas pu etre recuperes. Verifiez l API ou les permissions du compte connecte.</div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Roles</p>
                    <p class="mt-3 text-3xl font-black text-[#12210f]">{{ count($roleRows) }}</p>
                    <p class="mt-1 text-sm text-stone-500">Profils d acces disponibles.</p>
                </div>
                <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Permissions</p>
                    <p class="mt-3 text-3xl font-black text-[#12210f]">{{ $permissionRows->count() }}</p>
                    <p class="mt-1 text-sm text-stone-500">Actions autorisables par role.</p>
                </div>
                <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Groupes</p>
                    <p class="mt-3 text-3xl font-black text-[#12210f]">{{ $permissionGroups->count() }}</p>
                    <p class="mt-1 text-sm text-stone-500">Domaines fonctionnels.</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                @forelse($roleRows as $role)
                    @php
                        $roleName = is_array($role) ? ($role['name'] ?? 'Role') : $role;
                        $rolePermissions = collect(is_array($role) ? ($role['permissions'] ?? []) : [])
                            ->map(fn ($permission) => is_array($permission) ? ($permission['name'] ?? null) : $permission)
                            ->filter()
                            ->values();
                    @endphp
                    <article class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Role</p>
                                <h2 class="mt-1 text-xl font-black text-[#12210f]">{{ $roleName ?: 'Role' }}</h2>
                            </div>
                            <span class="rounded-full bg-[#f15b2a]/10 px-3 py-1 text-xs font-black text-[#f15b2a]">{{ $rolePermissions->count() }} droits</span>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse($rolePermissions as $permission)
                                <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-bold text-stone-700">{{ $permission }}</span>
                            @empty
                                <span class="text-sm text-stone-500">Aucune permission associee.</span>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-stone-200 bg-white p-8 text-center text-sm text-stone-500 shadow-sm lg:col-span-2">Aucun role retourne par l API.</div>
                @endforelse
            </div>
        </div>

        <aside class="space-y-4">
            <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                <h2 class="text-lg font-black text-[#12210f]">Catalogue des permissions</h2>
                <div class="mt-4 space-y-4">
                    @forelse($permissionGroups as $group => $items)
                        <section class="rounded-2xl bg-stone-50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="font-black text-[#12210f]">{{ $group }}</h3>
                                <span class="rounded-full bg-white px-2 py-1 text-xs font-bold text-stone-500 ring-1 ring-stone-200">{{ $items->count() }}</span>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach($items as $permission)
                                    <span class="rounded-full bg-white px-2 py-1 text-[11px] font-bold text-stone-600 ring-1 ring-stone-200">{{ $permission }}</span>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <p class="text-sm text-stone-500">Aucune permission retournee par l API.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl bg-[#12210f] p-4 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/50">Approche</p>
                <h2 class="mt-2 text-lg font-black">Lecture avant modification</h2>
                <p class="mt-2 text-sm leading-6 text-white/70">L ecran permet d auditer les droits sans risque. Les actions de modification peuvent ensuite etre ajoutees proprement avec des endpoints dedies.</p>
            </div>
        </aside>
    </section>
@endsection
