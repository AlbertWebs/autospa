<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Sales</span></x-slot>

    <x-ui.section-header eyebrow="Sales" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('invoices.index'),
        ])
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(20rem,1fr)]">
        <x-ui.card>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Invoice Details</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $invoice->customer?->full_name ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Vehicle</dt><dd>{{ $invoice->vehicle?->registration_number ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ $invoice->status?->label() ?? 'Pending' }}</x-ui.badge></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Issued</dt><dd>{{ $invoice->issued_at?->format('M j, Y g:i A') ?? $invoice->created_at?->format('M j, Y g:i A') }}</dd></div>
            </dl>

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
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
                        @foreach ($invoice->items as $item)
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
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Totals</h2>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Subtotal</dt><dd class="font-mono">{{ number_format((float) $invoice->subtotal, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Discount</dt><dd class="font-mono">{{ number_format((float) $invoice->discount_amount, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Tax</dt><dd class="font-mono">{{ number_format((float) $invoice->tax_amount, 2) }}</dd></div>
                <div class="flex justify-between gap-4 border-t border-slate-200 pt-3 font-semibold dark:border-slate-800"><dt>Total</dt><dd class="font-mono text-brand-primary-dim dark:text-brand-primary">{{ number_format((float) $invoice->total_amount, 2) }}</dd></div>
            </div>

            @if ($invoice->payments->isNotEmpty())
                <h3 class="mt-6 text-sm font-semibold text-slate-900 dark:text-white">Payments</h3>
                <div class="mt-3 space-y-3">
                    @foreach ($invoice->payments as $payment)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-800 dark:bg-slate-900/40">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-medium text-slate-900 dark:text-white">{{ $payment->paymentMethod?->name ?? $payment->method?->value ?? 'Payment' }}</span>
                                <span class="font-mono">{{ number_format((float) $payment->amount, 2) }}</span>
                            </div>
                            @if ($payment->reference)
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Reference: <span class="font-mono">{{ $payment->reference }}</span></p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($invoice->receipts->isNotEmpty())
                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Issued Receipt</p>
                    <a href="{{ route('receipts.show', $invoice->receipts->first()) }}" class="mt-2 inline-flex items-center gap-2 text-sm font-medium text-brand-primary-dim hover:underline dark:text-brand-primary">
                        <span class="material-symbols-outlined text-base">receipt</span>
                        {{ $invoice->receipts->first()->receipt_number }}
                    </a>
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
