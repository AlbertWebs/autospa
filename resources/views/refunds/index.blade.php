<x-ui.index-page
    eyebrow="Finance"
    title="Refunds"
    subtitle="Process and track customer refund requests."
    :create-route="route('refunds.create')"
    create-label="New Refund"
>
    <x-ui.data-table
        :paginator="$refunds"
        :empty="$refunds->isEmpty()"
        empty-title="No refunds"
        empty-description="Refund records will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Refund #</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($refunds as $refund)
            <tr class="asp-table-row">
                <x-ui.td primary mono>#{{ $refund->id }}</x-ui.td>
                <x-ui.td>{{ number_format($refund->amount ?? 0, 2) }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ $refund->status ?? 'pending' }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions :view="route('refunds.show', $refund)" />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
