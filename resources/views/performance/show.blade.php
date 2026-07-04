<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Performance Report</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('performance.index'),
        ])
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Jobs Completed" :value="$metric->jobs_completed ?? 0" />
        <x-ui.stat-card label="Revenue Generated" :value="number_format($metric->revenue_generated ?? 0, 2)" />
        <x-ui.stat-card label="Average Rating" :value="$metric->average_rating ? number_format($metric->average_rating, 1) : 'N/A'" />
        <x-ui.stat-card label="Employee" :value="$metric->employee?->full_name ?? 'N/A'" />
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Period</dt><dd>{{ $metric->period_start?->format('M j, Y') }} – {{ $metric->period_end?->format('M j, Y') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Jobs Completed</dt><dd class="font-medium">{{ $metric->jobs_completed ?? 0 }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Revenue Generated</dt><dd class="font-medium">{{ number_format($metric->revenue_generated ?? 0, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Average Rating</dt><dd>{{ $metric->average_rating ? number_format($metric->average_rating, 1) : 'N/A' }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
