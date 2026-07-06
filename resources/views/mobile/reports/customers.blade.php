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
        <x-mobile.stat-tile label="Total" icon="group" :value="number_format($report['total'] ?? 0)" />
        <x-mobile.stat-tile label="New" icon="person_add" :value="number_format($report['new_in_period'] ?? 0)" />
        <x-mobile.stat-tile label="Active" icon="directions_car" :value="number_format($report['active_in_period'] ?? 0)" />
        <x-mobile.stat-tile label="Repeat Rate" icon="autorenew" :value="($report['repeat_rate'] ?? 0) . '%'" />
    </div>

    <div class="asp-mobile-card mb-4 space-y-3">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Period Revenue</p>
        <p class="text-2xl font-bold">KES {{ number_format($report['period_revenue'] ?? 0, 0) }}</p>
        <p class="text-sm text-slate-500">
            Avg KES {{ number_format($report['avg_spend_per_active'] ?? 0, 0) }} per active customer
        </p>
    </div>

    @if (($report['top_spenders'] ?? collect())->isNotEmpty())
        <div class="asp-mobile-card mb-4">
            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">Top Spenders</p>
            <div class="space-y-3">
                @foreach ($report['top_spenders'] as $index => $row)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $index + 1 }}. {{ $row->customer->full_name }}</p>
                            <p class="text-xs text-slate-500">{{ $row->invoice_count }} invoices</p>
                        </div>
                        <span class="shrink-0 font-mono font-semibold">KES {{ number_format((float) $row->period_spending, 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="asp-mobile-card space-y-3">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Visit Frequency</p>
        @foreach ([
            'never_visited' => 'No visits',
            'one_time' => 'One visit',
            'regular' => '2–5 visits',
            'loyal' => '6+ visits',
        ] as $key => $label)
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">{{ $label }}</span>
                <span class="font-semibold">{{ number_format($report[$key] ?? 0) }}</span>
            </div>
        @endforeach
    </div>
</x-layouts.mobile>
