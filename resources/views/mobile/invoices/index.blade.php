<x-layouts.mobile title="Invoices">
    <x-mobile.page-header title="Invoices" :back="route('mobile.menu')" />
    <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
        @forelse ($invoices as $invoice)
            <x-mobile.list-card
                :href="route('mobile.invoices.show', $invoice)"
                :title="$invoice->invoice_number ?? 'Invoice #' . $invoice->id"
                :subtitle="$invoice->customer?->full_name"
                :meta="$invoice->issued_at?->format('M j, Y')"
                :status="$invoice->status?->label()"
            />
        @empty
            <x-ui.empty-state title="No invoices" />
        @endforelse
    </div>
    <div class="mt-4">{{ $invoices->links() }}</div>
</x-layouts.mobile>
