<x-ui.index-page
    eyebrow="Finance"
    title="Receipts"
    subtitle="Payment receipts issued to customers."
>
    <x-ui.data-table
        :paginator="$receipts"
        :empty="$receipts->isEmpty()"
        empty-title="No receipts yet"
        empty-description="Payment receipts will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Receipt #</x-ui.th>
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Date</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($receipts as $receipt)
            <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" :paginator="$receipts" />
                <x-ui.td primary mono>{{ $receipt->receipt_number }}</x-ui.td>
                <x-ui.td>{{ $receipt->invoice?->customer?->full_name ?? 'N/A' }}</x-ui.td>
                <x-ui.td muted>{{ $receipt->created_at?->format('M j, Y g:i A') ?? '—' }}</x-ui.td>
                <x-ui.td>{{ number_format((float) $receipt->amount, 2) }}</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions :view="route('receipts.show', $receipt)" />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
