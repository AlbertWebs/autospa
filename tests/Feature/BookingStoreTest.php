<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_create_booking_and_see_it_on_index_for_scheduled_day(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KDJ 902K',
        ]);

        $scheduledAt = now()->addDay()->startOfHour();

        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'type' => BookingType::Appointment->value,
            'status' => BookingStatus::Pending->value,
            'scheduled_at' => $scheduledAt->format('Y-m-d\TH:i'),
            'notes' => 'Test booking',
        ]);

        $response->assertRedirect(route('bookings.index', [
            'date' => $scheduledAt->toDateString(),
        ]));
        $response->assertSessionHas('success');

        $index = $this->actingAs($user)->get(route('bookings.index', [
            'date' => $scheduledAt->toDateString(),
        ]));

        $index->assertOk();
        $index->assertSee($customer->full_name);
        $index->assertSee('KDJ 902K', false);
    }

    public function test_manager_can_create_booking_with_selected_services(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KCA 111A',
        ]);

        $category = ServiceCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Wash',
            'is_active' => true,
        ]);

        $service = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Premium Wash',
            'price' => 1500,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        $scheduledAt = now()->startOfHour();

        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'type' => BookingType::Appointment->value,
            'status' => BookingStatus::Confirmed->value,
            'scheduled_at' => $scheduledAt->format('Y-m-d\TH:i'),
            'services' => [(string) $service->id],
        ]);

        $response->assertRedirect(route('bookings.index', [
            'date' => $scheduledAt->toDateString(),
        ]));

        $this->assertDatabaseHas('booking_services', [
            'service_id' => $service->id,
            'price' => 1500,
        ]);
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
