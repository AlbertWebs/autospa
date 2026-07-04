<x-ui.index-page
    eyebrow="Finance"
    title="Invoices"
    subtitle="View and manage customer invoices from sales."
>
    <x-ui.data-table
        :paginator="$invoices"
        :empty="$invoices->isEmpty()"
        empty-title="No invoices yet"
        empty-description="Invoices from sales will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Invoice #</x-ui.th>
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($invoices as $invoice)
            <tr class="asp-table-row">
                <x-ui.td primary mono>{{ $invoice->invoice_number }}</x-ui.td>
                <x-ui.td>{{ $invoice->customer?->full_name ?? 'N/A' }}</x-ui.td>
                <x-ui.td>{{ number_format((float) $invoice->total_amount, 2) }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ $invoice->status?->label() ?? 'Pending' }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions :view="route('invoices.show', $invoice)" />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
