<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\BusinessHour;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Vehicle;
use App\Support\RegistrationNumber;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicBookingService
{
    public function __construct(
        protected CompanyService $companyService,
    ) {}

    public function defaultBranch(): ?Branch
    {
        return Branch::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }

    /** @return Collection<int, Service> */
    public function activeServices(?int $branchId = null): Collection
    {
        $query = Service::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name');

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    /** @return Collection<int, BusinessHour> */
    public function businessHours(int $branchId): Collection
    {
        return BusinessHour::query()
            ->where('branch_id', $branchId)
            ->orderBy('day_of_week')
            ->get();
    }

    /**
     * @param  array{
     *     full_name: string,
     *     phone: string,
     *     email?: string|null,
     *     registration_number?: string|null,
     *     scheduled_at: string,
     *     service_ids: list<int>,
     *     notes?: string|null
     * }  $data
     */
    public function book(array $data): Booking
    {
        $branch = $this->defaultBranch();

        if ($branch === null) {
            throw ValidationException::withMessages([
                'full_name' => 'Bookings are temporarily unavailable. Please call us instead.',
            ]);
        }

        $serviceIds = collect($data['service_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $services = Service::query()
            ->where('branch_id', $branch->id)
            ->where('is_active', true)
            ->whereIn('id', $serviceIds)
            ->get()
            ->keyBy('id');

        if ($serviceIds->isEmpty() || $serviceIds->count() !== $services->count()) {
            throw ValidationException::withMessages([
                'service_ids' => 'Select at least one available service.',
            ]);
        }

        return DB::transaction(function () use ($data, $branch, $serviceIds, $services) {
            $phone = trim($data['phone']);
            $customer = Customer::query()
                ->where('branch_id', $branch->id)
                ->where('phone', $phone)
                ->first();

            if ($customer === null) {
                $customer = Customer::query()->create([
                    'branch_id' => $branch->id,
                    'full_name' => trim($data['full_name']),
                    'phone' => $phone,
                    'email' => filled($data['email'] ?? null) ? trim((string) $data['email']) : null,
                ]);
            } else {
                $customer->update([
                    'full_name' => trim($data['full_name']),
                    'email' => filled($data['email'] ?? null)
                        ? trim((string) $data['email'])
                        : $customer->email,
                ]);
            }

            $vehicleId = null;
            $registration = RegistrationNumber::normalize($data['registration_number'] ?? null);

            if (filled($registration)) {
                $vehicle = Vehicle::query()
                    ->where('branch_id', $branch->id)
                    ->where('customer_id', $customer->id)
                    ->where('registration_number', $registration)
                    ->first();

                if ($vehicle === null) {
                    $vehicle = Vehicle::query()->create([
                        'branch_id' => $branch->id,
                        'customer_id' => $customer->id,
                        'registration_number' => $registration,
                    ]);
                }

                $vehicleId = $vehicle->id;
            }

            $durationMinutes = (int) $services->sum('duration_minutes');
            $scheduledAt = $data['scheduled_at'];
            $endsAt = $durationMinutes > 0
                ? \Carbon\Carbon::parse($scheduledAt)->addMinutes($durationMinutes)
                : null;

            $booking = Booking::query()->create([
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicleId,
                'created_by' => null,
                'type' => BookingType::Appointment,
                'status' => BookingStatus::Pending,
                'scheduled_at' => $scheduledAt,
                'ends_at' => $endsAt,
                'notes' => filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null,
                'is_recurring' => false,
            ]);

            $booking->bookingServices()->createMany(
                $serviceIds->map(fn (int $id) => [
                    'service_id' => $id,
                    'price' => $services->get($id)?->price ?? 0,
                    'duration_minutes' => $services->get($id)?->duration_minutes,
                ])->all()
            );

            return $booking->load(['customer', 'vehicle', 'bookingServices.service']);
        });
    }
}
