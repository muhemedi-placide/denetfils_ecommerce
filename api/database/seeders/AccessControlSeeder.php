<?php

namespace Database\Seeders;

use App\Support\CoreDefaults;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (CoreDefaults::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $rolePermissions = [
            'super_admin' => CoreDefaults::PERMISSIONS,
            'admin' => CoreDefaults::PERMISSIONS,
            'operations_manager' => [
                'users.view',
                'catalog.view',
                'orders.view',
                'orders.manage',
                'payments.view',
                'customers.view',
            ],
            'catalog_manager' => [
                'catalog.view',
                'catalog.manage',
            ],
            'support_agent' => [
                'users.view',
                'customers.view',
                'customers.manage',
                'orders.view',
            ],
            'finance_manager' => [
                'orders.view',
                'payments.view',
                'payments.manage',
                'compliance.view',
                'audit.view',
            ],
            'customer' => [],
        ];

        foreach (CoreDefaults::ROLES as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions[$roleName] ?? []);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
