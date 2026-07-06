<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Vehicles</span></x-slot>

    <x-ui.section-header eyebrow="Vehicles" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('vehicles.index'),
            'editRoute' => route('vehicles.edit', $vehicle),
            'deleteRoute' => route('vehicles.destroy', $vehicle),
            'deleteConfirm' => 'Delete this vehicle?',
        ])
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Vehicle Details</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Make / Model</dt><dd class="font-medium">{{ $vehicle->make }} {{ $vehicle->model }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Year</dt><dd>{{ $vehicle->year ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Color</dt><dd>{{ $vehicle->color ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">VIN</dt><dd>{{ $vehicle->vin ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Mileage</dt><dd>{{ $vehicle->mileage ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ $vehicle->status?->label() ?? 'N/A' }}</x-ui.badge></dd></div>
            </dl>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Owner</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $vehicle->customer?->full_name ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $vehicle->customer?->phone ?? 'N/A' }}</dd></div>
            </dl>
        </x-ui.card>
    </div>
</x-layouts.app>
