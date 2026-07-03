<x-ui.index-page
    eyebrow="Inventory"
    title="Suppliers"
    subtitle="Manage vendor contacts for inventory purchases."
    :create-route="route('suppliers.create')"
    create-label="Add Supplier"
>
    <x-ui.data-table
        :paginator="$suppliers"
        :empty="$suppliers->isEmpty()"
        empty-title="No suppliers yet"
        empty-description="Add suppliers for inventory purchases."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Contact</x-ui.th>
            <x-ui.th>Phone</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($suppliers as $supplier)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $supplier->name }}</x-ui.td>
                <x-ui.td muted>{{ $supplier->contact_person ?? '—' }}</x-ui.td>
                <x-ui.td>{{ $supplier->phone ?? '—' }}</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('suppliers.show', $supplier)"
                        :edit="route('suppliers.edit', $supplier)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
