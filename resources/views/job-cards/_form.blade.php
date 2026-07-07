@php
    use App\Enums\JobCardStatus;

    $jobCard = $jobCard ?? null;
    $ajax = $ajax ?? false;
    $splitLayout = $splitLayout ?? false;
    $employees = $employees ?? collect();
    $servicesCollection = $services ?? collect();
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
    $selectedServiceIds = array_map('intval', $selectedServiceIds);
    $servicesByCategory = $servicesCollection->groupBy(fn ($service) => $service->category?->name ?: 'General');
    $estimatedTotal = $servicesCollection->whereIn('id', $selectedServiceIds)->sum('price');
@endphp

@php
    $customerVehicleSection = view('job-cards._form-customer-vehicle', get_defined_vars())->render();
    $servicesSection = view('job-cards._form-services', get_defined_vars())->render();
    $assignmentSection = view('job-cards._form-assignment', get_defined_vars())->render();
    $notesSection = view('job-cards._form-notes', get_defined_vars())->render();
@endphp

@if ($splitLayout)
    <div class="asp-job-card-form-layout">
        <div class="asp-job-card-form-main">
            <section class="asp-job-card-step">
                <div class="asp-job-card-step-header">
                    <span class="asp-job-card-step-badge">1</span>
                    <div>
                        <h2 class="asp-job-card-step-title">Vehicle &amp; Customer</h2>
                        <p class="asp-job-card-step-desc">Who is checking in and which vehicle is being washed.</p>
                    </div>
                </div>
                {!! $customerVehicleSection !!}
            </section>

            <section class="asp-job-card-step">
                <div class="asp-job-card-step-header">
                    <span class="asp-job-card-step-badge">2</span>
                    <div>
                        <h2 class="asp-job-card-step-title">Services</h2>
                        <p class="asp-job-card-step-desc">Select one or more wash or detailing services.</p>
                    </div>
                </div>
                {!! $servicesSection !!}
            </section>

            <section class="asp-job-card-step">
                <div class="asp-job-card-step-header">
                    <span class="asp-job-card-step-badge">3</span>
                    <div>
                        <h2 class="asp-job-card-step-title">Notes</h2>
                        <p class="asp-job-card-step-desc">Optional instructions for the wash bay team.</p>
                    </div>
                </div>
                {!! $notesSection !!}
            </section>
        </div>

        <aside class="asp-job-card-form-aside">
            <div class="asp-job-card-summary">
                <div class="asp-job-card-summary-header">
                    <span class="material-symbols-outlined text-brand-primary">receipt_long</span>
                    <h3 class="asp-job-card-summary-title">Job Summary</h3>
                </div>

                @if ($ajax)
                    <dl class="asp-job-card-summary-meta">
                        <div>
                            <dt>Customer</dt>
                            <dd x-text="selectedCustomerName || 'Not selected'"></dd>
                        </div>
                        <div>
                            <dt>Vehicle</dt>
                            <dd x-text="selectedVehicleLabel || 'Not selected'"></dd>
                        </div>
                    </dl>

                    <div class="asp-job-card-summary-divider"></div>

                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Selected services</p>
                        <template x-if="selectedServices.length === 0">
                            <p class="text-sm text-slate-400">No services selected yet.</p>
                        </template>
                        <ul class="space-y-2" x-show="selectedServices.length > 0">
                            <template x-for="service in selectedServices" :key="service.id">
                                <li class="flex items-center justify-between gap-3 text-sm">
                                    <span class="truncate text-slate-700 dark:text-slate-200" x-text="service.name"></span>
                                    <span class="shrink-0 font-mono text-xs text-slate-500" x-text="formatMoney(service.price)"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="asp-job-card-summary-total">
                        <span>Estimated total</span>
                        <span class="font-display text-xl font-bold text-slate-900 dark:text-white" x-text="formatMoney(serviceTotal)"></span>
                    </div>
                @else
                    <dl class="asp-job-card-summary-meta">
                        <div>
                            <dt>Services selected</dt>
                            <dd>{{ count($selectedServiceIds) }}</dd>
                        </div>
                    </dl>

                    <div class="asp-job-card-summary-total">
                        <span>Estimated total</span>
                        <span class="font-display text-xl font-bold text-slate-900 dark:text-white">KES {{ number_format($estimatedTotal, 0) }}</span>
                    </div>
                @endif
            </div>

            <div class="asp-job-card-aside-panel">
                <div class="asp-job-card-step-header !mb-4">
                    <span class="asp-job-card-step-badge asp-job-card-step-badge--muted">4</span>
                    <div>
                        <h2 class="asp-job-card-step-title">Assignment</h2>
                        <p class="asp-job-card-step-desc">Assign a washer and set the starting status.</p>
                    </div>
                </div>
                {!! $assignmentSection !!}
            </div>
        </aside>
    </div>
@else
    {!! $servicesSection !!}

    <div class="grid gap-6 xl:grid-cols-2">
        <x-ui.form-section
            title="Vehicle & Customer"
            description="Link the job card to a customer and their vehicle."
        >
            {!! $customerVehicleSection !!}
        </x-ui.form-section>

        <x-ui.form-section
            title="Assignment"
            description="Assign the vehicle to an employee and link optional booking details."
        >
            {!! $assignmentSection !!}
        </x-ui.form-section>
    </div>

    <x-ui.form-section title="Notes" description="Internal notes visible to staff on the job card.">
        {!! $notesSection !!}
    </x-ui.form-section>
@endif
