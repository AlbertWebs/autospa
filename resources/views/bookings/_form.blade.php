@php $booking = $booking ?? null; @endphp

<x-ui.form-section title="Booking Information" description="Schedule, customer, vehicle, and service details.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Customer" for="customer_id" name="customer_id" :required="true">
            <x-ui.select id="customer_id" name="customer_id" required>
                <option value="">Select customer…</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $booking->customer_id ?? '') == $customer->id)>{{ $customer->full_name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Vehicle" for="vehicle_id" name="vehicle_id">
            <x-ui.select id="vehicle_id" name="vehicle_id">
                <option value="">Select vehicle…</option>
                @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $booking->vehicle_id ?? '') == $vehicle->id)>{{ $vehicle->registration_number }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Type" for="type" name="type" :required="true">
            <x-ui.select id="type" name="type" required>
                @foreach (['appointment', 'walk_in'] as $type)
                    <option value="{{ $type }}" @selected(old('type', $booking->type ?? 'appointment') == $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Status" for="status" name="status" :required="true">
            <x-ui.select id="status" name="status" required>
                @foreach (['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $booking->status ?? 'pending') == $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Scheduled At" for="scheduled_at" name="scheduled_at">
            <x-ui.input id="scheduled_at" name="scheduled_at" type="datetime-local" :value="old('scheduled_at', isset($booking->scheduled_at) ? $booking->scheduled_at->format('Y-m-d\TH:i') : '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Ends At" for="ends_at" name="ends_at">
            <x-ui.input id="ends_at" name="ends_at" type="datetime-local" :value="old('ends_at', isset($booking->ends_at) ? $booking->ends_at->format('Y-m-d\TH:i') : '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Services" name="services" :col-span="2">
            <div class="asp-checkbox-group">
                @foreach ($services as $service)
                    <x-ui.checkbox-card name="services[]" :value="$service->id" :checked="in_array($service->id, old('services', $booking?->services->pluck('id')->toArray() ?? []))">
                        {{ $service->name }}
                    </x-ui.checkbox-card>
                @endforeach
            </div>
        </x-ui.form-field>

        <x-ui.form-field label="Notes" for="notes" name="notes" :col-span="2">
            <x-ui.textarea id="notes" name="notes" rows="3">{{ old('notes', $booking->notes ?? '') }}</x-ui.textarea>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
