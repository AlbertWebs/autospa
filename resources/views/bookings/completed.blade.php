<x-ui.index-page
    eyebrow="Operations"
    title="Completed Bookings"
    subtitle="Finished appointments and service visits."
    :create-route="route('bookings.create')"
    create-label="New Booking"
>
    <x-ui.data-table
        :paginator="$bookings"
        :empty="$bookings->isEmpty()"
        empty-title="No completed bookings"
        empty-description="Completed bookings will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Vehicle</x-ui.th>
            <x-ui.th>Scheduled</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($bookings as $booking)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $booking->customer?->full_name ?? 'N/A' }}</x-ui.td>
                <x-ui.td mono>{{ $booking->vehicle?->registration_number ?? 'N/A' }}</x-ui.td>
                <x-ui.td>{{ $booking->scheduled_at?->format('M j, Y g:i A') ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('bookings.show', $booking)"
                        :edit="route('bookings.edit', $booking)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
