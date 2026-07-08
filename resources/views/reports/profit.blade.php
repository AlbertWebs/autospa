@php
    $periodLabel = \Carbon\Carbon::parse($report['from'])->format('M j') . ' – ' . \Carbon\Carbon::parse($report['to'])->format('M j, Y');
    $formatKes = fn (float $amount): string => 'KES ' . number_format($amount, 0);
    $netPositive = ($report['net_profit'] ?? 0) >= 0;
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Insights</span></x-slot>

    <x-ui.section-header eyebrow="Insights" />

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Profit & Loss</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Money in, money out, and net profit for {{ $periodLabel }}.</p>
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
            <a href="{{ route('reports.profit') }}" class="asp-btn asp-btn-secondary !py-2.5">This Month</a>
        </div>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Money In" :value="$formatKes($report['money_in'] ?? 0)" :hint="($report['money_in_count'] ?? 0) . ' payments received'" variant="revenue" icon="south_west" />
        <x-ui.stat-card label="Money Out" :value="$formatKes($report['money_out'] ?? 0)" hint="Commissions paid + supplier purchases" icon="north_east" />
        <x-ui.stat-card
            label="Net Profit"
            :value="$formatKes($report['net_profit'] ?? 0)"
            hint="Cash received minus cash paid out"
            icon="trending_up"
            :variant="$netPositive ? 'revenue' : 'payments'"
        />
        @if ($report['commissions_enabled'] ?? false)
            <x-ui.stat-card
                label="Operating Profit"
                :value="$formatKes($report['operating_profit'] ?? 0)"
                hint="Collected revenue minus commissions earned"
                icon="analytics"
            />
        @endif
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">Money In</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Customer payments received in this period.</p>
            @if (($report['money_in_breakdown'] ?? collect())->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($report['money_in_breakdown'] as $row)
                        <div>
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-300">{{ $row['label'] }}</span>
                                <span class="font-mono font-medium">{{ $formatKes($row['total']) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full bg-emerald-500"
                                    style="width: {{ ($report['max_money_in'] ?? 1) > 0 ? round(($row['total'] / $report['max_money_in']) * 100) : 0 }}%"
                                ></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">{{ $row['count'] }} {{ $row['count'] === 1 ? 'payment' : 'payments' }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <x-ui.empty-state title="No payments" description="Completed payments in this period will appear here." />
            @endif
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">Money Out</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Cash leaving the business in this period.</p>
            <div class="space-y-3">
                @foreach ([
                    ['key' => 'commissions_paid', 'label' => 'Washer commissions paid'],
                    ['key' => 'supplier_purchases', 'label' => 'Supplier purchases received'],
                ] as $item)
                    @php
                        $row = ($report['money_out_breakdown'] ?? collect())->firstWhere('key', $item['key']) ?? ['total' => 0, 'count' => 0, 'label' => $item['label']];
                    @endphp
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-300">{{ $row['label'] ?? $item['label'] }}</span>
                            <span class="font-mono font-medium">{{ $formatKes($row['total'] ?? 0) }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full bg-rose-500"
                                style="width: {{ ($report['max_money_out'] ?? 1) > 0 ? round((($row['total'] ?? 0) / max($report['money_out'] ?? 1, $report['max_money_out'] ?? 1)) * 100) : 0 }}%"
                            ></div>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">{{ $row['count'] ?? 0 }} {{ ($row['count'] ?? 0) === 1 ? 'transaction' : 'transactions' }}</p>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    @if ($report['commissions_enabled'] ?? false)
        <x-ui.card class="mb-6">
            <h2 class="mb-1 text-lg font-semibold">Operating View</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Revenue collected on invoices minus washer commissions earned (accrual basis).</p>
            <dl class="grid gap-3 text-sm sm:grid-cols-3">
                <div class="rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                    <dt class="text-slate-500">Revenue collected</dt>
                    <dd class="mt-1 font-mono text-lg font-semibold">{{ $formatKes($report['revenue_collected'] ?? 0) }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                    <dt class="text-slate-500">Commissions earned</dt>
                    <dd class="mt-1 font-mono text-lg font-semibold">{{ $formatKes($report['commissions_earned'] ?? 0) }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                    <dt class="text-slate-500">Pending commission</dt>
                    <dd class="mt-1 font-mono text-lg font-semibold text-amber-600 dark:text-amber-400">{{ $formatKes($report['commissions_pending'] ?? 0) }}</dd>
                </div>
            </dl>
        </x-ui.card>
    @endif

    <x-ui.card class="mb-6">
        <h2 class="mb-1 text-lg font-semibold">Daily Net Profit</h2>
        <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Money in minus money out by day.</p>
        @if (($report['daily_trend'] ?? collect())->isNotEmpty())
            <div class="space-y-3">
                @foreach ($report['daily_trend'] as $day)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-slate-500">{{ $day['label'] }}</span>
                            <span @class(['font-mono font-medium', 'text-emerald-600 dark:text-emerald-400' => $day['net_profit'] >= 0, 'text-rose-600 dark:text-rose-400' => $day['net_profit'] < 0])>
                                {{ $formatKes($day['net_profit']) }}
                            </span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full {{ $day['net_profit'] >= 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"
                                style="width: {{ ($report['max_daily_net'] ?? 1) > 0 ? round((abs($day['net_profit']) / $report['max_daily_net']) * 100) : 0 }}%"
                            ></div>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">
                            In {{ $formatKes($day['money_in']) }} · Out {{ $formatKes($day['money_out']) }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <x-ui.empty-state title="No cash activity" description="Payments and payouts in this period will chart here." />
        @endif
    </x-ui.card>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 class="text-lg font-semibold">Recent Payments In</h2>
            </div>
            <x-ui.data-table
                :empty="($report['recent_payments'] ?? collect())->isEmpty()"
                empty-title="No payments"
                empty-description="Customer payments received in this period."
                :count="($report['recent_payments'] ?? collect())->count()"
            >
                <x-slot name="header">
                    <x-ui.th>Customer</x-ui.th>
                    <x-ui.th>Method</x-ui.th>
                    <x-ui.th>Date</x-ui.th>
                    <x-ui.th align="right">Amount</x-ui.th>
                </x-slot>
                @foreach ($report['recent_payments'] ?? [] as $payment)
                    <tr class="asp-table-row">
                        <x-ui.td primary>{{ $payment->customer?->full_name ?? 'Walk-in' }}</x-ui.td>
                        <x-ui.td muted>{{ $payment->paymentMethod?->name ?? $payment->method?->label() ?? 'Payment' }}</x-ui.td>
                        <x-ui.td muted>{{ ($payment->paid_at ?? $payment->created_at)?->format('M j, Y') }}</x-ui.td>
                        <x-ui.td align="right" mono>{{ $formatKes($payment->amount) }}</x-ui.td>
                    </tr>
                @endforeach
            </x-ui.data-table>
        </x-ui.card>

        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 class="text-lg font-semibold">Recent Money Out</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-brand-border/60">
                @forelse ($report['recent_commission_payouts'] ?? [] as $commission)
                    <div class="flex items-center justify-between gap-3 px-6 py-3 text-sm">
                        <div>
                            <p class="font-medium">Commission · {{ $commission->employee?->full_name }}</p>
                            <p class="text-xs text-slate-500">{{ $commission->paid_at?->format('M j, Y g:i A') }}</p>
                        </div>
                        <span class="font-mono font-semibold text-rose-600 dark:text-rose-400">−{{ $formatKes($commission->amount) }}</span>
                    </div>
                @empty
                @endforelse
                @foreach ($report['recent_supplier_purchases'] ?? [] as $order)
                    <div class="flex items-center justify-between gap-3 px-6 py-3 text-sm">
                        <div>
                            <p class="font-medium">Purchase · {{ $order->supplier?->name ?? $order->reference }}</p>
                            <p class="text-xs text-slate-500">Received {{ $order->received_at?->format('M j, Y') }}</p>
                        </div>
                        <span class="font-mono font-semibold text-rose-600 dark:text-rose-400">−{{ $formatKes($order->total_amount) }}</span>
                    </div>
                @endforeach
                @if (($report['recent_commission_payouts'] ?? collect())->isEmpty() && ($report['recent_supplier_purchases'] ?? collect())->isEmpty())
                    <div class="px-6 py-8">
                        <x-ui.empty-state title="No payouts" description="Commission and supplier payments in this period will list here." />
                    </div>
                @endif
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
