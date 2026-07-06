<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Insights</span></x-slot>

    <x-ui.section-header eyebrow="Insights" />

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Revenue" :value="number_format($report['revenue'] ?? 0, 2)" />
        <x-ui.stat-card label="Bookings" :value="$report['bookings'] ?? 0" />
        <x-ui.stat-card label="Date" :value="$report['date'] ?? 'N/A'" />
        <x-ui.stat-card label="Completed Jobs" :value="$report['completed_jobs'] ?? 0" />
    </div>
    <x-ui.card>
        <h2 class="mb-4 text-lg font-semibold">Summary</h2>
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            @foreach ($report as $key => $value)
                @if(is_scalar($value))
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                        <dd class="font-medium">{{ is_numeric($value) && (str_contains($key, 'revenue') || str_contains($key, 'value')) ? number_format($value, 2) : $value }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </x-ui.card>
</x-layouts.app>
