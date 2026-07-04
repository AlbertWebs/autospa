<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Staff Report</h1></x-slot>
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Active Employees" :value="$report['employees'] ?? 0" />
        <x-ui.stat-card label="Jobs Completed" :value="$report['jobs_completed'] ?? 0" />
    </div>
    <x-ui.card>
        <h2 class="mb-4 text-lg font-semibold">Summary</h2>
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            @foreach ($report as $key => $value)
                @if(is_scalar($value))
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                        <dd class="font-medium">{{ $value }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </x-ui.card>
</x-layouts.app>
