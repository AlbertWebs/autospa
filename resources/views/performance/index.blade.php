<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Staff Performance</h1></x-slot>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card title="Jobs Completed" :value="$metrics['jobs_completed'] ?? 0" />
        <x-ui.stat-card title="Revenue Generated" :value="number_format($metrics['revenue'] ?? 0, 2)" />
        <x-ui.stat-card title="Avg. Rating" :value="$metrics['avg_rating'] ?? '—'" />
        <x-ui.stat-card title="Attendance Rate" :value="($metrics['attendance_rate'] ?? 0).'%'" />
    </div>
    <x-ui.card class="mt-6" :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Jobs</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Revenue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Rating</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($metrics['employees'] ?? [] as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $row['name'] ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $row['jobs'] ?? 0 }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $row['rating'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8"><x-ui.empty-state title="No performance data" description="Staff performance metrics will appear here." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>
