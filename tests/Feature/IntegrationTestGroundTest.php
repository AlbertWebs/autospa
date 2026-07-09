<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Support\EmailSettings;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationTestGroundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'integrations.sms.driver' => 'stub',
            'integrations.mpesa.driver' => 'stub',
            'integrations.whatsapp.driver' => 'stub',
        ]);

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_admin_can_view_test_ground_page(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $response = $this->actingAs($user)->get(route('settings.test-ground.index'));

        $response->assertOk();
        $response->assertSee('M-Pesa STK Push');
        $response->assertSee('Send test B2C request');
        $response->assertSee('Request account balance');
    }

    public function test_admin_can_send_test_sms_via_stub_driver(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $response = $this->actingAs($user)->from(route('settings.test-ground.index'))->post(route('settings.test-ground.send'), [
            'channel' => 'sms',
            'recipient' => '0712345678',
            'message' => 'Hello from test ground',
        ]);

        $response->assertRedirect(route('settings.test-ground.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('test_result.details.driver', 'SmsStubDriver');
    }

    public function test_admin_can_send_test_mpesa_b2c_via_stub_driver(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $response = $this->actingAs($user)->from(route('settings.test-ground.index'))->post(route('settings.test-ground.send'), [
            'channel' => 'mpesa_b2c',
            'recipient' => '0712345678',
            'amount' => 1500,
        ]);

        $response->assertRedirect(route('settings.test-ground.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('test_result.details.driver', 'MpesaStubDriver');
    }

    public function test_admin_can_request_mpesa_balance_via_stub_driver(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $response = $this->actingAs($user)->from(route('settings.test-ground.index'))->post(route('settings.test-ground.send'), [
            'channel' => 'mpesa_balance',
        ]);

        $response->assertRedirect(route('settings.test-ground.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('test_result.details.driver', 'MpesaStubDriver');
    }

    public function test_admin_can_send_test_email_even_when_notifications_disabled(): void
    {
        EmailSettings::disable();

        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $response = $this->actingAs($user)->from(route('settings.test-ground.index'))->post(route('settings.test-ground.send'), [
            'channel' => 'email',
            'recipient' => 'test@example.com',
            'subject' => 'Test',
            'message' => 'Integration test email body',
        ]);

        $response->assertRedirect(route('settings.test-ground.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('test_result.details.driver', config('mail.default'));
    }

    public function test_user_without_settings_permission_cannot_send_tests(): void
    {
        $user = User::factory()->create([
            'branch_id' => Branch::query()->firstOrFail()->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('settings.test-ground.send'), [
            'channel' => 'sms',
            'recipient' => '0712345678',
            'message' => 'Should fail',
        ]);

        $response->assertForbidden();
    }

    protected function makeUserWithRole(RoleSlug $roleSlug): User
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', $roleSlug->value)->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
