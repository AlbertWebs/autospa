@php
    $stockProductsJson = $stockProducts->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'quantity_on_hand' => (float) $product->quantity_on_hand,
        'unit' => $product->unit,
    ])->values();
@endphp

<div
    x-data="productStockModal({
        products: @js($stockProductsJson),
        storeUrl: @js(route('stock-movements.store')),
        defaultMovedAt: @js(now()->format('Y-m-d\TH:i')),
    })"
>
    <x-ui.index-page
        eyebrow="Inventory"
        title="Products"
        subtitle="Manage retail items, supplies, and stock levels."
        :create-route="route('products.create')"
        create-label="Add Product"
    >
        @if ($canAddStock)
            <x-slot name="actions">
                <button type="button" class="asp-btn asp-btn-secondary" @click="openAddStockModal()">
                    <span class="material-symbols-outlined text-lg">add_box</span>
                    Add Stock
                </button>
            </x-slot>
        @endif

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
                <x-ui.table-number-td :loop="$loop" :paginator="$products" />
                    <x-ui.td primary>{{ $product->name }}</x-ui.td>
                    <x-ui.td mono muted>{{ $product->sku }}</x-ui.td>
                    <x-ui.td>{{ number_format($product->quantity_on_hand, 2) }}</x-ui.td>
                    <x-ui.td>{{ number_format($product->selling_price, 2) }}</x-ui.td>
                    <x-ui.td align="right">
                        <x-ui.table-actions
                            :view="route('products.show', $product)"
                            :edit="route('products.edit', $product)"
                        >
                            @if ($canAddStock)
                                <button
                                    type="button"
                                    class="asp-table-action"
                                    @click="openAddStockModal({{ $product->id }})"
                                >
                                    <span class="material-symbols-outlined text-base">add_box</span>
                                    Add Stock
                                </button>
                            @endif
                        </x-ui.table-actions>
                    </x-ui.td>
                </tr>
            @endforeach
        </x-ui.data-table>
    </x-ui.index-page>

    @if ($canAddStock)
        @include('partials.products.add-stock-modal')
    @endif
</div>
