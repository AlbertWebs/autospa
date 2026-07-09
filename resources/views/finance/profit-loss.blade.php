@php
    $formatKes = fn (float $amount): string => 'KES ' . number_format($amount, 0);
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Finance</span></x-slot>
    <x-ui.section-header eyebrow="Finance" />

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Profit &amp; Loss</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Practical P&amp;L: income minus all tracked expenses for the selected period.</p>
    </div>

    @include('finance._tabs')

    <form method="GET" class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.form-field label="From" for="from">
            <x-ui.input id="from" name="from" type="date" :value="$report['from']" />
        </x-ui.form-field>
        <x-ui.form-field label="To" for="to">
            <x-ui.input id="to" name="to" type="date" :value="$report['to']" />
        </x-ui.form-field>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
            <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Update</button>
        </div>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.stat-card label="Income" :value="$formatKes($report['income_total'])" icon="south_west" variant="revenue" />
        <x-ui.stat-card label="Expenses" :value="$formatKes($report['expense_total'])" icon="north_east" />
        <x-ui.stat-card label="Net Profit" :value="$formatKes($report['net_profit'])" icon="trending_up" :variant="$report['net_profit'] >= 0 ? 'revenue' : 'payments'" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="mb-3 text-lg font-semibold">Income Lines</h2>
            <div class="space-y-2 text-sm">
                @foreach ($report['income_breakdown'] as $row)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800">
                        <span>{{ $row['label'] }}</span>
                        <span class="font-mono font-semibold">{{ $formatKes($row['total']) }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-3 text-lg font-semibold">Expense Lines</h2>
            <div class="space-y-2 text-sm">
                @foreach ($report['expense_breakdown'] as $row)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800">
                        <span>{{ $row['label'] }}</span>
                        <span class="font-mono font-semibold">{{ $formatKes($row['total']) }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
