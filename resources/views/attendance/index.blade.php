<x-ui.index-page
    eyebrow="Staff"
    title="Attendance"
    subtitle="Record and review employee clock-in and clock-out times."
    :create-route="route('attendance.create')"
    create-label="Record Attendance"
>
    <x-ui.data-table
        :paginator="$attendance"
        :empty="$attendance->isEmpty()"
        empty-title="No attendance records"
        empty-description="Record employee attendance here."
    >
        <x-slot name="header">
            <x-ui.th>Employee</x-ui.th>
            <x-ui.th>Date</x-ui.th>
            <x-ui.th>Clock In/Out</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($attendance as $record)
            <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" :paginator="$attendance" />
                <x-ui.td primary>{{ $record->employee?->full_name ?? 'N/A' }}</x-ui.td>
                <x-ui.td>{{ $record->date?->format('M j, Y') }}</x-ui.td>
                <x-ui.td muted>{{ $record->clock_in ?? 'N/A' }} / {{ $record->clock_out ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions :view="route('attendance.show', $record)" />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
