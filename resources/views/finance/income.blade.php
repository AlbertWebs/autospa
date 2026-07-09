@php
    $formatKes = fn (float $amount): string => 'KES ' . number_format($amount, 0);
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Finance</span></x-slot>
    <x-ui.section-header eyebrow="Finance" />

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Income</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Track all completed customer payments.</p>
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

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <x-ui.stat-card label="Total Income" :value="$formatKes($report['total_income'])" :hint="$report['payment_count'] . ' payments'" variant="revenue" icon="south_west" />
    </div>

    <x-ui.card class="mb-6">
        <h2 class="mb-3 text-lg font-semibold">Income Breakdown</h2>
        <div class="space-y-3">
            @foreach ($report['income_breakdown'] as $row)
                <div>
                    <div class="mb-1 flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-300">{{ $row['label'] }}</span>
                        <span class="font-mono font-medium">{{ $formatKes($row['total']) }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ ($report['max_income_row'] ?? 1) > 0 ? round(($row['total'] / $report['max_income_row']) * 100) : 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-ui.card>

    <x-ui.card :padding="false">
        <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <h2 class="text-lg font-semibold">Payments</h2>
        </div>
        <x-ui.data-table :empty="$report['payments']->isEmpty()" empty-title="No payments" empty-description="No completed payments found for this period." :count="$report['payments']->count()">
            <x-slot name="header">
                <x-ui.th>Customer</x-ui.th>
                <x-ui.th>Method</x-ui.th>
                <x-ui.th>Date</x-ui.th>
                <x-ui.th align="right">Amount</x-ui.th>
            </x-slot>
            @foreach ($report['payments'] as $payment)
                <tr class="asp-table-row">
                    <x-ui.table-number-td :loop="$loop" />
                    <x-ui.td primary>{{ $payment->customer?->full_name ?? 'Walk-in' }}</x-ui.td>
                    <x-ui.td muted>{{ $payment->paymentMethod?->name ?? $payment->method?->label() ?? 'Payment' }}</x-ui.td>
                    <x-ui.td muted>{{ ($payment->paid_at ?? $payment->created_at)?->format('M j, Y g:i A') }}</x-ui.td>
                    <x-ui.td align="right" mono>{{ $formatKes((float) $payment->amount) }}</x-ui.td>
                </tr>
            @endforeach
        </x-ui.data-table>
    </x-ui.card>
</x-layouts.app>
