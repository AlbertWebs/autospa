<x-ui.index-page
    eyebrow="Inventory"
    title="Low Stock"
    subtitle="Products at or below their minimum stock level."
>
    <x-ui.data-table
        :paginator="$products"
        :empty="$products->isEmpty()"
        empty-title="All stocked up"
        empty-description="No products below minimum level."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>SKU</x-ui.th>
            <x-ui.th>On Hand</x-ui.th>
            <x-ui.th>Minimum</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($products as $product)
            <tr class="asp-table-row">
                <x-ui.td primary>
                    {{ $product->name }}
                    <x-ui.badge color="red">Low</x-ui.badge>
                </x-ui.td>
                <x-ui.td mono muted>{{ $product->sku }}</x-ui.td>
                <x-ui.td>{{ $product->quantity_on_hand }}</x-ui.td>
                <x-ui.td>{{ $product->minimum_level }}</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions :view="route('products.show', $product)" />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
