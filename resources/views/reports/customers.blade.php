<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Insights</span></x-slot>

    <x-ui.section-header eyebrow="Insights" />

    <form method="GET" class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.form-field label="From" for="from">
            <x-ui.input id="from" name="from" type="date" :value="$report['from']" />
        </x-ui.form-field>
        <x-ui.form-field label="To" for="to">
            <x-ui.input id="to" name="to" type="date" :value="$report['to']" />
        </x-ui.form-field>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
            <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Update Report</button>
            <a href="{{ route('reports.customers') }}" class="asp-btn asp-btn-secondary !py-2.5">This Month</a>
        </div>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
        <x-ui.stat-card label="Total Customers" :value="number_format($report['total'] ?? 0)" icon="group" />
        <x-ui.stat-card
            label="New in Period"
            :value="number_format($report['new_in_period'] ?? 0)"
            hint="{{ \Carbon\Carbon::parse($report['from'])->format('M j') }} – {{ \Carbon\Carbon::parse($report['to'])->format('M j') }}"
            variant="bookings"
            icon="person_add"
        />
        <x-ui.stat-card label="Active in Period" :value="number_format($report['active_in_period'] ?? 0)" icon="directions_car" variant="service" />
        <x-ui.stat-card
            label="Period Revenue"
            :value="'KES ' . number_format($report['period_revenue'] ?? 0, 0)"
            variant="revenue"
            icon="payments"
        />
        <x-ui.stat-card
            label="Avg Spend / Active"
            :value="'KES ' . number_format($report['avg_spend_per_active'] ?? 0, 0)"
            icon="trending_up"
        />
        <x-ui.stat-card
            label="Repeat Rate"
            :value="($report['repeat_rate'] ?? 0) . '%'"
            :hint="($report['returning_in_period'] ?? 0) . ' returning customers'"
            icon="autorenew"
        />
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">Visit Frequency</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Based on completed job cards (all time).</p>
            <dl class="grid gap-3 text-sm">
                @foreach ([
                    'never_visited' => 'No visits yet',
                    'one_time' => 'One visit',
                    'regular' => '2–5 visits',
                    'loyal' => '6+ visits',
                ] as $key => $label)
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="font-mono font-medium">{{ number_format($report[$key] ?? 0) }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">Fleet Size</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Registered vehicles per customer.</p>
            <dl class="grid gap-3 text-sm">
                @foreach ([
                    'no_vehicles' => 'No vehicles',
                    'one_vehicle' => 'One vehicle',
                    'multiple_vehicles' => 'Multiple vehicles',
                ] as $key => $label)
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="font-mono font-medium">{{ number_format($report[$key] ?? 0) }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-1 text-lg font-semibold">New Customer Trend</h2>
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Monthly acquisitions (last 6 months).</p>
            <div class="space-y-3">
                @foreach ($report['acquisition_trend'] ?? [] as $month)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-slate-500">{{ $month['label'] }}</span>
                            <span class="font-mono font-medium">{{ $month['count'] }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full bg-indigo-500"
                                style="width: {{ $report['max_acquisition'] > 0 ? round(($month['count'] / $report['max_acquisition']) * 100) : 0 }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 class="text-lg font-semibold">Top Spenders</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">By paid invoice amount in the selected period.</p>
            </div>
            <x-ui.data-table
                :empty="($report['top_spenders'] ?? collect())->isEmpty()"
                empty-title="No spending in this period"
                empty-description="Paid invoices in the date range will rank customers here."
                :count="($report['top_spenders'] ?? collect())->count()"
            >
                <x-slot name="header">
                    <x-ui.th>#</x-ui.th>
                    <x-ui.th>Customer</x-ui.th>
                    <x-ui.th>Invoices</x-ui.th>
                    <x-ui.th align="right">Spent</x-ui.th>
                </x-slot>
                @foreach ($report['top_spenders'] ?? [] as $index => $row)
                    <tr class="asp-table-row">
                        <x-ui.td muted>{{ $index + 1 }}</x-ui.td>
                        <x-ui.td primary>
                            <a href="{{ route('customers.show', $row->customer) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                {{ $row->customer->full_name }}
                            </a>
                            @if ($row->customer->phone)
                                <p class="text-xs text-slate-500">{{ $row->customer->phone }}</p>
                            @endif
                        </x-ui.td>
                        <x-ui.td>{{ $row->invoice_count }}</x-ui.td>
                        <x-ui.td align="right" mono>KES {{ number_format((float) $row->period_spending, 0) }}</x-ui.td>
                    </tr>
                @endforeach
            </x-ui.data-table>
        </x-ui.card>

        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 class="text-lg font-semibold">New Customers</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Profiles created in the selected period.</p>
            </div>
            <x-ui.data-table
                :empty="($report['new_customers'] ?? collect())->isEmpty()"
                empty-title="No new customers"
                empty-description="New registrations in this period will appear here."
                :count="($report['new_customers'] ?? collect())->count()"
            >
                <x-slot name="header">
                    <x-ui.th>Customer</x-ui.th>
                    <x-ui.th>Joined</x-ui.th>
                    <x-ui.th>Vehicles</x-ui.th>
                    <x-ui.th align="right">Actions</x-ui.th>
                </x-slot>
                @foreach ($report['new_customers'] ?? [] as $customer)
                    <tr class="asp-table-row">
                        <x-ui.td primary>{{ $customer->full_name }}</x-ui.td>
                        <x-ui.td muted>{{ $customer->created_at->format('M j, Y') }}</x-ui.td>
                        <x-ui.td>{{ $customer->vehicles_count }}</x-ui.td>
                        <x-ui.td align="right">
                            <a href="{{ route('customers.show', $customer) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View</a>
                        </x-ui.td>
                    </tr>
                @endforeach
            </x-ui.data-table>
        </x-ui.card>
    </div>

    <x-ui.card :padding="false">
        <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <h2 class="text-lg font-semibold">At-Risk Customers</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Previously active customers with no completed visit in the last 60 days.
                @if (($report['at_risk_count'] ?? 0) > ($report['at_risk_customers'] ?? collect())->count())
                    Showing {{ ($report['at_risk_customers'] ?? collect())->count() }} of {{ $report['at_risk_count'] }}.
                @endif
            </p>
        </div>
        <x-ui.data-table
            :empty="($report['at_risk_customers'] ?? collect())->isEmpty()"
            empty-title="No at-risk customers"
            empty-description="Customers who lapse after 60 days without a visit will appear here."
            :count="($report['at_risk_customers'] ?? collect())->count()"
        >
            <x-slot name="header">
                <x-ui.th>Customer</x-ui.th>
                <x-ui.th>Last Visit</x-ui.th>
                <x-ui.th>Total Visits</x-ui.th>
                <x-ui.th align="right">Actions</x-ui.th>
            </x-slot>
            @foreach ($report['at_risk_customers'] ?? [] as $customer)
                <tr class="asp-table-row">
                    <x-ui.td primary>
                        {{ $customer->full_name }}
                        @if ($customer->phone)
                            <p class="text-xs text-slate-500">{{ $customer->phone }}</p>
                        @endif
                    </x-ui.td>
                    <x-ui.td muted>
                        {{ $customer->last_visit_at ? \Carbon\Carbon::parse($customer->last_visit_at)->format('M j, Y') : '—' }}
                    </x-ui.td>
                    <x-ui.td>{{ $customer->completed_visits }}</x-ui.td>
                    <x-ui.td align="right">
                        <a href="{{ route('customers.show', $customer) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View</a>
                    </x-ui.td>
                </tr>
            @endforeach
        </x-ui.data-table>
    </x-ui.card>
</x-layouts.app>
