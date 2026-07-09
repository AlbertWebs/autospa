@php
    $periodLabel = \Carbon\Carbon::parse($report['from'])->format('M j') . ' – ' . \Carbon\Carbon::parse($report['to'])->format('M j, Y');
    $formatKes = fn (float $amount): string => 'KES ' . number_format($amount, 0);
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Insights</span></x-slot>

    <x-ui.section-header eyebrow="Insights" />

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Revenue Report</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Income breakdown for {{ $periodLabel }}.</p>
    </div>

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
            label="Collected"
            :value="$formatKes($report['collected'] ?? $report['period'] ?? 0)"
            :hint="$periodLabel"
            variant="revenue"
            icon="payments"
        />
        <x-ui.stat-card
            label="Billed"
            :value="$formatKes($report['billed'] ?? 0)"
            :hint="($report['invoice_count'] ?? 0) . ' invoices'"
            icon="receipt_long"
        />
        <x-ui.stat-card label="Today" :value="$formatKes($report['today'] ?? 0)" icon="today" />
        <x-ui.stat-card label="This Week" :value="$formatKes($report['this_week'] ?? 0)" icon="date_range" />
        <x-ui.stat-card
            label="Outstanding"
            :value="$formatKes($report['outstanding'] ?? 0)"
            hint="All unpaid invoices"
            icon="hourglass_top"
        />
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">Income Summary</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Totals from invoices issued in this period.</p>
            <dl class="grid gap-3 text-sm">
                @foreach ([
                    'billed' => 'Gross billed',
                    'discounts' => 'Discounts',
                    'tax' => 'Tax',
                    'collected' => 'Collected',
                    'outstanding_from_period' => 'Still outstanding',
                ] as $key => $label)
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="font-mono font-medium">{{ $formatKes($report[$key] ?? 0) }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">By Payment Method</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">How customers paid for period invoices.</p>
            @if (($report['payment_breakdown'] ?? collect())->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($report['payment_breakdown'] as $row)
                        <div>
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-300">{{ $row['label'] }}</span>
                                <span class="font-mono font-medium">{{ $formatKes($row['total']) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full bg-emerald-500"
                                    style="width: {{ ($report['max_payment'] ?? 1) > 0 ? round(($row['total'] / $report['max_payment']) * 100) : 0 }}%"
                                ></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">{{ $row['count'] }} {{ $row['count'] === 1 ? 'payment' : 'payments' }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <x-ui.empty-state
                    title="No payments"
                    description="Collected payments for invoices in this period will appear here."
                />
            @endif
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">By Sales Type</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Revenue from services vs retail products.</p>
            <dl class="grid gap-3 text-sm">
                @foreach ([
                    'services_revenue' => 'Car wash services',
                    'products_revenue' => 'Products & supplies',
                ] as $key => $label)
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="font-mono font-medium">{{ $formatKes($report[$key] ?? 0) }}</dd>
                    </div>
                @endforeach
            </dl>
            @if (($report['sales_total'] ?? 0) > 0)
                <div class="mt-4 flex h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div
                        class="bg-indigo-500"
                        style="width: {{ round((($report['services_revenue'] ?? 0) / $report['sales_total']) * 100) }}%"
                    ></div>
                    <div
                        class="bg-amber-500"
                        style="width: {{ round((($report['products_revenue'] ?? 0) / $report['sales_total']) * 100) }}%"
                    ></div>
                </div>
                <div class="mt-2 flex justify-between text-xs text-slate-400">
                    <span>Services</span>
                    <span>Products</span>
                </div>
            @endif
        </x-ui.card>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">Daily Collections</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Paid amount by invoice issue date.</p>
            @if (($report['daily_trend'] ?? collect())->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($report['daily_trend'] as $day)
                        <div>
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-slate-500">{{ $day['label'] }}</span>
                                <span class="font-mono font-medium">{{ $formatKes($day['collected']) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full bg-brand-primary"
                                    style="width: {{ ($report['max_daily_collected'] ?? 1) > 0 ? round(($day['collected'] / $report['max_daily_collected']) * 100) : 0 }}%"
                                ></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">{{ $day['invoices'] }} {{ $day['invoices'] === 1 ? 'invoice' : 'invoices' }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <x-ui.empty-state
                    title="No daily data"
                    description="Issue invoices in this period to see daily collections."
                />
            @endif
        </x-ui.card>

        <div class="grid gap-6">
            <x-ui.card :padding="false">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-lg font-semibold">Top Services</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">By line-item revenue in the period.</p>
                </div>
                <x-ui.data-table
                    :empty="($report['top_services'] ?? collect())->isEmpty()"
                    empty-title="No service sales"
                    empty-description="Service lines on invoices in this period will rank here."
                    :count="($report['top_services'] ?? collect())->count()"
                >
                    <x-slot name="header">
                        <x-ui.th>Service</x-ui.th>
                        <x-ui.th align="right">Qty</x-ui.th>
                        <x-ui.th align="right">Revenue</x-ui.th>
                    </x-slot>
                    @foreach ($report['top_services'] ?? [] as $row)
                        <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" />
                            <x-ui.td primary>{{ $row->description }}</x-ui.td>
                            <x-ui.td align="right" mono>{{ number_format($row->quantity, 0) }}</x-ui.td>
                            <x-ui.td align="right" mono>{{ $formatKes($row->total) }}</x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.data-table>
            </x-ui.card>

            <x-ui.card :padding="false">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-lg font-semibold">Top Products</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Retail and add-on product sales.</p>
                </div>
                <x-ui.data-table
                    :empty="($report['top_products'] ?? collect())->isEmpty()"
                    empty-title="No product sales"
                    empty-description="Product lines on invoices in this period will rank here."
                    :count="($report['top_products'] ?? collect())->count()"
                >
                    <x-slot name="header">
                        <x-ui.th>Product</x-ui.th>
                        <x-ui.th align="right">Qty</x-ui.th>
                        <x-ui.th align="right">Revenue</x-ui.th>
                    </x-slot>
                    @foreach ($report['top_products'] ?? [] as $row)
                        <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" />
                            <x-ui.td primary>{{ $row->description }}</x-ui.td>
                            <x-ui.td align="right" mono>{{ number_format($row->quantity, 0) }}</x-ui.td>
                            <x-ui.td align="right" mono>{{ $formatKes($row->total) }}</x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.data-table>
            </x-ui.card>
        </div>
    </div>

    <x-ui.card :padding="false">
        <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <h2 class="text-lg font-semibold">Recent Paid Invoices</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Latest collections in the selected period.</p>
        </div>
        <x-ui.data-table
            :empty="($report['recent_invoices'] ?? collect())->isEmpty()"
            empty-title="No paid invoices"
            empty-description="Paid invoices issued in this period will list here."
            :count="($report['recent_invoices'] ?? collect())->count()"
        >
            <x-slot name="header">
                <x-ui.th>Invoice</x-ui.th>
                <x-ui.th>Customer</x-ui.th>
                <x-ui.th>Date</x-ui.th>
                <x-ui.th align="right">Billed</x-ui.th>
                <x-ui.th align="right">Collected</x-ui.th>
            </x-slot>
            @foreach ($report['recent_invoices'] ?? [] as $invoice)
                <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" />
                    <x-ui.td primary>
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-brand-primary hover:underline">
                            {{ $invoice->invoice_number }}
                        </a>
                    </x-ui.td>
                    <x-ui.td>{{ $invoice->customer?->full_name ?? 'Walk-in' }}</x-ui.td>
                    <x-ui.td muted>{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</x-ui.td>
                    <x-ui.td align="right" mono>{{ $formatKes($invoice->total_amount) }}</x-ui.td>
                    <x-ui.td align="right" mono>{{ $formatKes($invoice->paid_amount) }}</x-ui.td>
                </tr>
            @endforeach
        </x-ui.data-table>
    </x-ui.card>
</x-layouts.app>
