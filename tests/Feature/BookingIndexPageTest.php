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

    public function test_bookings_index_shows_all_bookings_by_default(): void
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
            'status' => BookingStatus::Pending,
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

        $response = $this->actingAs($user)->get(route('bookings.index'));

        $response->assertOk();
        $response->assertSee('1–2 of 2');
        $response->assertSee('All bookings for this branch.');
    }

    public function test_bookings_index_can_filter_to_single_day(): void
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
            'status' => BookingStatus::Pending,
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
            'date' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('1–1 of 1');
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

        $response->assertRedirect();
        $response->assertLocation(route('bookings.index', [
            'status' => BookingStatus::Pending->value,
        ]));
    }

    public function test_past_booking_shows_mark_done_on_index(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KCB 222B',
            'make' => 'Mazda',
            'model' => 'Demio',
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

        $response = $this->actingAs($user)->get(route('bookings.index'));

        $response->assertOk();
        $response->assertSee('Mark done');
    }

    public function test_manager_can_mark_past_booking_as_done(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KCC 333C',
            'make' => 'Honda',
            'model' => 'Fit',
        ]);

        $booking = Booking::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'type' => BookingType::Appointment,
            'status' => BookingStatus::Confirmed,
            'scheduled_at' => now()->subDays(2),
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('bookings.mark-done', $booking));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame(BookingStatus::Completed, $booking->fresh()->status);
    }

    public function test_cannot_mark_future_booking_as_done(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KCD 444D',
            'make' => 'Subaru',
            'model' => 'Impreza',
        ]);

        $booking = Booking::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'type' => BookingType::Appointment,
            'status' => BookingStatus::Pending,
            'scheduled_at' => now()->addDay(),
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('bookings.mark-done', $booking));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
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
