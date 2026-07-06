<x-layouts.mobile :title="$title">
    <x-mobile.page-header :title="$title" :back="route('mobile.vehicles.index')" />

    <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
        @forelse ($vehicles as $vehicle)
            <x-mobile.list-card
                :href="route('mobile.vehicles.show', $vehicle)"
                :title="$vehicle->registration_number"
                :subtitle="$vehicle->customer?->full_name"
                :meta="trim(implode(' ', array_filter([$vehicle->make, $vehicle->model])))"
            />
        @empty
            <x-ui.empty-state :title="'No ' . strtolower($title)" />
        @endforelse
    </div>

    <div class="mt-4">{{ $vehicles->links() }}</div>
</x-layouts.mobile>
