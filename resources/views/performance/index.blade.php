<x-ui.index-page
    eyebrow="Staff"
    title="Staff Performance"
    :subtitle="($attendanceEnabled ?? false) ? 'Overview of team productivity, revenue, and attendance.' : 'Overview of team productivity and revenue.'"
>
    <div class="grid gap-6 sm:grid-cols-2 {{ ($attendanceEnabled ?? false) ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
        <x-ui.stat-card label="Jobs Completed" :value="$metrics['jobs_completed'] ?? 0" />
        <x-ui.stat-card label="Revenue Generated" :value="number_format($metrics['revenue'] ?? 0, 2)" />
        <x-ui.stat-card label="Avg. Rating" :value="$metrics['avg_rating'] ?? 'N/A'" />
        @if ($attendanceEnabled ?? false)
            <x-ui.stat-card label="Attendance Rate" :value="($metrics['attendance_rate'] ?? 0).'%'" />
        @endif
    </div>

    <x-ui.data-table
        class="mt-6"
        :empty="empty($metrics['employees'] ?? [])"
        :count="count($metrics['employees'] ?? [])"
        empty-title="No performance data"
        empty-description="Staff performance metrics will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Employee</x-ui.th>
            <x-ui.th>Jobs</x-ui.th>
            <x-ui.th>Revenue</x-ui.th>
            <x-ui.th>Rating</x-ui.th>
        </x-slot>

        @foreach ($metrics['employees'] ?? [] as $row)
            <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" />
                <x-ui.td primary>{{ $row['name'] ?? 'N/A' }}</x-ui.td>
                <x-ui.td>{{ $row['jobs'] ?? 0 }}</x-ui.td>
                <x-ui.td>{{ number_format($row['revenue'] ?? 0, 2) }}</x-ui.td>
                <x-ui.td>{{ $row['rating'] ?? 'N/A' }}</x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
