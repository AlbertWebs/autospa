<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">New Job Card</span>
    </x-slot>

    <header class="asp-page-header">
        <div>
            <p class="asp-page-eyebrow">Operations</p>
            <h1 class="asp-page-title">Check In Vehicle</h1>
            <p class="asp-page-subtitle">Create a new job card to start service on the floor.</p>
        </div>
    </header>

    <div class="asp-panel max-w-3xl">
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
                x-data="jobCardCreateForm({
                    customerId: @js(old('customer_id', '')),
                    vehicleId: @js(old('vehicle_id', '')),
                    successMessage: 'Job card created.',
                })"
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
</x-layouts.app>
