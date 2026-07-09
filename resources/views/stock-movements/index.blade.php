<x-ui.index-page
    eyebrow="Inventory"
    title="Stock Movements"
    subtitle="Audit trail of stock changes. Add stock from the Products page."
>
    <x-ui.data-table
        :paginator="$movements"
        :empty="$movements->isEmpty()"
        empty-title="No stock movements"
        empty-description="Stock changes appear here when you add stock from Products."
    >
        <x-slot name="header">
            <x-ui.th>Product</x-ui.th>
            <x-ui.th>Type</x-ui.th>
            <x-ui.th>Quantity</x-ui.th>
            <x-ui.th>Balance After</x-ui.th>
            <x-ui.th>Date & Time</x-ui.th>
            <x-ui.th>Recorded By</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($movements as $movement)
            <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" :paginator="$movements" />
                <x-ui.td primary>{{ $movement->product?->name ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ ucfirst($movement->type) }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td>{{ number_format($movement->quantity, 2) }}</x-ui.td>
                <x-ui.td>{{ number_format($movement->balance_after, 2) }}</x-ui.td>
                <x-ui.td muted>{{ $movement->moved_at?->format('M j, Y g:i A') ?? $movement->created_at?->format('M j, Y g:i A') }}</x-ui.td>
                <x-ui.td muted>{{ $movement->user?->name ?? 'System' }}</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions :view="route('stock-movements.show', $movement)" />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
