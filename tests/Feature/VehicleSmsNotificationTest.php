<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Enums\VehicleStatus;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleSmsNotificationService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class VehicleSmsNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_sms_service_skips_when_notifications_are_disabled(): void
    {
        Setting::setValue('sms', 'enabled', false, null, 'boolean');

        $result = app(VehicleSmsNotificationService::class)->sendVehicleCollected(
            new Customer([
                'full_name' => 'Test User',
                'phone' => '+254700000001',
            ])
        );

        $this->assertNull($result);
    }

    public function test_sms_service_skips_when_customer_has_no_phone(): void
    {
        Setting::setValue('sms', 'enabled', true, null, 'boolean');

        $result = app(VehicleSmsNotificationService::class)->sendVehicleCollected(
            new Customer([
                'full_name' => 'Test User',
            ])
        );

        $this->assertNull($result);
    }

    public function test_vehicle_ready_notification_only_sends_on_transition_to_ready_for_pickup(): void
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', RoleSlug::Manager->value)->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        $customer = Customer::factory()->create([
            'branch_id' => $branch->id,
            'full_name' => 'Ready Customer',
            'phone' => '+254700000002',
        ]);

        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KDA 123A',
            'make' => 'Toyota',
            'model' => 'Axio',
            'status' => VehicleStatus::Active,
        ]);

        $mock = Mockery::mock(VehicleSmsNotificationService::class);
        $mock->shouldReceive('sendVehicleReadyForCollection')
            ->once()
            ->withArgs(fn (Customer $notifiedCustomer, Vehicle $notifiedVehicle) => $notifiedCustomer->is($customer) && $notifiedVehicle->is($vehicle));
        $mock->shouldReceive('sendVehicleRegistered')->zeroOrMoreTimes();
        $this->app->instance(VehicleSmsNotificationService::class, $mock);

        session(['current_branch_id' => $branch->id]);

        $this->actingAs($user)->put(route('vehicles.update', $vehicle), [
            'customer_id' => $customer->id,
            'registration_number' => $vehicle->registration_number,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'status' => VehicleStatus::ReadyForPickup->value,
        ])->assertRedirect(route('vehicles.show', $vehicle));

        $vehicle->refresh();

        $mockNoRepeat = Mockery::mock(VehicleSmsNotificationService::class);
        $mockNoRepeat->shouldReceive('sendVehicleReadyForCollection')->never();
        $mockNoRepeat->shouldReceive('sendVehicleRegistered')->zeroOrMoreTimes();
        $this->app->instance(VehicleSmsNotificationService::class, $mockNoRepeat);

        $this->actingAs($user)->put(route('vehicles.update', $vehicle), [
            'customer_id' => $customer->id,
            'registration_number' => $vehicle->registration_number,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'status' => VehicleStatus::ReadyForPickup->value,
        ])->assertRedirect(route('vehicles.show', $vehicle));
    }
}
