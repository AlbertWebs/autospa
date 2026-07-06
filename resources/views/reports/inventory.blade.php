<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Insights</span></x-slot>

    <x-ui.section-header eyebrow="Insights" />

    <form method="GET" class="mb-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.form-field label="Stock Position As Of" for="as_of">
            <x-ui.input id="as_of" name="as_of" type="datetime-local" :value="$report['as_of']" />
        </x-ui.form-field>
        <x-ui.form-field label="Movements From" for="from">
            <x-ui.input id="from" name="from" type="date" :value="$report['from']" />
        </x-ui.form-field>
        <x-ui.form-field label="Movements To" for="to">
            <x-ui.input id="to" name="to" type="date" :value="$report['to']" />
        </x-ui.form-field>
        <div class="flex items-end">
            <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Update Report</button>
        </div>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-ui.stat-card label="Total Products" :value="$report['total_products'] ?? 0" />
        <x-ui.stat-card label="Low Stock (as of)" :value="$report['low_stock'] ?? 0" />
        <x-ui.stat-card label="Stock Value (as of)" :value="number_format($report['stock_value'] ?? 0, 2)" />
        <x-ui.stat-card label="Stock Ins" :value="$report['stock_in_count'] ?? 0" />
        <x-ui.stat-card label="Qty Added" :value="number_format($report['stock_in_quantity'] ?? 0, 2)" />
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 class="text-lg font-semibold">Stock Position</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    On-hand quantity as of {{ \Carbon\Carbon::parse($report['as_of'])->format('M j, Y g:i A') }}
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="asp-table w-full">
                    <thead>
                        <tr>
                            <x-ui.th>Product</x-ui.th>
                            <x-ui.th>SKU</x-ui.th>
                            <x-ui.th>Quantity</x-ui.th>
                            <x-ui.th>Value</x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['stock_positions'] as $position)
                            <tr class="asp-table-row">
                                <x-ui.td primary>{{ $position['product']->name }}</x-ui.td>
                                <x-ui.td mono muted>{{ $position['product']->sku }}</x-ui.td>
                                <x-ui.td>
                                    {{ number_format($position['quantity'], 2) }} {{ $position['product']->unit }}
                                    @if ($position['is_low'])
                                        <x-ui.badge color="amber" class="ml-2">Low</x-ui.badge>
                                    @endif
                                </x-ui.td>
                                <x-ui.td>{{ number_format($position['value'], 2) }}</x-ui.td>
                            </tr>
                        @empty
                            <tr>
                                <x-ui.td colspan="4" muted>No products found.</x-ui.td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 class="text-lg font-semibold">Stock Movements</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $report['from'] }} – {{ $report['to'] }}
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="asp-table w-full">
                    <thead>
                        <tr>
                            <x-ui.th>Date & Time</x-ui.th>
                            <x-ui.th>Product</x-ui.th>
                            <x-ui.th>Type</x-ui.th>
                            <x-ui.th>Qty</x-ui.th>
                            <x-ui.th>Balance</x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['movements'] as $movement)
                            <tr class="asp-table-row">
                                <x-ui.td muted>{{ $movement->moved_at?->format('M j, Y g:i A') }}</x-ui.td>
                                <x-ui.td primary>{{ $movement->product?->name ?? 'N/A' }}</x-ui.td>
                                <x-ui.td>
                                    <x-ui.badge color="indigo">{{ ucfirst($movement->type) }}</x-ui.badge>
                                </x-ui.td>
                                <x-ui.td>{{ number_format($movement->quantity, 2) }}</x-ui.td>
                                <x-ui.td>{{ number_format($movement->balance_after, 2) }}</x-ui.td>
                            </tr>
                        @empty
                            <tr>
                                <x-ui.td colspan="5" muted>No stock movements in this period.</x-ui.td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
