<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\RoleSlug;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_view_unified_bookings_page(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KDJ 902K',
            'make' => 'Toyota',
            'model' => 'Vitz',
        ]);

        Booking::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'type' => BookingType::WalkIn,
            'status' => BookingStatus::Pending,
            'scheduled_at' => now(),
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('bookings.index'));

        $response->assertOk();
        $response->assertSee('Walk-in');
        $response->assertSee('Pending');
    }

    public function test_bookings_can_be_filtered_by_status_and_date(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KCA 111A',
            'make' => 'Nissan',
            'model' => 'Note',
        ]);

        Booking::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'type' => BookingType::Appointment,
            'status' => BookingStatus::Completed,
            'scheduled_at' => now(),
            'created_by' => $user->id,
        ]);

        Booking::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'type' => BookingType::Appointment,
            'status' => BookingStatus::Pending,
            'scheduled_at' => now()->subDay(),
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('bookings.index', [
            'status' => BookingStatus::Completed->value,
            'date' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('Completed');
        $response->assertSee('1–1 of 1');
    }

    public function test_legacy_pending_route_redirects_to_filtered_index(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($user)->get(route('bookings.pending'));

        $response->assertRedirect(route('bookings.index', ['status' => BookingStatus::Pending->value]));
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
