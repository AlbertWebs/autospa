<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Integrations\Sms\RebueTextSmsDriver;
use App\Integrations\Sms\SmsStubDriver;
use App\Models\Branch;
use App\Models\Integration;
use App\Models\Role;
use App\Models\Scopes\BranchScope;
use App\Models\Setting;
use App\Models\User;
use App\Services\IntegrationService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationSmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_integration_service_uses_rebuetext_when_sms_integration_is_enabled(): void
    {
        Integration::query()->create([
            'provider' => 'sms',
            'driver' => 'rebuetext',
            'is_enabled' => true,
            'credentials' => ['access_token' => 'db-token'],
            'settings' => ['sender_id' => 'AUTOSPA'],
        ]);

        $driver = app(IntegrationService::class)->sms();

        $this->assertInstanceOf(RebueTextSmsDriver::class, $driver);
    }

    public function test_integration_service_falls_back_to_stub_when_sms_integration_disabled(): void
    {
        config(['integrations.sms.driver' => 'stub']);

        Integration::query()->create([
            'provider' => 'sms',
            'driver' => 'rebuetext',
            'is_enabled' => false,
            'credentials' => ['access_token' => 'db-token'],
            'settings' => ['sender_id' => 'AUTOSPA'],
        ]);

        $driver = app(IntegrationService::class)->sms();

        $this->assertInstanceOf(SmsStubDriver::class, $driver);
    }

    public function test_integration_service_uses_env_rebuetext_when_driver_configured(): void
    {
        config([
            'integrations.sms.driver' => 'rebuetext',
            'integrations.sms.rebuetext.access_token' => 'env-token',
            'integrations.sms.rebuetext.sender_id' => 'AUTOSPA',
        ]);

        $driver = app(IntegrationService::class)->sms();

        $this->assertInstanceOf(RebueTextSmsDriver::class, $driver);
    }

    public function test_admin_can_update_sms_integration_settings(): void
    {
        $branch = Branch::query()->firstOrFail();
        $adminRole = Role::query()->where('slug', RoleSlug::SuperAdmin->value)->firstOrFail();
        $admin = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($adminRole);

        Integration::query()->withoutGlobalScope(BranchScope::class)->create([
            'provider' => 'sms',
            'driver' => 'stub',
            'is_enabled' => false,
            'credentials' => null,
            'settings' => ['sender_id' => 'AUTOSPA'],
        ]);

        session(['current_branch_id' => $branch->id]);

        $this->actingAs($admin)->put(route('settings.integrations.update'), [
            'integrations' => [
                'sms' => [
                    'enabled' => true,
                    'driver' => 'rebuetext',
                    'access_token' => 'new-token',
                    'sender_id' => 'AUTOSPA',
                ],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $integration = Integration::query()->withoutGlobalScope(BranchScope::class)->where('provider', 'sms')->first();

        $this->assertTrue($integration->is_enabled);
        $this->assertSame('rebuetext', $integration->driver);
        $this->assertSame('new-token', $integration->credentials['access_token']);
        $this->assertSame('AUTOSPA', Setting::getValue('sms', 'sender_id'));
    }
}
