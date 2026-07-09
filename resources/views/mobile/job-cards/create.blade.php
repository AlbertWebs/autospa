@php
    $customersJson = $customers->map(fn ($c) => [
        'id' => $c->id,
        'full_name' => $c->full_name,
    ])->values();

    $vehiclesJson = $vehicles->map(fn ($v) => [
        'id' => $v->id,
        'customer_id' => $v->customer_id,
        'registration_number' => $v->registration_number,
        'make' => $v->make,
        'model' => $v->model,
        'color' => $v->color,
    ])->values();
@endphp

<x-layouts.mobile title="Check In">
    <x-mobile.page-header
        title="Check In Vehicle"
        subtitle="Create a new job card"
        :back="route('mobile.job-cards.live')"
    />

    <div
        x-data="jobCardCreateForm({
            customerId: @js(old('customer_id', '')),
            vehicleId: @js(old('vehicle_id', '')),
            customers: @js($customersJson),
            vehicles: @js($vehiclesJson),
            customerStoreUrl: @js(route('customers.store')),
            vehicleStoreUrl: @js(route('vehicles.store')),
            successMessage: 'Job card created.',
        })"
    >
        <form method="POST" action="{{ route('mobile.job-cards.store') }}" class="asp-form asp-mobile-card" data-offline-capable="true" @submit.prevent="submit">
            @csrf
            @include('job-cards._form', ['ajax' => true, 'employees' => $employees, 'bookings' => $bookings])

            <div class="mt-4 flex gap-2">
                <button type="submit" class="asp-btn asp-btn-primary flex-1" :disabled="loading">
                    <span x-text="loading ? 'Creating…' : 'Create Job Card'"></span>
                </button>
                <a href="{{ route('mobile.job-cards.live') }}" class="asp-btn asp-btn-ghost">Cancel</a>
            </div>
        </form>

        @include('partials.customers.quick-create-modal')
        @include('partials.vehicles.quick-create-modal')
    </div>
</x-layouts.mobile>
