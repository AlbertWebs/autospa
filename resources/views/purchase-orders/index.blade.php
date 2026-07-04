<x-ui.index-page
    eyebrow="Inventory"
    title="Purchase Orders"
    subtitle="Create and manage orders to restock inventory."
    :create-route="route('purchase-orders.create')"
    create-label="New Purchase Order"
>
    <x-ui.data-table
        :paginator="$purchaseOrders"
        :empty="$purchaseOrders->isEmpty()"
        empty-title="No purchase orders"
        empty-description="Create purchase orders to restock inventory."
    >
        <x-slot name="header">
            <x-ui.th>Reference</x-ui.th>
            <x-ui.th>Supplier</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($purchaseOrders as $purchaseOrder)
            <tr class="asp-table-row">
                <x-ui.td primary mono>{{ $purchaseOrder->reference ?? '#'.$purchaseOrder->id }}</x-ui.td>
                <x-ui.td>{{ $purchaseOrder->supplier?->name ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ ucfirst($purchaseOrder->status) }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('purchase-orders.show', $purchaseOrder)"
                        :edit="route('purchase-orders.edit', $purchaseOrder)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
