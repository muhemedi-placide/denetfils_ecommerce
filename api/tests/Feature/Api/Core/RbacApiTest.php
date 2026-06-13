<?php

namespace Tests\Feature\Api\Core;

use App\Models\AuditLog;
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
        $customer = User::factory()->create();
        $customer->assignRole('customer');
        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/admin/users')->assertForbidden();
    }

    public function test_catalog_manager_cannot_assign_roles(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('catalog_manager');
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
}
