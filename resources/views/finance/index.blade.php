@php
    $formatKes = fn (float $amount): string => 'KES ' . number_format($amount, 0);
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Finance</span></x-slot>

    <x-ui.section-header eyebrow="Finance" />

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Finance Overview</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Run daily accounting from one place: income, expenses, profit and account closures.</p>
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

    <x-ui.card class="mb-6">
        <h2 class="mb-3 text-lg font-semibold">Close Accounts</h2>
        <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Lock this period totals for reconciliation and historical audit trail.</p>
        <form method="POST" action="{{ route('finance.close-accounts') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-turbo="false">
            @csrf
            <x-ui.form-field label="From" for="close_from">
                <x-ui.input id="close_from" name="from" type="date" :value="$report['from']" />
            </x-ui.form-field>
            <x-ui.form-field label="To" for="close_to">
                <x-ui.input id="close_to" name="to" type="date" :value="$report['to']" />
            </x-ui.form-field>
            <div class="flex items-end sm:col-span-2 lg:col-span-2">
                <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Close Accounts</button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card :padding="false">
        <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <h2 class="text-lg font-semibold">Recent Account Closures</h2>
        </div>
        <x-ui.data-table :empty="$closures->isEmpty()" empty-title="No closures yet" empty-description="Use the button above to close your first accounting period." :count="$closures->count()">
            <x-slot name="header">
                <x-ui.th>Period</x-ui.th>
                <x-ui.th align="right">Income</x-ui.th>
                <x-ui.th align="right">Expenses</x-ui.th>
                <x-ui.th align="right">Net</x-ui.th>
                <x-ui.th>Closed By</x-ui.th>
            </x-slot>
            @foreach ($closures as $closure)
                <tr class="asp-table-row">
                    <x-ui.table-number-td :loop="$loop" />
                    <x-ui.td primary>{{ $closure->from_date?->format('M j, Y') }} - {{ $closure->to_date?->format('M j, Y') }}</x-ui.td>
                    <x-ui.td align="right" mono>{{ $formatKes((float) $closure->income_total) }}</x-ui.td>
                    <x-ui.td align="right" mono>{{ $formatKes((float) $closure->expense_total) }}</x-ui.td>
                    <x-ui.td align="right" mono>{{ $formatKes((float) $closure->net_profit) }}</x-ui.td>
                    <x-ui.td muted>{{ $closure->closer?->name ?? 'System' }}</x-ui.td>
                </tr>
            @endforeach
        </x-ui.data-table>
    </x-ui.card>
</x-layouts.app>
