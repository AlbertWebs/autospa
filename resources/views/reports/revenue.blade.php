<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Revenue Report</h1></x-slot>

    <form method="GET" class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.form-field label="From" for="from">
            <x-ui.input id="from" name="from" type="date" :value="$report['from']" />
        </x-ui.form-field>
        <x-ui.form-field label="To" for="to">
            <x-ui.input id="to" name="to" type="date" :value="$report['to']" />
        </x-ui.form-field>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
            <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Update Report</button>
            <a href="{{ route('reports.revenue') }}" class="asp-btn asp-btn-secondary !py-2.5">This Month</a>
        </div>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-ui.stat-card
            label="Period Revenue"
            :value="number_format($report['period'] ?? 0, 2)"
            hint="{{ \Carbon\Carbon::parse($report['from'])->format('M j') }} – {{ \Carbon\Carbon::parse($report['to'])->format('M j, Y') }}"
            variant="revenue"
            icon="payments"
        />
        <x-ui.stat-card label="Today" :value="number_format($report['today'] ?? 0, 2)" />
        <x-ui.stat-card label="This Week" :value="number_format($report['this_week'] ?? 0, 2)" />
        <x-ui.stat-card label="This Month" :value="number_format($report['this_month'] ?? 0, 2)" />
        <x-ui.stat-card label="Outstanding" :value="number_format($report['outstanding'] ?? 0, 2)" />
    </div>
    <x-ui.card>
        <h2 class="mb-4 text-lg font-semibold">Summary</h2>
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            @foreach ($report as $key => $value)
                @if(is_scalar($value))
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                        <dd class="font-medium">{{ is_numeric($value) ? number_format($value, 2) : $value }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </x-ui.card>
</x-layouts.app>
