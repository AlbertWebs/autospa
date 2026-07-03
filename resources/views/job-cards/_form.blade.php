@php
    use App\Enums\JobCardStatus;

    $jobCard = $jobCard ?? null;
    $ajax = $ajax ?? false;
@endphp

<x-ui.form-section
    title="Vehicle & Customer"
    description="Link the job card to a customer and their vehicle."
>
    <div class="asp-form-grid">
        <x-ui.form-field label="Customer" for="customer_id" name="customer_id" :required="true" :ajax="$ajax">
            @if ($ajax)
                <x-ui.select id="customer_id" name="customer_id" :ajax="$ajax" x-model="customerId" required>
                    <option value="">Select customer…</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                    @endforeach
                </x-ui.select>
            @else
                <x-ui.select id="customer_id" name="customer_id" required>
                    <option value="">Select customer…</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $jobCard?->customer_id) == $customer->id)>
                            {{ $customer->full_name }}
                        </option>
                    @endforeach
                </x-ui.select>
            @endif
        </x-ui.form-field>

        <x-ui.form-field label="Vehicle" for="vehicle_id" name="vehicle_id" :required="true" :ajax="$ajax" hint="Filtered by selected customer when applicable.">
            @if ($ajax)
                <x-ui.select id="vehicle_id" name="vehicle_id" :ajax="$ajax" x-model="vehicleId" required>
                    <option value="">Select vehicle…</option>
                    @foreach ($vehicles as $vehicle)
                        <option
                            value="{{ $vehicle->id }}"
                            x-bind:disabled="customerId && String(customerId) !== '{{ $vehicle->customer_id }}'"
                        >{{ $vehicle->registration_number }}@if ($vehicle->make) · {{ $vehicle->make }} {{ $vehicle->model }}@endif</option>
                    @endforeach
                </x-ui.select>
            @else
                <x-ui.select id="vehicle_id" name="vehicle_id" required>
                    <option value="">Select vehicle…</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $jobCard?->vehicle_id) == $vehicle->id)>
                            {{ $vehicle->registration_number }}@if ($vehicle->make) · {{ $vehicle->make }} {{ $vehicle->model }}@endif
                        </option>
                    @endforeach
                </x-ui.select>
            @endif
        </x-ui.form-field>
    </div>
</x-ui.form-section>

<x-ui.form-section
    title="Assignment"
    description="Optional booking link and technician assignment."
>
    <div class="asp-form-grid">
        <x-ui.form-field label="Booking" for="booking_id" name="booking_id" :ajax="$ajax" hint="Link to an existing booking, if any.">
            <x-ui.select id="booking_id" name="booking_id" :ajax="$ajax">
                <option value="">None</option>
                @foreach ($bookings as $booking)
                    <option value="{{ $booking->id }}" @selected(old('booking_id', $jobCard?->booking_id) == $booking->id)>
                        #{{ $booking->id }} — {{ $booking->customer?->full_name }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Assigned To" for="assigned_to" name="assigned_to" :ajax="$ajax">
            <x-ui.select id="assigned_to" name="assigned_to" :ajax="$ajax">
                <option value="">Unassigned</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('assigned_to', $jobCard?->assigned_to) == $employee->id)>
                        {{ $employee->name }}
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
