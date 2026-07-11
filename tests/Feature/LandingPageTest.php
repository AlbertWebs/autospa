<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_installed_app_serves_landing_at_home(): void
    {
        $branch = Branch::query()->firstOrFail();
        $service = $this->createService($branch->id, 'Body Wash', 800);

        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee('Kimana', false);
        $response->assertSee('Book Auto Spa', false);
        $response->assertSee('Body Wash', false);
        $response->assertSee('KES 800', false);
        $response->assertSee('application/ld+json', false);
        $response->assertDontSee('Checkout · Job', false);
    }

    public function test_guest_can_request_appointment_booking(): void
    {
        $branch = Branch::query()->firstOrFail();
        $service = $this->createService($branch->id, 'Full Detail', 2500);
        $scheduledAt = Carbon::now()->addDay()->format('Y-m-d\TH:i');

        $response = $this->post(route('landing.book'), [
            'full_name' => 'Jane Kimana',
            'phone' => '0711222333',
            'email' => 'jane@example.com',
            'registration_number' => 'KDG 111A',
            'scheduled_at' => $scheduledAt,
            'service_ids' => [$service->id],
            'notes' => 'Please wax exterior',
        ]);

        $response->assertRedirect(route('landing').'#book');
        $response->assertSessionHas('success');

        $customer = Customer::query()->where('phone', '0711222333')->first();
        $this->assertNotNull($customer);
        $this->assertSame('Jane Kimana', $customer->full_name);
        $this->assertSame($branch->id, $customer->branch_id);

        $vehicle = Vehicle::query()
            ->where('customer_id', $customer->id)
            ->where('registration_number', 'KDG 111A')
            ->first();
        $this->assertNotNull($vehicle);

        $booking = Booking::query()->first();
        $this->assertNotNull($booking);
        $this->assertSame(BookingType::Appointment, $booking->type);
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame($customer->id, $booking->customer_id);
        $this->assertSame($vehicle->id, $booking->vehicle_id);
        $this->assertNull($booking->created_by);
        $this->assertDatabaseHas('booking_services', [
            'booking_id' => $booking->id,
            'service_id' => $service->id,
            'price' => 2500,
        ]);
    }

    public function test_guest_can_book_without_vehicle(): void
    {
        $branch = Branch::query()->firstOrFail();
        $service = $this->createService($branch->id, 'Carpet Wash', 1200);
        $scheduledAt = Carbon::now()->addDays(2)->format('Y-m-d\TH:i');

        $response = $this->post(route('landing.book'), [
            'full_name' => 'Carpet Guest',
            'phone' => '0799888777',
            'scheduled_at' => $scheduledAt,
            'service_ids' => [$service->id],
        ]);

        $response->assertRedirect(route('landing').'#book');

        $booking = Booking::query()->firstOrFail();
        $this->assertNull($booking->vehicle_id);
        $this->assertSame('Carpet Guest', $booking->customer->full_name);
    }

    public function test_public_booking_requires_service_and_future_time(): void
    {
        $response = $this->from(route('landing'))->post(route('landing.book'), [
            'full_name' => 'No Service',
            'phone' => '0700000001',
            'scheduled_at' => Carbon::now()->subHour()->format('Y-m-d\TH:i'),
            'service_ids' => [],
        ]);

        $response->assertRedirect(route('landing'));
        $response->assertSessionHasErrors(['scheduled_at', 'service_ids']);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_guest_cannot_access_staff_booking_create(): void
    {
        $this->get(route('bookings.create'))->assertRedirect(route('login'));
    }

    protected function createService(int $branchId, string $name, float $price): Service
    {
        $category = ServiceCategory::query()->create([
            'branch_id' => $branchId,
            'name' => 'Wash',
            'is_active' => true,
        ]);

        return Service::query()->create([
            'branch_id' => $branchId,
            'service_category_id' => $category->id,
            'name' => $name,
            'price' => $price,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);
    }
}
