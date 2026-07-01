@extends('layouts.admin')

@section('title', 'Acces et permissions')
@section('page_title', 'Acces et roles')
@section('page_subtitle', 'Selectionnez un role puis attribuez ses droits module par module.')

@php
    $roleRows = collect(data_get($roles, 'data', []))->values();
    $permissionRows = collect(data_get($permissions, 'data', []))
        ->map(fn ($permission) => is_array($permission) ? ($permission['name'] ?? null) : $permission)
        ->filter()
        ->values();
    $protectedRoles = ['super_admin', 'admin', 'customer'];
    $selectedRole = $roleRows->first(function ($role) use ($selectedRoleName) {
        $name = is_array($role) ? ($role['name'] ?? '') : $role;
        return $selectedRoleName !== '' && $name === $selectedRoleName;
    }) ?? $roleRows->first(function ($role) use ($protectedRoles) {
        $name = is_array($role) ? ($role['name'] ?? '') : $role;
        return ! in_array($name, $protectedRoles, true);
    }) ?? $roleRows->first();
    $selectedRoleId = is_array($selectedRole) ? ($selectedRole['id'] ?? null) : null;
    $selectedRoleName = is_array($selectedRole) ? ($selectedRole['name'] ?? '') : (string) $selectedRole;
    $selectedPermissions = collect(is_array($selectedRole) ? ($selectedRole['permissions'] ?? []) : [])
        ->map(fn ($permission) => is_array($permission) ? ($permission['name'] ?? null) : $permission)
        ->filter()
        ->values();
    $permissionsLocked = in_array($selectedRoleName, $protectedRoles, true);
    $permissionGroups = $permissionRows->groupBy(fn ($permission) => Str::before($permission, '.'));
    $preferredActions = collect(['view', 'create', 'manage', 'update', 'assign', 'suspend']);
    $permissionActions = $permissionRows
        ->map(fn ($permission) => Str::after($permission, '.'))
        ->unique()
        ->sortBy(fn ($action) => ($position = $preferredActions->search($action)) === false ? 100 : $position)
        ->values();
    $actionLabels = [
        'view' => 'Voir',
        'create' => 'Creer',
        'manage' => 'Gerer',
        'update' => 'Modifier',
        'assign' => 'Attribuer',
        'suspend' => 'Suspendre',
    ];
@endphp

@section('content')
    <section
        class="grid gap-5"
        x-data="permissionMatrix({
            endpoint: @js($selectedRoleId ? route('admin.access.permissions', ['locale' => $locale, 'role' => $selectedRoleId]) : null),
            csrf: @js(csrf_token()),
            initialPermissions: @js($selectedPermissions->values()->all()),
            locked: @js($permissionsLocked),
        })"
    >
        <div
            x-show="notice"
            x-cloak
            x-transition
            class="fixed right-5 top-5 z-[90] max-w-sm rounded-xl border px-4 py-3 text-sm font-bold shadow-2xl"
            :class="noticeType === 'success'
                ? 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-400/30 dark:bg-emerald-500/15 dark:text-emerald-100'
                : 'border-red-300 bg-red-50 text-red-800 dark:border-red-400/30 dark:bg-red-500/15 dark:text-red-100'"
            role="status"
            aria-live="polite"
        >
            <div class="flex items-start gap-3">
                <svg x-show="noticeType === 'success'" xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6" /></svg>
                <svg x-show="noticeType === 'error'" xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 8v5" /><path d="M12 17h.01" /></svg>
                <span x-text="notice"></span>
            </div>
        </div>
        @if(!data_get($roles, 'ok', true) || !data_get($permissions, 'ok', true))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">
                Certains droits n ont pas pu etre recuperes. Verifiez l API et les permissions du compte connecte.
            </div>
        @endif

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="admin-kicker inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3 4 7v6c0 5 3.4 7.6 8 8 4.6-.4 8-3 8-8V7l-8-4Z" /><path d="m9 12 2 2 4-5" /></svg>
                    Administration
                </p>
                <h2 class="mt-2 text-3xl font-black text-ink dark:text-cream">Roles et permissions</h2>
                <p class="mt-2 max-w-3xl admin-muted">Choisissez un role, cochez les actions autorisees puis enregistrez. Les comptes clients restent isoles des roles administratifs.</p>
            </div>
            <a href="{{ route('admin.users', ['locale' => $locale]) }}" class="admin-btn">
                Gerer l equipe
            </a>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <article class="admin-card p-4">
                <p class="admin-kicker">Roles</p>
                <strong class="mt-3 block text-3xl font-black">{{ $roleRows->count() }}</strong>
                <p class="mt-1 admin-muted">Profils disponibles</p>
            </article>
            <article class="admin-card p-4">
                <p class="admin-kicker">Permissions</p>
                <strong class="mt-3 block text-3xl font-black">{{ $permissionRows->count() }}</strong>
                <p class="mt-1 admin-muted">Actions configurables</p>
            </article>
            <article class="admin-card p-4">
                <p class="admin-kicker">Role selectionne</p>
                <strong class="mt-3 block truncate text-2xl font-black">{{ Str::headline($selectedRoleName ?: 'Aucun') }}</strong>
                <p class="mt-1 admin-muted">{{ $selectedPermissions->count() }} droit(s)</p>
            </article>
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
            <section class="admin-card overflow-hidden">
                <div class="flex flex-col gap-4 border-b border-leaf/10 p-4 dark:border-white/10 lg:flex-row lg:items-end lg:justify-between sm:p-5">
                    <div>
                        <p class="admin-kicker">Matrice d autorisation</p>
                        <h3 class="mt-2 text-xl font-black">{{ Str::headline($selectedRoleName ?: 'Selectionnez un role') }}</h3>
                        <p class="mt-1 admin-muted">Chaque ligne correspond a un module du back-office.</p>
                    </div>
                    <label class="block w-full lg:max-w-sm">
                        <span class="admin-kicker mb-2 flex items-center gap-2 text-leaf dark:text-meadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="15" r="4" /><path d="m11 12 8-8" /></svg>
                            Role a configurer
                        </span>
                        <select class="admin-select border-leaf dark:border-meadow" x-on:change="window.location.href = $event.target.value">
                            @foreach($roleRows as $role)
                                @php $optionRoleName = is_array($role) ? ($role['name'] ?? '') : $role; @endphp
                                <option value="{{ route('admin.access', ['locale' => $locale, 'role' => $optionRoleName]) }}" @selected($optionRoleName === $selectedRoleName)>{{ Str::headline($optionRoleName) }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                @if($selectedRoleId)
                    <div>
                        @if($permissionsLocked)
                            <div class="m-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900 dark:border-amber-400/30 dark:bg-amber-500/10 dark:text-amber-100 sm:m-5">
                                Ce role est protege. Ses permissions ne peuvent pas etre modifiees depuis cette interface.
                            </div>
                        @endif

                        <div class="overflow-x-auto">
                            <table class="admin-table min-w-[760px]">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3">Module</th>
                                        @foreach($permissionActions as $action)
                                            <th class="px-3 py-3 text-center">{{ $actionLabels[$action] ?? Str::headline($action) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissionGroups as $module => $modulePermissions)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-mint text-leaf dark:bg-meadow/15 dark:text-meadow">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3 4 7v6c0 5 3.4 7.6 8 8 4.6-.4 8-3 8-8V7l-8-4Z" /></svg>
                                                    </span>
                                                    <span>
                                                        <strong class="block">{{ Str::headline($module) }}</strong>
                                                        <small class="admin-muted">{{ $module }}</small>
                                                    </span>
                                                </div>
                                            </td>
                                            @foreach($permissionActions as $action)
                                                @php
                                                    $permissionName = $module.'.'.$action;
                                                    $permissionExists = $modulePermissions->contains($permissionName);
                                                    $checked = $selectedPermissions->contains($permissionName);
                                                @endphp
                                                <td class="px-3 py-3 text-center">
                                                    @if($permissionExists)
                                                        <label class="inline-grid {{ $permissionsLocked ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}">
                                                            <input
                                                                class="peer sr-only"
                                                                type="checkbox"
                                                                value="{{ $permissionName }}"
                                                                @checked($checked)
                                                                @disabled($permissionsLocked)
                                                                x-bind:disabled="saving || locked"
                                                                x-on:change="setPermission(@js($permissionName), $event.target.checked, $event.target)"
                                                            >
                                                            <span class="grid h-9 w-9 place-items-center rounded-lg border border-leaf/15 bg-white text-transparent transition peer-checked:border-leaf peer-checked:bg-leaf peer-checked:text-white dark:border-white/15 dark:bg-white/5 dark:peer-checked:border-meadow dark:peer-checked:bg-meadow dark:peer-checked:text-ink" :class="savingPermission === @js($permissionName) ? 'animate-pulse' : ''">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 4 4L19 6" /></svg>
                                                            </span>
                                                        </label>
                                                    @else
                                                        <span class="admin-muted">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-between gap-3 border-t border-leaf/10 p-4 dark:border-white/10 sm:p-5">
                            <p class="admin-muted">
                                <span class="font-black text-leaf dark:text-meadow">Enregistrement automatique.</span>
                                Chaque clic est applique immediatement au backend.
                            </p>
                            <span x-show="saving" x-cloak class="admin-pill animate-pulse">Mise a jour...</span>
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center admin-muted">Aucun role disponible.</div>
                @endif
            </section>

            <aside class="admin-card p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="admin-kicker">Roles</p>
                        <h3 class="mt-2 text-xl font-black">Choisir un role</h3>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-leaf dark:text-meadow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="15" r="4" /><path d="m11 12 8-8" /><path d="m15 4 5 5" /></svg>
                </div>
                <div class="mt-4 space-y-2">
                    @forelse($roleRows as $role)
                        @php
                            $roleId = is_array($role) ? ($role['id'] ?? null) : null;
                            $roleName = is_array($role) ? ($role['name'] ?? '') : $role;
                            $rolePermissions = collect(is_array($role) ? ($role['permissions'] ?? []) : []);
                            $isSelected = $roleName === $selectedRoleName;
                            $isProtected = in_array($roleName, $protectedRoles, true);
                        @endphp
                        <a href="{{ route('admin.access', ['locale' => $locale, 'role' => $roleName]) }}" class="flex items-center gap-3 rounded-xl border p-3 transition {{ $isSelected ? 'border-leaf bg-mint dark:border-meadow dark:bg-meadow/10' : 'border-leaf/10 bg-linen hover:border-leaf/30 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/25' }}">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $isSelected ? 'bg-leaf text-white dark:bg-meadow dark:text-ink' : 'bg-white text-leaf dark:bg-white/10 dark:text-meadow' }}">
                                {{ Str::of($roleName)->substr(0, 1)->upper() }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <strong class="block truncate">{{ Str::headline($roleName) }}</strong>
                                <small class="admin-muted">{{ $rolePermissions->count() }} droit(s)</small>
                            </span>
                            @if($isProtected)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="10" width="14" height="10" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
                            @endif
                        </a>
                    @empty
                        <p class="admin-muted">Aucun role retourne par l API.</p>
                    @endforelse
                </div>
            </aside>
        </div>
    </section>
@endsection

<script>
    window.permissionMatrix = function (config) {
        return {
            endpoint: config.endpoint,
            csrf: config.csrf,
            permissions: [...config.initialPermissions],
            locked: config.locked,
            saving: false,
            savingPermission: null,
            notice: '',
            noticeType: 'success',
            noticeTimer: null,

            async setPermission(permission, allowed, checkbox) {
                if (this.locked || this.saving || !this.endpoint) {
                    checkbox.checked = !allowed;
                    return;
                }

                const previous = [...this.permissions];
                this.permissions = allowed
                    ? [...new Set([...this.permissions, permission])]
                    : this.permissions.filter((item) => item !== permission);
                this.saving = true;
                this.savingPermission = permission;

                try {
                    const response = await fetch(this.endpoint, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            role_name: @js($selectedRoleName),
                            permissions: this.permissions,
                        }),
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(payload.message || 'La permission n a pas pu etre enregistree.');
                    }

                    this.showNotice(payload.message || 'Permission mise a jour automatiquement.', 'success');
                } catch (error) {
                    this.permissions = previous;
                    checkbox.checked = previous.includes(permission);
                    this.showNotice(error.message || 'Erreur pendant la mise a jour.', 'error');
                } finally {
                    this.saving = false;
                    this.savingPermission = null;
                }
            },

            showNotice(message, type) {
                this.notice = message;
                this.noticeType = type;
                window.clearTimeout(this.noticeTimer);
                this.noticeTimer = window.setTimeout(() => {
                    this.notice = '';
                }, 3500);
            },
        };
    };
</script>
