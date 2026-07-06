<x-layouts.mobile title="Vehicles">
    <x-mobile.page-header title="Vehicles" subtitle="All registered vehicles" />

    <div class="mb-4 flex gap-2">
        <a href="{{ route('mobile.vehicles.active') }}" class="asp-mobile-chip">Active</a>
        <a href="{{ route('mobile.vehicles.ready') }}" class="asp-mobile-chip">Ready</a>
    </div>

    <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
        @forelse ($vehicles as $vehicle)
            <x-mobile.list-card
                :href="route('mobile.vehicles.show', $vehicle)"
                :title="$vehicle->registration_number"
                :subtitle="$vehicle->customer?->full_name"
                :meta="trim(implode(' ', array_filter([$vehicle->make, $vehicle->model])))"
                :status="$vehicle->status?->label()"
            />
        @empty
            <x-ui.empty-state title="No vehicles" />
        @endforelse
    </div>

    <div class="mt-4">{{ $vehicles->links() }}</div>
</x-layouts.mobile>
