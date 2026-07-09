<x-ui.index-page
    eyebrow="Vehicles"
    title="Vehicle Check-In"
    subtitle="Vehicles awaiting intake and check-in processing."
    :create-route="route('vehicles.create')"
    create-label="Add Vehicle"
>
    <x-ui.data-table
        :paginator="$vehicles"
        :empty="$vehicles->isEmpty()"
        empty-title="No vehicles to check in"
        empty-description="Vehicles awaiting check-in will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Registration</x-ui.th>
            <x-ui.th>Vehicle</x-ui.th>
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($vehicles as $vehicle)
            <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" :paginator="$vehicles" />
                <x-ui.td mono primary>{{ $vehicle->registration_number }}</x-ui.td>
                <x-ui.td>{{ $vehicle->make }} {{ $vehicle->model }}</x-ui.td>
                <x-ui.td>{{ $vehicle->customer?->full_name ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ $vehicle->status?->label() ?? 'N/A' }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('vehicles.show', $vehicle)"
                        :edit="route('vehicles.edit', $vehicle)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
