<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Integrations\Mpesa\DarajaMpesaDriver;
use App\Integrations\Mpesa\MpesaStubDriver;
use App\Models\Branch;
use App\Models\Integration;
use App\Models\Role;
use App\Models\Scopes\BranchScope;
use App\Models\User;
use App\Services\IntegrationService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationMpesaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_integration_service_uses_daraja_when_mpesa_integration_is_enabled(): void
    {
        Integration::query()->withoutGlobalScope(BranchScope::class)->create([
            'branch_id' => null,
            'provider' => 'mpesa',
            'driver' => 'daraja',
            'is_enabled' => true,
            'credentials' => [
                'consumer_key' => 'key',
                'consumer_secret' => 'secret',
                'shortcode' => '174379',
                'passkey' => 'passkey',
                'initiator_name' => 'testapi',
                'security_credential' => 'credential',
            ],
            'settings' => [
                'base_url' => 'https://sandbox.safaricom.co.ke',
                'queue_timeout_url' => 'https://example.com/timeout',
                'result_url' => 'https://example.com/result',
                'balance_result_url' => 'https://example.com/balance/result',
                'balance_timeout_url' => 'https://example.com/balance/timeout',
            ],
        ]);

        $driver = app(IntegrationService::class)->mpesa();

        $this->assertInstanceOf(DarajaMpesaDriver::class, $driver);
    }

    public function test_integration_service_falls_back_to_stub_when_mpesa_disabled(): void
    {
        Integration::query()->withoutGlobalScope(BranchScope::class)->create([
            'branch_id' => null,
            'provider' => 'mpesa',
            'driver' => 'daraja',
            'is_enabled' => false,
            'credentials' => ['consumer_key' => 'key'],
            'settings' => ['base_url' => 'https://sandbox.safaricom.co.ke'],
        ]);

        $driver = app(IntegrationService::class)->mpesa();

        $this->assertInstanceOf(MpesaStubDriver::class, $driver);
    }

    public function test_admin_can_update_mpesa_integration_settings(): void
    {
        $branch = Branch::query()->firstOrFail();
        $adminRole = Role::query()->where('slug', RoleSlug::SuperAdmin->value)->firstOrFail();
        $admin = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($adminRole);

        Integration::query()->withoutGlobalScope(BranchScope::class)->create([
            'provider' => 'mpesa',
            'driver' => 'stub',
            'is_enabled' => false,
            'credentials' => null,
            'settings' => null,
        ]);

        session(['current_branch_id' => $branch->id]);

        $this->actingAs($admin)->put(route('settings.integrations.update'), [
            'integrations' => [
                'mpesa' => [
                    'enabled' => true,
                    'driver' => 'daraja',
                    'consumer_key' => 'new-key',
                    'consumer_secret' => 'new-secret',
                    'shortcode' => '174379',
                    'passkey' => 'new-passkey',
                    'initiator_name' => 'apiuser',
                    'security_credential' => 'secure-value',
                    'base_url' => 'https://sandbox.safaricom.co.ke',
                    'result_url' => 'https://example.com/mpesa/result',
                    'queue_timeout_url' => 'https://example.com/mpesa/timeout',
                    'balance_result_url' => 'https://example.com/mpesa/balance/result',
                    'balance_timeout_url' => 'https://example.com/mpesa/balance/timeout',
                ],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $integration = Integration::query()->withoutGlobalScope(BranchScope::class)->where('provider', 'mpesa')->first();

        $this->assertTrue($integration->is_enabled);
        $this->assertSame('daraja', $integration->driver);
        $this->assertSame('new-key', $integration->credentials['consumer_key']);
        $this->assertSame('apiuser', $integration->credentials['initiator_name']);
        $this->assertSame('https://example.com/mpesa/result', $integration->settings['result_url']);
    }
}
