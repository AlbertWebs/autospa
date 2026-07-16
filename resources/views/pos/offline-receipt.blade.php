<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Sales</span></x-slot>

    <x-ui.section-header eyebrow="Sales" class="print:hidden" />

    <div
        class="asp-receipt-page mx-auto max-w-3xl"
        x-data="offlineReceiptPage()"
        x-init="load()"
    >
        <div class="asp-receipt-actions mb-6 flex flex-wrap items-center gap-2 print:hidden">
            <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                <span class="material-symbols-outlined text-base">point_of_sale</span>
                New Sale
            </a>
            <button type="button" @click="$store.fullscreen.printDocument()" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-medium text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined text-base">print</span>
                Print Receipt
            </button>
            <button type="button" @click="$store.fullscreen.printThermalDocument()" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-medium text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800">
                <span class="material-symbols-outlined text-base">receipt</span>
                Print Thermal
            </button>
        </div>

        <div x-show="!receipt" x-cloak class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-900/40">
            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No offline receipt found</p>
            <p class="mt-1 text-xs text-slate-500">Complete an offline sale to generate a printable receipt.</p>
            <a href="{{ route('pos.index') }}" class="asp-btn asp-btn-primary mt-4 inline-flex">Back to POS</a>
        </div>

        <article x-show="receipt" x-cloak class="asp-receipt-print rounded-2xl border border-slate-200/80 bg-white p-6 shadow-soft dark:border-brand-border/60 dark:bg-brand-surface sm:p-8">
            <header class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5 dark:border-slate-800">
                <div>
                    <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">Pending Sync Receipt</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-900 dark:text-white" x-text="receipt?.receipt_number"></h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sale saved offline — final receipt number issues when synced.</p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Total Received</p>
                    <p class="font-mono text-2xl font-bold text-brand-primary-dim dark:text-brand-primary">
                        KES <span x-text="formatMoney(receipt?.amount ?? 0)"></span>
                    </p>
                </div>
            </header>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Customer</h3>
                    <dl class="mt-3 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Name</dt><dd class="font-medium" x-text="receipt?.customer_name || 'Walk-in customer'"></dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd x-text="receipt?.customer_phone || 'N/A'"></dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Vehicle</dt><dd x-text="receipt?.vehicle_label || 'N/A'"></dd></div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Transaction</h3>
                    <dl class="mt-3 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Issued</dt><dd x-text="issuedLabel"></dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Payment</dt><dd x-text="receipt?.method_name || 'Cash'"></dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200">Queued for sync</span></dd></div>
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
                            <template x-for="(item, index) in (receipt?.items || [])" :key="index">
                                <tr>
                                    <td class="px-4 py-3 text-slate-900 dark:text-white" x-text="item.description"></td>
                                    <td class="px-4 py-3 text-right font-mono" x-text="formatMoney(item.quantity)"></td>
                                    <td class="px-4 py-3 text-right font-mono" x-text="formatMoney(item.unit_price)"></td>
                                    <td class="px-4 py-3 text-right font-mono" x-text="formatMoney(item.total)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-slate-900 dark:text-white" x-text="receipt?.method_name || 'Cash'"></p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Paid offline</p>
                    </div>
                    <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">
                        KES <span x-text="formatMoney(receipt?.amount ?? 0)"></span>
                    </p>
                </div>
            </div>
        </article>
    </div>
</x-layouts.app>
