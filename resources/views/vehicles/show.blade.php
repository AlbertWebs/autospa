<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $vehicle->registration_number }}</h1></x-slot>

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
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Year</dt><dd>{{ $vehicle->year ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Color</dt><dd>{{ $vehicle->color ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">VIN</dt><dd>{{ $vehicle->vin ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Mileage</dt><dd>{{ $vehicle->mileage ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}</x-ui.badge></dd></div>
            </dl>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Owner</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $vehicle->customer?->full_name ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $vehicle->customer?->phone ?? '—' }}</dd></div>
            </dl>
        </x-ui.card>
    </div>
</x-layouts.app>
