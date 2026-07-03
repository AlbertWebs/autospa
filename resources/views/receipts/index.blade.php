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
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($receipts as $receipt)
            <tr class="asp-table-row">
                <x-ui.td primary mono>{{ $receipt->number ?? '#'.$receipt->id }}</x-ui.td>
                <x-ui.td>{{ $receipt->customer?->full_name ?? '—' }}</x-ui.td>
                <x-ui.td>{{ number_format($receipt->total ?? 0, 2) }}</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions :view="route('receipts.show', $receipt)" />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
