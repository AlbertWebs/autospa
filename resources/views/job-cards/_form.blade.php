@php
    use App\Enums\JobCardStatus;

    $jobCard = $jobCard ?? null;
    $ajax = $ajax ?? false;
    $employees = $employees ?? collect();
    $customersJson = ($customers ?? collect())->map(fn ($customer) => [
        'id' => $customer->id,
        'full_name' => $customer->full_name,
    ])->values();
    $vehiclesJson = ($vehicles ?? collect())->map(fn ($vehicle) => [
        'id' => $vehicle->id,
        'customer_id' => $vehicle->customer_id,
        'registration_number' => $vehicle->registration_number,
        'make' => $vehicle->make,
        'model' => $vehicle->model,
        'color' => $vehicle->color,
    ])->values();

    $selectedServiceIds = old('service_ids', $selectedServiceIds ?? ($jobCard?->services->pluck('service_id')->all() ?? []));
@endphp

<x-ui.form-section
    title="Services"
    description="Select the wash or detailing services for this job."
>
    <x-ui.form-field
        label="Services"
        name="service_ids"
        :required="true"
        :col-span="2"
        :ajax="$ajax"
        hint="Choose at least one service."
    >
        @if (($services ?? collect())->isEmpty())
            <p class="text-sm text-amber-600 dark:text-amber-400">
                No active services found for this branch.
                <a href="{{ route('services.create') }}" class="font-medium underline">Add services</a> first.
            </p>
        @else
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($services as $service)
                    <label class="asp-checkbox-card">
                        <input
                            type="checkbox"
                            name="service_ids[]"
                            value="{{ $service->id }}"
                            @checked(in_array($service->id, $selectedServiceIds, false))
                            x-bind:class="{ 'asp-input--error': errors?.service_ids }"
                        >
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-slate-900 dark:text-white">{{ $service->name }}</span>
                            <span class="block font-mono text-xs text-slate-500">KES {{ number_format($service->price, 0) }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @if ($ajax)
                <p class="asp-field-error" x-show="errors?.service_ids" x-cloak>
                    <span class="material-symbols-outlined text-sm">error</span>
                    <span x-text="errors?.service_ids?.[0]"></span>
                </p>
            @else
                @error('service_ids')
                    <p class="asp-field-error">
                        <span class="material-symbols-outlined text-sm">error</span>
                        {{ $message }}
                    </p>
                @enderror
                @error('service_ids.*')
                    <p class="asp-field-error">
                        <span class="material-symbols-outlined text-sm">error</span>
                        {{ $message }}
                    </p>
                @enderror
            @endif
        @endif
    </x-ui.form-field>
</x-ui.form-section>

<x-ui.form-section
    title="Vehicle & Customer"
    description="Link the job card to a customer and their vehicle."
>
    <div
        class="asp-form-grid"
        @if (! $ajax)
            x-data="customerVehicleLinkForm({
                customerId: @js(old('customer_id', $jobCard?->customer_id ?? '')),
                vehicleId: @js(old('vehicle_id', $jobCard?->vehicle_id ?? '')),
                customers: @js($customersJson),
                vehicles: @js($vehiclesJson),
            })"
        @endif
    >
        <x-ui.form-field label="Customer" for="customer_id" name="customer_id" :required="true" :ajax="$ajax">
            @if ($ajax)
                <div class="asp-field-addon">
                    <x-ui.select id="customer_id" name="customer_id" :ajax="$ajax" x-model="customerId" required>
                        <option value="">Select customer…</option>
                        <template x-for="customer in customers" :key="customer.id">
                            <option :value="customer.id" x-text="customer.full_name"></option>
                        </template>
                    </x-ui.select>
                    <button
                        type="button"
                        class="asp-btn asp-btn-secondary shrink-0 !px-3"
                        title="Create new customer"
                        @click="openCustomerModal()"
                    >
                        <span class="material-symbols-outlined text-lg">person_add</span>
                    </button>
                </div>
                <p class="asp-field-hint">
                    <button type="button" class="text-brand-primary-dim hover:underline dark:text-brand-primary" @click="openCustomerModal()">
                        + Create new customer
                    </button>
                </p>
            @else
                <x-ui.select id="customer_id" name="customer_id" x-model="customerId" required>
                    <option value="">Select customer…</option>
                    <template x-for="customer in customers" :key="customer.id">
                        <option :value="customer.id" x-text="customer.full_name"></option>
                    </template>
                </x-ui.select>
            @endif
        </x-ui.form-field>

        <x-ui.form-field label="Vehicle" for="vehicle_id" name="vehicle_id" :required="true" :ajax="$ajax" hint="Filtered by selected customer.">
            @if ($ajax)
                <div class="asp-field-addon">
                    <x-ui.select id="vehicle_id" name="vehicle_id" :ajax="$ajax" x-model="vehicleId" required>
                        <option value="">Select vehicle…</option>
                        <template x-for="vehicle in filteredVehicles" :key="vehicle.id">
                            <option :value="vehicle.id" x-text="vehicleLabel(vehicle)"></option>
                        </template>
                    </x-ui.select>
                    <button
                        type="button"
                        class="asp-btn asp-btn-secondary shrink-0 !px-3"
                        title="Add vehicle for selected customer"
                        @click="openVehicleModal()"
                        :disabled="!customerId"
                    >
                        <span class="material-symbols-outlined text-lg">directions_car</span>
                    </button>
                </div>
                <p class="asp-field-hint">
                    <button
                        type="button"
                        class="text-brand-primary-dim hover:underline disabled:cursor-not-allowed disabled:opacity-50 dark:text-brand-primary"
                        @click="openVehicleModal()"
                        :disabled="!customerId"
                    >
                        + Add vehicle for this customer
                    </button>
                    <span x-show="!customerId" class="text-slate-400">: select a customer first</span>
                </p>
            @else
                <x-ui.select id="vehicle_id" name="vehicle_id" x-model="vehicleId" required>
                    <option value="">Select vehicle…</option>
                    <template x-for="vehicle in filteredVehicles" :key="vehicle.id">
                        <option :value="vehicle.id" x-text="vehicleLabel(vehicle)"></option>
                    </template>
                </x-ui.select>
            @endif
        </x-ui.form-field>
    </div>
</x-ui.form-section>

<x-ui.form-section
    title="Assignment"
    description="Assign the vehicle to an employee and link optional booking details."
>
    <div class="asp-form-grid">
        <x-ui.form-field
            label="Assign vehicle to employee"
            for="assigned_to"
            name="assigned_to"
            :ajax="$ajax"
            hint="Active employees at your current branch."
            :col-span="2"
        >
            <x-ui.select id="assigned_to" name="assigned_to" :ajax="$ajax">
                <option value="">Select employee…</option>
                @forelse ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('assigned_to', $jobCard?->assigned_to) == $employee->id)>
                        {{ $employee->displayName() }}
                    </option>
                @empty
                    <option value="" disabled>No active employees at this branch</option>
                @endforelse
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Booking" for="booking_id" name="booking_id" :ajax="$ajax" hint="Link to an existing booking, if any.">
            <x-ui.select id="booking_id" name="booking_id" :ajax="$ajax">
                <option value="">None</option>
                @foreach ($bookings as $booking)
                    <option value="{{ $booking->id }}" @selected(old('booking_id', $jobCard?->booking_id) == $booking->id)>
                        #{{ $booking->id }}: {{ $booking->customer?->full_name }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Status" for="status" name="status" :required="true" :ajax="$ajax">
            <x-ui.select id="status" name="status" :ajax="$ajax" required>
                @foreach (JobCardStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $jobCard?->status?->value ?? JobCardStatus::Open->value) == $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>
    </div>
</x-ui.form-section>

<x-ui.form-section title="Notes" description="Internal notes visible to staff on the job card.">
    <x-ui.form-field label="Notes" for="notes" name="notes" :col-span="2" :ajax="$ajax">
        <x-ui.textarea id="notes" name="notes" rows="4" :ajax="$ajax" placeholder="Damage notes, special requests, bay instructions…">{{ old('notes', $jobCard?->notes) }}</x-ui.textarea>
    </x-ui.form-field>
</x-ui.form-section>
