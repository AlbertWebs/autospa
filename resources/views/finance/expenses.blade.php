@php
    $formatKes = fn (float $amount): string => 'KES ' . number_format($amount, 0);
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Finance</span></x-slot>
    <x-ui.section-header eyebrow="Finance" />

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Expenses</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Record operating costs like rent, utilities, transport, and other direct expenses.</p>
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

    <x-ui.card class="mb-6">
        <h2 class="mb-3 text-lg font-semibold">Record Expense</h2>
        <form method="POST" action="{{ route('finance.expenses.store') }}" class="grid gap-4 md:grid-cols-2" data-turbo="false">
            @csrf
            <input type="hidden" name="from" value="{{ old('from', $report['from']) }}">
            <input type="hidden" name="to" value="{{ old('to', $report['to']) }}">
            <x-ui.form-field label="Category" for="category" name="category" :required="true">
                <x-ui.input id="category" name="category" :value="old('category')" placeholder="Rent, Utilities, Fuel..." required />
            </x-ui.form-field>
            <x-ui.form-field label="Amount" for="amount" name="amount" :required="true">
                <x-ui.input id="amount" name="amount" type="number" step="0.01" min="0.01" :value="old('amount')" required />
            </x-ui.form-field>
            <x-ui.form-field label="Description" for="description" name="description" :required="true">
                <x-ui.input id="description" name="description" :value="old('description')" placeholder="Monthly office rent" required />
            </x-ui.form-field>
            <x-ui.form-field label="Expense Date" for="spent_on" name="spent_on" :required="true">
                <x-ui.input id="spent_on" name="spent_on" type="date" :value="old('spent_on', now()->toDateString())" required />
            </x-ui.form-field>
            <div class="md:col-span-2">
                <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Save Expense</button>
            </div>
        </form>
    </x-ui.card>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Total Expenses" :value="$formatKes($report['total_expenses'])" icon="north_east" />
        <x-ui.stat-card label="Manual Expenses" :value="$formatKes($report['manual_expenses_total'])" icon="receipt_long" />
    </div>

    <x-ui.card class="mb-6">
        <h2 class="mb-3 text-lg font-semibold">Expense Breakdown</h2>
        <div class="space-y-3">
            @foreach ($report['breakdown'] as $row)
                <div>
                    <div class="mb-1 flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-300">{{ $row['label'] }}</span>
                        <span class="font-mono font-medium">{{ $formatKes($row['total']) }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-rose-500" style="width: {{ ($report['max_expense_row'] ?? 1) > 0 ? round(($row['total'] / $report['max_expense_row']) * 100) : 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-ui.card>

    <x-ui.card :padding="false">
        <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <h2 class="text-lg font-semibold">Manual Expense Entries</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Recent recorded expenses for this branch. Totals above still follow the date filter.</p>
        </div>
        <x-ui.data-table :empty="$report['manual_expense_entries']->isEmpty()" empty-title="No expenses yet" empty-description="Use the form above to record rent, utilities, and other costs." :count="$report['manual_expense_entries']->count()">
            <x-slot name="header">
                <x-ui.th>Date</x-ui.th>
                <x-ui.th>Category</x-ui.th>
                <x-ui.th>Description</x-ui.th>
                <x-ui.th align="right">Amount</x-ui.th>
            </x-slot>
            @foreach ($report['manual_expense_entries'] as $expense)
                <tr class="asp-table-row">
                    <x-ui.table-number-td :loop="$loop" />
                    <x-ui.td muted>{{ $expense->spent_on?->format('M j, Y') }}</x-ui.td>
                    <x-ui.td primary>{{ $expense->category }}</x-ui.td>
                    <x-ui.td muted>{{ $expense->description }}</x-ui.td>
                    <x-ui.td align="right" mono>{{ $formatKes((float) $expense->amount) }}</x-ui.td>
                </tr>
            @endforeach
        </x-ui.data-table>
    </x-ui.card>
</x-layouts.app>
