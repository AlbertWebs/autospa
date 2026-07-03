<x-ui.index-page
    eyebrow="Inventory"
    title="Products"
    subtitle="Manage retail items, supplies, and stock levels."
    :create-route="route('products.create')"
    create-label="Add Product"
>
    <x-ui.data-table
        :paginator="$products"
        :empty="$products->isEmpty()"
        empty-title="No products yet"
        empty-description="Add inventory products for retail and supplies."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>SKU</x-ui.th>
            <x-ui.th>Stock</x-ui.th>
            <x-ui.th>Price</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($products as $product)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $product->name }}</x-ui.td>
                <x-ui.td mono muted>{{ $product->sku }}</x-ui.td>
                <x-ui.td>{{ $product->quantity_on_hand }}</x-ui.td>
                <x-ui.td>{{ number_format($product->selling_price, 2) }}</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('products.show', $product)"
                        :edit="route('products.edit', $product)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
