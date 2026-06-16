@extends('layouts.admin')

@section('title', 'Acces et permissions')
@section('page_title', 'Acces')
@section('page_subtitle', 'Lecture claire des roles et permissions disponibles dans l API.')

@php
    $roleRows = data_get($roles, 'data', []);
    $permissionRows = collect(data_get($permissions, 'data', []))->map(function ($permission) {
        return is_array($permission) ? ($permission['name'] ?? 'permission') : $permission;
    })->filter()->values();
    $permissionGroups = $permissionRows->groupBy(function ($permission) {
        return Str::of($permission ?: 'general')->before('.')->headline()->toString();
    });
@endphp

@section('content')
    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-5">
            @if(!data_get($roles, 'ok', true) || !data_get($permissions, 'ok', true))
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">Certains droits n ont pas pu etre recuperes. Verifiez l API ou les permissions du compte connecte.</div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <div class="admin-card p-4">
                    <p class="admin-kicker">Roles</p>
                    <p class="mt-3 text-3xl font-black text-ink dark:text-cream">{{ count($roleRows) }}</p>
                    <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">Profils d acces disponibles.</p>
                </div>
                <div class="admin-card p-4">
                    <p class="admin-kicker">Permissions</p>
                    <p class="mt-3 text-3xl font-black text-ink dark:text-cream">{{ $permissionRows->count() }}</p>
                    <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">Actions autorisables par role.</p>
                </div>
                <div class="admin-card p-4">
                    <p class="admin-kicker">Groupes</p>
                    <p class="mt-3 text-3xl font-black text-ink dark:text-cream">{{ $permissionGroups->count() }}</p>
                    <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">Domaines fonctionnels.</p>
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
                    <article class="admin-card p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="admin-kicker">Role</p>
                                <h2 class="mt-1 text-xl font-black text-ink dark:text-cream">{{ $roleName ?: 'Role' }}</h2>
                            </div>
                            <span class="admin-pill">{{ $rolePermissions->count() }} droits</span>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse($rolePermissions->take(8) as $permission)
                                <span class="rounded-full bg-linen px-2.5 py-1 text-xs font-bold text-cocoa/70 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/70 dark:ring-white/10">{{ $permission }}</span>
                            @empty
                                <span class="text-sm text-cocoa/55 dark:text-cream/55">Aucune permission associee.</span>
                            @endforelse
                        </div>
                        <button type="button" data-dialog-target="role-show-{{ Str::slug($roleName) }}" class="admin-btn-secondary mt-4">Voir droits</button>
                    </article>
                @empty
                    <div class="admin-card p-8 text-center text-sm text-cocoa/55 dark:text-cream/55 lg:col-span-2">Aucun role retourne par l API.</div>
                @endforelse
            </div>
        </div>

        <aside class="space-y-5">
            <div class="admin-card p-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-black text-ink dark:text-cream">Catalogue des permissions</h2>
                    <span class="admin-pill">{{ $permissionRows->count() }}</span>
                </div>
                <div class="mt-4 space-y-4">
                    @forelse($permissionGroups as $group => $items)
                        <section class="rounded-xl bg-linen p-3 dark:bg-white/5">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="font-black text-ink dark:text-cream">{{ $group }}</h3>
                                <span class="rounded-full bg-white px-2 py-1 text-xs font-bold text-cocoa/55 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/55 dark:ring-white/10">{{ $items->count() }}</span>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach($items as $permission)
                                    <span class="rounded-full bg-white px-2 py-1 text-[11px] font-bold text-cocoa/60 ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream/60 dark:ring-white/10">{{ $permission }}</span>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <p class="text-sm text-cocoa/55 dark:text-cream/55">Aucune permission retournee par l API.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </section>
@endsection

@push('admin_modals')
    @foreach($roleRows as $role)
        @php
            $roleName = is_array($role) ? ($role['name'] ?? 'Role') : $role;
            $rolePermissions = collect(is_array($role) ? ($role['permissions'] ?? []) : [])
                ->map(fn ($permission) => is_array($permission) ? ($permission['name'] ?? null) : $permission)
                ->filter()
                ->values();
        @endphp
        <dialog id="role-show-{{ Str::slug($roleName) }}" class="admin-dialog">
            <div class="admin-modal-card p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="admin-kicker">Role</p>
                        <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $roleName }}</h2>
                    </div>
                    <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                    </button>
                </div>
                <div class="mt-5 flex flex-wrap gap-2">
                    @forelse($rolePermissions as $permission)
                        <span class="rounded-full bg-linen px-3 py-1 text-xs font-black text-cocoa ring-1 ring-leaf/10 dark:bg-white/10 dark:text-cream dark:ring-white/10">{{ $permission }}</span>
                    @empty
                        <span class="text-sm text-cocoa/55 dark:text-cream/55">Aucune permission associee.</span>
                    @endforelse
                </div>
            </div>
        </dialog>
    @endforeach
@endpush
