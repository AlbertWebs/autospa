@php $vehicle = $vehicle ?? null; @endphp

<x-ui.form-section title="Vehicle Information" description="Registration, make/model, and vehicle details.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Customer" for="customer_id" name="customer_id" :required="true" :col-span="2">
            <x-ui.select id="customer_id" name="customer_id" required>
                <option value="">Select customer…</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $vehicle->customer_id ?? '') == $customer->id)>{{ $customer->full_name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Registration Number" for="registration_number" name="registration_number" :required="true">
            <x-ui.input id="registration_number" name="registration_number" :value="old('registration_number', $vehicle->registration_number ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Make" for="make" name="make" :required="true">
            <x-ui.input id="make" name="make" :value="old('make', $vehicle->make ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Model" for="model" name="model" :required="true">
            <x-ui.input id="model" name="model" :value="old('model', $vehicle->model ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Year" for="year" name="year">
            <x-ui.input id="year" name="year" type="number" :value="old('year', $vehicle->year ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Color" for="color" name="color">
            <x-ui.input id="color" name="color" :value="old('color', $vehicle->color ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="VIN" for="vin" name="vin">
            <x-ui.input id="vin" name="vin" :value="old('vin', $vehicle->vin ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Mileage" for="mileage" name="mileage">
            <x-ui.input id="mileage" name="mileage" type="number" :value="old('mileage', $vehicle->mileage ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Status" for="status" name="status">
            <x-ui.select id="status" name="status">
                @foreach (['active', 'in_service', 'ready', 'inactive'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $vehicle->status ?? 'active') == $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
