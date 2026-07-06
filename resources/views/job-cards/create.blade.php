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

<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">Operations</span>
    </x-slot>

    <x-ui.section-header eyebrow="Operations" />

    <div
        class="max-w-6xl"
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
        <div class="asp-panel">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title">Job Card Details</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">All required fields are marked with an asterisk.</p>
                </div>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">garage</span>
            </div>

            <div class="asp-panel-body">
                <form
                    method="POST"
                    action="{{ route('job-cards.store') }}"
                    class="asp-form"
                    @submit.prevent="submit"
                >
                    @csrf

                    <div
                        class="asp-form-alert mb-2"
                        x-show="Object.keys(errors).filter(key => errors[key]?.length).length > 0"
                        x-cloak
                        style="display: none;"
                    >
                        <span class="material-symbols-outlined shrink-0 text-lg">error</span>
                        <p>Please fix the highlighted fields below and try again.</p>
                    </div>

                    @include('job-cards._form', ['ajax' => true])

                    <x-ui.form-actions>
                        <button
                            type="submit"
                            class="asp-btn asp-btn-primary min-w-[10rem]"
                            :disabled="loading"
                        >
                            <span class="material-symbols-outlined text-lg" x-show="!loading">check_circle</span>
                            <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="loading ? 'Creating…' : 'Create Job Card'"></span>
                        </button>
                        <a href="{{ route('job-cards.index') }}" class="asp-btn asp-btn-ghost">Cancel</a>
                    </x-ui.form-actions>
                </form>
            </div>
        </div>

        @include('partials.customers.quick-create-modal')
        @include('partials.vehicles.quick-create-modal')
    </div>
</x-layouts.app>
