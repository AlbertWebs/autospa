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
    <x-ui.form-field label="Customer" for="customer_id" name="customer_id" :required="true" :ajax="$ajax" :col-span="2">
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

    <x-ui.form-field label="Vehicle" for="vehicle_id" name="vehicle_id" :required="true" :ajax="$ajax" hint="Filtered by selected customer." :col-span="2">
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
