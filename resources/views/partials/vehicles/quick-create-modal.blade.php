{{-- Requires parent x-data with showVehicleModal, vehicleForm, vehicleErrors, vehicleSaving, customerId, customers, closeVehicleModal, createVehicle --}}
<div
    x-show="showVehicleModal"
    x-cloak
    class="asp-modal-backdrop"
    @keydown.escape.window="showVehicleModal && closeVehicleModal()"
>
    <div
        class="asp-modal"
        @click.outside="closeVehicleModal()"
        x-transition
    >
        <div class="asp-modal-header">
            <div>
                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-primary">New Vehicle</p>
                <h3 class="asp-modal-title">Quick Add Vehicle</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400" x-show="customerId" x-cloak>
                    For
                    <span class="font-medium text-slate-700 dark:text-slate-200" x-text="customers.find(c => String(c.id) === String(customerId))?.full_name ?? 'selected customer'"></span>
                </p>
            </div>
            <button type="button" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-brand-surface-high dark:hover:text-slate-200" @click="closeVehicleModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form @submit.prevent="createVehicle" class="asp-form !space-y-5">
            <div class="asp-modal-body space-y-5">
                <div
                    class="asp-form-alert mb-0"
                    x-show="Object.keys(vehicleErrors).filter(key => vehicleErrors[key]?.length).length > 0"
                    x-cloak
                    style="display: none;"
                >
                    <span class="material-symbols-outlined shrink-0 text-lg">error</span>
                    <p>Please fix the errors below.</p>
                </div>

                <x-ui.form-field label="Registration Number" for="quick_vehicle_registration" required>
                    <x-ui.input
                        id="quick_vehicle_registration"
                        type="text"
                        class="uppercase"
                        x-model="vehicleForm.registration_number"
                        @input="vehicleForm.registration_number = $event.target.value.toUpperCase()"
                        x-bind:class="{ 'asp-input--error': vehicleErrors.registration_number }"
                        placeholder="KDA 123A"
                        required
                    />
                    <p class="asp-field-error" x-show="vehicleErrors.registration_number" x-cloak>
                        <span class="material-symbols-outlined text-sm">error</span>
                        <span x-text="vehicleErrors.registration_number?.[0]"></span>
                    </p>
                </x-ui.form-field>

                <div class="asp-form-grid">
                    <x-ui.form-field label="Make" for="quick_vehicle_make">
                        <x-ui.input
                            id="quick_vehicle_make"
                            type="text"
                            x-model="vehicleForm.make"
                            x-bind:class="{ 'asp-input--error': vehicleErrors.make }"
                            placeholder="Toyota"
                        />
                        <p class="asp-field-error" x-show="vehicleErrors.make" x-cloak>
                            <span class="material-symbols-outlined text-sm">error</span>
                            <span x-text="vehicleErrors.make?.[0]"></span>
                        </p>
                    </x-ui.form-field>

                    <x-ui.form-field label="Model" for="quick_vehicle_model">
                        <x-ui.input
                            id="quick_vehicle_model"
                            type="text"
                            x-model="vehicleForm.model"
                            x-bind:class="{ 'asp-input--error': vehicleErrors.model }"
                            placeholder="RAV4"
                        />
                        <p class="asp-field-error" x-show="vehicleErrors.model" x-cloak>
                            <span class="material-symbols-outlined text-sm">error</span>
                            <span x-text="vehicleErrors.model?.[0]"></span>
                        </p>
                    </x-ui.form-field>
                </div>

                <x-ui.form-field label="Color" for="quick_vehicle_color" hint="Optional">
                    <x-ui.input
                        id="quick_vehicle_color"
                        type="text"
                        x-model="vehicleForm.color"
                        x-bind:class="{ 'asp-input--error': vehicleErrors.color }"
                        placeholder="Silver"
                    />
                    <p class="asp-field-error" x-show="vehicleErrors.color" x-cloak>
                        <span class="material-symbols-outlined text-sm">error</span>
                        <span x-text="vehicleErrors.color?.[0]"></span>
                    </p>
                </x-ui.form-field>
            </div>

            <div class="asp-modal-footer">
                <button type="button" class="asp-btn asp-btn-ghost" @click="closeVehicleModal()" x-bind:disabled="vehicleSaving">
                    Cancel
                </button>
                <button type="submit" class="asp-btn asp-btn-primary min-w-[8rem]" x-bind:disabled="vehicleSaving">
                    <svg x-show="vehicleSaving" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="material-symbols-outlined text-lg" x-show="!vehicleSaving">directions_car</span>
                    <span x-text="vehicleSaving ? 'Saving…' : 'Add Vehicle'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
