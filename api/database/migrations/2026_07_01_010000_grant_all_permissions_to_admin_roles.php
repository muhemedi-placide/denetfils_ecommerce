<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::findOrCreate('carts.view', 'web');
        Permission::findOrCreate('carts.manage', 'web');

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->get();

        foreach (['admin', 'super_admin'] as $roleName) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['carts.view', 'carts.manage'])
            ->get()
            ->each
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
