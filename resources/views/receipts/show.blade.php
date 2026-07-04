<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Receipt {{ $receipt->receipt_number }}</h1></x-slot>

    @php
        $routeAccess = app(\App\Support\RouteAccess::class);
        $canViewReceiptsIndex = $routeAccess->allows(auth()->user(), 'receipts.index');
        $canStartNewSale = $routeAccess->allows(auth()->user(), 'pos.index');
        $canViewInvoice = $routeAccess->allows(auth()->user(), 'invoices.show');
    @endphp

    <div class="mb-6 flex flex-wrap items-center gap-2">
        @if ($canViewReceiptsIndex)
            <a href="{{ route('receipts.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                All Receipts
            </a>
        @endif
        @if ($canStartNewSale)
            <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                <span class="material-symbols-outlined text-base">point_of_sale</span>
                New Sale
            </a>
        @endif
        @if ($canViewInvoice)
            <a href="{{ route('invoices.show', $receipt->invoice) }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-medium text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined text-base">receipt_long</span>
                View Invoice
            </a>
        @endif
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-medium text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800">
            <span class="material-symbols-outlined text-base">print</span>
            Print Receipt
        </button>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(20rem,1fr)]">
        <x-ui.card>
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4 dark:border-slate-800">
                <div>
                    <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-slate-400">Issued Receipt</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-900 dark:text-white">{{ $receipt->receipt_number }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Generated automatically after successful checkout.</p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Total Received</p>
                    <p class="font-mono text-2xl font-bold text-brand-primary-dim dark:text-brand-primary">
                        KES {{ number_format((float) $receipt->amount, 2) }}
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Customer</h3>
                    <dl class="mt-3 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Name</dt><dd class="font-medium">{{ $receipt->invoice?->customer?->full_name ?? 'Walk-in customer' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $receipt->invoice?->customer?->phone ?? 'N/A' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Vehicle</dt><dd>{{ $receipt->invoice?->vehicle?->registration_number ?? 'N/A' }}</dd></div>
                    </dl>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Transaction</h3>
                    <dl class="mt-3 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Invoice</dt><dd class="font-mono">{{ $receipt->invoice?->invoice_number ?? 'N/A' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Issued</dt><dd>{{ $receipt->created_at?->format('M j, Y g:i A') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Delivery</dt><dd>{{ \Illuminate\Support\Str::headline($receipt->delivery_method ?? 'counter') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="green">Paid</x-ui.badge></dd></div>
                    </dl>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Items</h3>
                <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-900/60">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-slate-500">Description</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-500">Qty</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-500">Unit Price</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-500">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach ($receipt->invoice?->items ?? [] as $item)
                                <tr>
                                    <td class="px-4 py-3 text-slate-900 dark:text-white">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-right font-mono">{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono">{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono">{{ number_format((float) $item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Payment Summary</h2>

            <div class="mt-4 space-y-3">
                @foreach ($receipt->invoice?->payments ?? [] as $payment)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ $payment->paymentMethod?->name ?? $payment->method?->value ?? 'Payment' }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $payment->paid_at?->format('M j, Y g:i A') ?? 'Paid' }}</p>
                            </div>
                            <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">KES {{ number_format((float) $payment->amount, 2) }}</p>
                        </div>
                        @if ($payment->reference)
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Reference: <span class="font-mono">{{ $payment->reference }}</span></p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                    <span class="font-mono text-slate-900 dark:text-white">KES {{ number_format((float) ($receipt->invoice?->subtotal ?? 0), 2) }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Tax</span>
                    <span class="font-mono text-slate-900 dark:text-white">KES {{ number_format((float) ($receipt->invoice?->tax_amount ?? 0), 2) }}</span>
                </div>
                <div class="mt-3 border-t border-slate-200 pt-3 dark:border-slate-800">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-semibold text-slate-900 dark:text-white">Grand Total</span>
                        <span class="font-mono text-lg font-bold text-brand-primary-dim dark:text-brand-primary">KES {{ number_format((float) ($receipt->invoice?->total_amount ?? 0), 2) }}</span>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
