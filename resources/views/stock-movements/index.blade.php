<x-ui.index-page
    eyebrow="Inventory"
    title="Stock Movements"
    subtitle="Track stock in and out across your inventory."
    :create-route="route('stock-movements.create')"
    create-label="Record Movement"
>
    <x-ui.data-table
        :paginator="$movements"
        :empty="$movements->isEmpty()"
        empty-title="No stock movements"
        empty-description="Record stock in/out movements here."
    >
        <x-slot name="header">
            <x-ui.th>Product</x-ui.th>
            <x-ui.th>Type</x-ui.th>
            <x-ui.th>Quantity</x-ui.th>
            <x-ui.th>Date</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($movements as $movement)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $movement->product?->name ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ ucfirst($movement->type) }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td>{{ $movement->quantity }}</x-ui.td>
                <x-ui.td muted>{{ $movement->created_at?->format('M j, Y') }}</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions :view="route('stock-movements.show', $movement)" />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
