<x-layouts.mobile :title="$title">
    <x-mobile.page-header :title="$title" :back="route('mobile.reports.index')" />

    <form method="GET" action="{{ $filterRoute }}" class="mb-4 grid grid-cols-2 gap-3">
        <x-ui.form-field label="From" for="from">
            <x-ui.input id="from" name="from" type="date" :value="$filters['from'] ?? ''" />
        </x-ui.form-field>
        <x-ui.form-field label="To" for="to">
            <x-ui.input id="to" name="to" type="date" :value="$filters['to'] ?? ''" />
        </x-ui.form-field>
        <button type="submit" class="asp-btn asp-btn-primary col-span-2 !py-2.5">Update</button>
    </form>

    <div class="mb-4 grid grid-cols-2 gap-3">
        <x-mobile.stat-tile label="Active Staff" icon="groups" :value="number_format($report['active_employees'] ?? 0)" />
        <x-mobile.stat-tile label="Completed" icon="task_alt" :value="number_format($report['jobs_completed'] ?? 0)" />
        <x-mobile.stat-tile label="In Progress" icon="autorenew" :value="number_format($report['jobs_in_progress'] ?? 0)" />
        <x-mobile.stat-tile label="Revenue" icon="payments" :value="'KES ' . number_format($report['period_revenue'] ?? 0, 0)" />
    </div>

    @if (($report['leaderboard'] ?? collect())->isNotEmpty())
        <div class="asp-mobile-card">
            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Leaderboard</p>
            <div class="space-y-3">
                @foreach ($report['leaderboard']->take(8) as $index => $row)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $index + 1 }}. {{ $row['employee']->full_name }}</p>
                            <p class="text-xs text-slate-500">{{ $row['completed'] }} jobs · KES {{ number_format($row['revenue'], 0) }}</p>
                        </div>
                        @if ($row['in_progress'] > 0)
                            <x-ui.badge color="sky">{{ $row['in_progress'] }} active</x-ui.badge>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-layouts.mobile>
