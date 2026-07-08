@php
    $formatDuration = function (?int $minutes): string {
        if ($minutes === null || $minutes <= 0) {
            return '—';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $mins . 'm';
        }

        return $mins . 'm';
    };
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Insights</span></x-slot>

    <x-ui.section-header eyebrow="Insights" />

    <h1 class="mb-6 text-2xl font-bold text-slate-900 dark:text-white">Staff Report</h1>

    <form method="GET" class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.form-field label="From" for="from">
            <x-ui.input id="from" name="from" type="date" :value="$report['from']" />
        </x-ui.form-field>
        <x-ui.form-field label="To" for="to">
            <x-ui.input id="to" name="to" type="date" :value="$report['to']" />
        </x-ui.form-field>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
            <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Update Report</button>
            <a href="{{ route('reports.staff') }}" class="asp-btn asp-btn-secondary !py-2.5">This Month</a>
        </div>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
        <x-ui.stat-card label="Active Staff" :value="number_format($report['active_employees'] ?? 0)" icon="groups" />
        <x-ui.stat-card
            label="Jobs Completed"
            :value="number_format($report['jobs_completed'] ?? 0)"
            hint="{{ \Carbon\Carbon::parse($report['from'])->format('M j') }} – {{ \Carbon\Carbon::parse($report['to'])->format('M j') }}"
            variant="ready"
            icon="task_alt"
        />
        <x-ui.stat-card label="In Progress Now" :value="number_format($report['jobs_in_progress'] ?? 0)" icon="autorenew" variant="service" />
        <x-ui.stat-card
            label="Assigned Revenue"
            :value="'KES ' . number_format($report['period_revenue'] ?? 0, 0)"
            variant="revenue"
            icon="payments"
        />
        <x-ui.stat-card
            label="Avg Jobs / Staff"
            :value="number_format($report['avg_jobs_per_productive'] ?? 0, 1)"
            :hint="($report['productive_staff'] ?? 0) . ' staff with completions'"
            icon="trending_up"
        />
        <x-ui.stat-card
            label="Avg Completion"
            :value="$formatDuration($report['avg_completion_minutes'] ?? null)"
            icon="schedule"
        />
        <x-ui.stat-card
            label="Commission Earned"
            :value="'KES ' . number_format($report['total_commissions'] ?? 0, 0)"
            :hint="($report['total_commissions_pending'] ?? 0) > 0 ? 'KES ' . number_format($report['total_commissions_pending'], 0) . ' pending' : null"
            variant="payments"
            icon="savings"
        />
    </div>

    @if (($report['total_commissions_paid'] ?? 0) > 0)
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-ui.stat-card
                label="Commission Paid"
                :value="'KES ' . number_format($report['total_commissions_paid'] ?? 0, 0)"
                icon="check_circle"
                variant="ready"
            />
            <x-ui.stat-card
                label="Commission Pending"
                :value="'KES ' . number_format($report['total_commissions_pending'] ?? 0, 0)"
                icon="schedule"
                variant="service"
            />
        </div>
    @endif

    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">Job Activity</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Job cards with activity in the selected period.</p>
            <dl class="grid gap-3 text-sm">
                @foreach ($report['status_breakdown'] ?? [] as $status => $count)
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500 capitalize">{{ str_replace('_', ' ', $status) }}</dt>
                        <dd class="font-mono font-medium">{{ number_format($count) }}</dd>
                    </div>
                @endforeach
                <div class="flex justify-between gap-4 rounded-lg bg-amber-50 px-4 py-3 dark:bg-amber-950/30">
                    <dt class="text-amber-700 dark:text-amber-300">Unassigned completions</dt>
                    <dd class="font-mono font-medium text-amber-800 dark:text-amber-200">{{ number_format($report['unassigned_completed'] ?? 0) }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">By Position</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Completed jobs by staff role.</p>
            <div class="space-y-3">
                @forelse ($report['position_breakdown'] ?? [] as $row)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-slate-500">{{ $row['position'] }} ({{ $row['count'] }})</span>
                            <span class="font-mono font-medium">{{ $row['completed'] }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full bg-emerald-500"
                                style="width: {{ ($report['max_position_completed'] ?? 1) > 0 ? round(($row['completed'] / $report['max_position_completed']) * 100) : 0 }}%"
                            ></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No active staff.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">Weekly Completions</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Completed jobs by week (last 4 weeks).</p>
            <div class="space-y-3">
                @foreach ($report['productivity_trend'] ?? [] as $week)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-slate-500">Week of {{ $week['label'] }}</span>
                            <span class="font-mono font-medium">{{ $week['completed'] }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full bg-indigo-500"
                                style="width: {{ ($report['max_weekly_completed'] ?? 1) > 0 ? round(($week['completed'] / $report['max_weekly_completed']) * 100) : 0 }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <x-ui.card :padding="false" class="mb-6">
        <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <h2 class="text-lg font-semibold">Staff Leaderboard</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Performance in the selected period, ranked by completed jobs.</p>
        </div>
        <x-ui.data-table
            :empty="($report['leaderboard'] ?? collect())->isEmpty()"
            empty-title="No active staff"
            empty-description="Add employees to track team performance."
            :count="($report['leaderboard'] ?? collect())->count()"
        >
            <x-slot name="header">
                <x-ui.th>#</x-ui.th>
                <x-ui.th>Employee</x-ui.th>
                <x-ui.th>Completed</x-ui.th>
                <x-ui.th>In Progress</x-ui.th>
                <x-ui.th align="right">Revenue</x-ui.th>
                <x-ui.th align="right">Commission Earned</x-ui.th>
                <x-ui.th align="right">Paid</x-ui.th>
                <x-ui.th align="right">Pending</x-ui.th>
                <x-ui.th>Avg Time</x-ui.th>
                @if ($report['attendance_enabled'] ?? false)
                    <x-ui.th>Present Days</x-ui.th>
                @endif
                <x-ui.th align="right">Actions</x-ui.th>
            </x-slot>
            @foreach ($report['leaderboard'] ?? [] as $index => $row)
                <tr class="asp-table-row">
                    <x-ui.td muted>{{ $index + 1 }}</x-ui.td>
                    <x-ui.td primary>
                        {{ $row['employee']->full_name }}
                        @if ($row['employee']->position)
                            <p class="text-xs text-slate-500">{{ $row['employee']->position }}</p>
                        @endif
                    </x-ui.td>
                    <x-ui.td>{{ $row['completed'] }}</x-ui.td>
                    <x-ui.td>{{ $row['in_progress'] }}</x-ui.td>
                    <x-ui.td align="right" mono>KES {{ number_format($row['revenue'], 0) }}</x-ui.td>
                    <x-ui.td align="right" mono>KES {{ number_format($row['commissions'], 0) }}</x-ui.td>
                    <x-ui.td align="right" mono class="{{ $row['commissions_paid'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : '' }}">
                        KES {{ number_format($row['commissions_paid'], 0) }}
                    </x-ui.td>
                    <x-ui.td align="right" mono class="{{ $row['commissions_pending'] > 0 ? 'text-amber-600 dark:text-amber-400' : '' }}">
                        KES {{ number_format($row['commissions_pending'], 0) }}
                    </x-ui.td>
                    <x-ui.td muted>{{ $formatDuration($row['avg_minutes']) }}</x-ui.td>
                    @if ($report['attendance_enabled'] ?? false)
                        <x-ui.td>{{ $row['attendance_days'] }}</x-ui.td>
                    @endif
                    <x-ui.td align="right">
                        <a href="{{ route('employees.show', $row['employee']) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View</a>
                    </x-ui.td>
                </tr>
            @endforeach
        </x-ui.data-table>
    </x-ui.card>

    @if (($report['underutilized'] ?? collect())->isNotEmpty())
        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 class="text-lg font-semibold">Underutilized Staff</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Active employees with no completed jobs in the selected period.</p>
            </div>
            <x-ui.data-table
                :empty="false"
                :count="($report['underutilized'] ?? collect())->count()"
            >
                <x-slot name="header">
                    <x-ui.th>Employee</x-ui.th>
                    <x-ui.th>Position</x-ui.th>
                    <x-ui.th>In Progress</x-ui.th>
                    <x-ui.th align="right">Actions</x-ui.th>
                </x-slot>
                @foreach ($report['underutilized'] ?? [] as $row)
                    <tr class="asp-table-row">
                        <x-ui.td primary>{{ $row['employee']->full_name }}</x-ui.td>
                        <x-ui.td muted>{{ $row['employee']->position ?? '—' }}</x-ui.td>
                        <x-ui.td>{{ $row['in_progress'] }}</x-ui.td>
                        <x-ui.td align="right">
                            <a href="{{ route('employees.show', $row['employee']) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View</a>
                        </x-ui.td>
                    </tr>
                @endforeach
            </x-ui.data-table>
        </x-ui.card>
    @endif
</x-layouts.app>
