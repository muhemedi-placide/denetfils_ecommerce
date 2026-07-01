<?php

namespace Tests\Feature\Api\Core;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use App\Support\CoreDefaults;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RbacApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SupportedCountrySeeder::class, AccessControlSeeder::class]);
    }

    public function test_customer_cannot_access_admin_routes(): void
    {
        $customer = Customer::factory()->create();
        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/admin/users')->assertForbidden();
    }

    public function test_catalog_manager_cannot_assign_roles(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('catalog_manager');
        $this->assertSame('catalog_manager', $manager->fresh()->role->name);
        $target = User::factory()->create();
        Sanctum::actingAs($manager);

        $this->postJson("/api/v1/admin/users/{$target->id}/roles", [
            'roles' => ['support_agent'],
        ])->assertForbidden();
    }

    public function test_admin_can_create_and_suspend_user_and_audit_is_recorded(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $created = $this->postJson('/api/v1/admin/users', [
            'first_name' => 'Support',
            'last_name' => 'Agent',
            'email' => 'support@example.com',
            'password' => 'password-secure',
            'country_code' => 'FR',
            'roles' => ['support_agent'],
            'status' => 'active',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'support@example.com')
            ->assertJsonPath('data.role', 'support_agent')
            ->assertJsonPath('data.roles.0', 'support_agent')
            ->json('data.id');

        $this->postJson("/api/v1/admin/users/{$created}/suspend")
            ->assertOk()
            ->assertJsonPath('data.status', 'suspended');

        $this->assertDatabaseHas('audit_logs', ['action' => 'users.created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'users.suspended']);
    }

    public function test_super_admin_has_all_seeded_permissions(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');
        Sanctum::actingAs($superAdmin);

        $this->assertEmpty(array_diff(
            CoreDefaults::PERMISSIONS,
            $superAdmin->getAllPermissions()->pluck('name')->all(),
        ));

        $this->getJson('/api/v1/admin/permissions')->assertOk();
    }

    public function test_admin_can_sync_permissions_for_editable_role_but_not_protected_roles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $support = \Spatie\Permission\Models\Role::findByName('support_agent', 'web');
        $protected = \Spatie\Permission\Models\Role::findByName('admin', 'web');

        Sanctum::actingAs($admin);

        $this->patchJson("/api/v1/admin/roles/{$support->id}/permissions", [
            'permissions' => ['catalog.view', 'orders.view'],
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'support_agent')
            ->assertJsonPath('data.permissions.0', 'catalog.view')
            ->assertJsonPath('data.permissions.1', 'orders.view');

        $this->assertTrue($support->fresh()->hasPermissionTo('catalog.view'));
        $this->assertFalse($support->fresh()->hasPermissionTo('customers.manage'));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'roles.permissions_updated',
            'auditable_id' => $support->id,
        ]);

        $this->patchJson("/api/v1/admin/roles/{$protected->id}/permissions", [
            'permissions' => ['catalog.view'],
        ])->assertUnprocessable();
    }
}
