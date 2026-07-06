<x-layouts.mobile :title="$vehicle->registration_number">
    <x-mobile.page-header
        :title="$vehicle->registration_number"
        :subtitle="$vehicle->customer?->full_name"
        :back="route('mobile.vehicles.index')"
    />

    <div class="space-y-4">
        <div class="asp-mobile-card space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Make / Model</span><span>{{ trim(implode(' ', array_filter([$vehicle->make, $vehicle->model]))) ?: '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Color</span><span>{{ $vehicle->color ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Status</span><span>{{ $vehicle->status?->label() }}</span></div>
        </div>

        <a href="{{ route('vehicles.show', $vehicle) }}" class="asp-mobile-action-btn inline-flex w-full justify-center">Full details</a>
    </div>
</x-layouts.mobile>
