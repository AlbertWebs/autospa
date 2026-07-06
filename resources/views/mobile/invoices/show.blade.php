<x-layouts.mobile title="Invoice">
    <x-mobile.page-header :title="$invoice->invoice_number ?? 'Invoice'" :back="route('mobile.invoices.index')" />
    <div class="asp-mobile-card space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-slate-500">Customer</span><span>{{ $invoice->customer?->full_name }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Total</span><span class="font-bold">KES {{ number_format($invoice->total_amount, 0) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Paid</span><span>KES {{ number_format($invoice->paid_amount, 0) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Balance</span><span>KES {{ number_format($invoice->balance_due, 0) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Status</span><span>{{ $invoice->status?->label() }}</span></div>
    </div>
    <a href="{{ route('invoices.show', $invoice) }}" class="asp-mobile-action-btn mt-4 inline-flex w-full justify-center">Full invoice</a>
</x-layouts.mobile>
