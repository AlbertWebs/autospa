@php
    use App\Enums\JobCardStatus;

    $statusBadgeColor = fn (JobCardStatus $status): string => match ($status) {
        JobCardStatus::Open => 'amber',
        JobCardStatus::InProgress => 'sky',
        JobCardStatus::Completed => 'green',
        JobCardStatus::Cancelled => 'slate',
    };
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Insights</span></x-slot>

    <x-ui.section-header eyebrow="Insights" />

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <x-ui.form-field label="Report Date" for="report_date">
            <x-ui.input id="report_date" name="date" type="date" :value="$report['date']" />
        </x-ui.form-field>
        <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Update</button>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Open Today" :value="$report['today']['open'] ?? 0" icon="hourglass_top" variant="bookings" />
        <x-ui.stat-card label="In Progress Today" :value="$report['today']['in_progress'] ?? 0" icon="autorenew" variant="service" />
        <x-ui.stat-card label="Completed Today" :value="$report['today']['completed'] ?? 0" icon="task_alt" variant="ready" />
        <x-ui.stat-card label="Total Today" :value="$report['today']['total'] ?? 0" icon="assignment" />
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">This Week</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
                {{ $report['week_period'][0] ?? '—' }} – {{ $report['week_period'][1] ?? '—' }}
            </p>
            <dl class="grid gap-3 text-sm">
                @foreach (['open' => 'Open', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'total' => 'Total'] as $key => $label)
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="font-mono font-medium">{{ $report['week'][$key] ?? 0 }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">This Month</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">{{ $report['month_label'] ?? '—' }}</p>
            <dl class="grid gap-3 text-sm">
                @foreach (['open' => 'Open', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'total' => 'Total'] as $key => $label)
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="font-mono font-medium">{{ $report['month'][$key] ?? 0 }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>
    </div>

    <x-ui.card :padding="false">
        <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <h2 class="text-lg font-semibold">Job Cards for {{ $report['date'] }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">All job cards with activity on the selected date.</p>
        </div>

        <x-ui.data-table
            :empty="($report['job_cards'] ?? collect())->isEmpty()"
            empty-title="No job cards for this date"
            empty-description="Job card activity on this day will appear here."
            :count="($report['job_cards'] ?? collect())->count()"
        >
            <x-slot name="header">
<x-ui.th>Customer</x-ui.th>
                <x-ui.th>Vehicle</x-ui.th>
                <x-ui.th>Assignee</x-ui.th>
                <x-ui.th>Status</x-ui.th>
                <x-ui.th>Started</x-ui.th>
                <x-ui.th>Completed</x-ui.th>
                <x-ui.th align="right">Actions</x-ui.th>
            </x-slot>

            @foreach ($report['job_cards'] ?? [] as $jobCard)
                <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" />
                    <x-ui.td>{{ $jobCard->customer?->full_name ?? 'N/A' }}</x-ui.td>
                    <x-ui.td mono>{{ $jobCard->vehicle?->registration_number ?? 'N/A' }}</x-ui.td>
                    <x-ui.td>{{ $jobCard->assignee?->displayName() ?? 'Unassigned' }}</x-ui.td>
                    <x-ui.td>
                        <x-ui.badge :color="$statusBadgeColor($jobCard->status)">{{ $jobCard->status->label() }}</x-ui.badge>
                    </x-ui.td>
                    <x-ui.td muted>{{ $jobCard->started_at?->format('M j, g:i A') ?? '—' }}</x-ui.td>
                    <x-ui.td muted>{{ $jobCard->completed_at?->format('M j, g:i A') ?? '—' }}</x-ui.td>
                    <x-ui.td align="right">
                        <x-ui.table-actions :view="route('job-cards.show', $jobCard)" />
                    </x-ui.td>
                </tr>
            @endforeach
        </x-ui.data-table>
    </x-ui.card>
</x-layouts.app>
