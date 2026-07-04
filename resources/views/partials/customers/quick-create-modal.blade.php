{{-- Requires parent x-data with showCustomerModal, customerForm, customerErrors, customerSaving, closeCustomerModal, createCustomer --}}
<div
    x-show="showCustomerModal"
    x-cloak
    class="asp-modal-backdrop"
    @keydown.escape.window="showCustomerModal && closeCustomerModal()"
>
    <div
        class="asp-modal"
        @click.outside="closeCustomerModal()"
        x-transition
    >
        <div class="asp-modal-header">
            <div>
                <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-primary">New Customer</p>
                <h3 class="asp-modal-title">Quick Add Customer</h3>
            </div>
            <button type="button" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-brand-surface-high dark:hover:text-slate-200" @click="closeCustomerModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form @submit.prevent="createCustomer" class="asp-form !space-y-5">
            <div class="asp-modal-body space-y-5">
                <div
                    class="asp-form-alert mb-0"
                    x-show="Object.keys(customerErrors).filter(key => customerErrors[key]?.length).length > 0"
                    x-cloak
                    style="display: none;"
                >
                    <span class="material-symbols-outlined shrink-0 text-lg">error</span>
                    <p>Please fix the errors below.</p>
                </div>

                <x-ui.form-field label="Full Name" for="quick_customer_full_name" required>
                    <x-ui.input
                        id="quick_customer_full_name"
                        type="text"
                        x-model="customerForm.full_name"
                        x-bind:class="{ 'asp-input--error': customerErrors.full_name }"
                        placeholder="Jane Mwangi"
                        required
                    />
                    <p class="asp-field-error" x-show="customerErrors.full_name" x-cloak>
                        <span class="material-symbols-outlined text-sm">error</span>
                        <span x-text="customerErrors.full_name?.[0]"></span>
                    </p>
                </x-ui.form-field>

                <x-ui.form-field label="Phone" for="quick_customer_phone" required>
                    <x-ui.input
                        id="quick_customer_phone"
                        type="tel"
                        x-model="customerForm.phone"
                        x-bind:class="{ 'asp-input--error': customerErrors.phone }"
                        placeholder="+254 7XX XXX XXX"
                        required
                    />
                    <p class="asp-field-error" x-show="customerErrors.phone" x-cloak>
                        <span class="material-symbols-outlined text-sm">error</span>
                        <span x-text="customerErrors.phone?.[0]"></span>
                    </p>
                </x-ui.form-field>

                <x-ui.form-field label="Email" for="quick_customer_email" hint="Optional">
                    <x-ui.input
                        id="quick_customer_email"
                        type="email"
                        x-model="customerForm.email"
                        x-bind:class="{ 'asp-input--error': customerErrors.email }"
                        placeholder="customer@example.com"
                    />
                    <p class="asp-field-error" x-show="customerErrors.email" x-cloak>
                        <span class="material-symbols-outlined text-sm">error</span>
                        <span x-text="customerErrors.email?.[0]"></span>
                    </p>
                </x-ui.form-field>

                <x-ui.form-field label="Car Registration" for="quick_customer_registration_number" hint="Optional">
                    <x-ui.input
                        id="quick_customer_registration_number"
                        type="text"
                        x-model="customerForm.registration_number"
                        x-bind:class="{ 'asp-input--error': customerErrors.registration_number }"
                        placeholder="KDA 123A"
                    />
                    <p class="asp-field-error" x-show="customerErrors.registration_number" x-cloak>
                        <span class="material-symbols-outlined text-sm">error</span>
                        <span x-text="customerErrors.registration_number?.[0]"></span>
                    </p>
                </x-ui.form-field>
            </div>

            <div class="asp-modal-footer">
                <button type="button" class="asp-btn asp-btn-ghost" @click="closeCustomerModal()" x-bind:disabled="customerSaving">
                    Cancel
                </button>
                <button type="submit" class="asp-btn asp-btn-primary min-w-[8rem]" x-bind:disabled="customerSaving">
                    <svg x-show="customerSaving" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="material-symbols-outlined text-lg" x-show="!customerSaving">person_add</span>
                    <span x-text="customerSaving ? 'Saving…' : 'Add Customer'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
