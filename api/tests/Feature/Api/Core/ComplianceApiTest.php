<?php

namespace Tests\Feature\Api\Core;

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\SupportedCountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ComplianceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SupportedCountrySeeder::class, AccessControlSeeder::class]);
    }

    public function test_supported_countries_are_exposed_with_localized_names(): void
    {
        $this->getJson('/api/v1/supported-countries?locale=fr')
            ->assertOk()
            ->assertJsonFragment([
                'code' => 'FR',
                'name' => 'France',
                'currency' => 'EUR',
            ]);
    }

    public function test_current_privacy_consents_are_exposed(): void
    {
        $this->getJson('/api/v1/privacy/consents/current')
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'privacy_policy',
                'required' => true,
            ])
            ->assertJsonFragment([
                'type' => 'marketing_email',
                'required' => false,
            ]);
    }

    public function test_admin_can_read_audit_logs(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'test.audit',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/audit-logs')
            ->assertOk()
            ->assertJsonPath('data.0.action', 'test.audit');
    }
}
