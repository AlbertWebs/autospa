<x-ui.form-page
    eyebrow="Inventory"
    :title="$isStockIn ? 'Add Stock' : 'Record Stock Movement'"
    :subtitle="$isStockIn ? 'Increase on-hand quantity for a product.' : 'Log an inventory adjustment or transfer.'"
    panel-title="Stock Movement Details"
    panel-icon="inventory_2"
    :action="route('stock-movements.store')"
    :submit-label="$isStockIn ? 'Add Stock' : 'Record Movement'"
    :cancel-url="$returnTo === 'products' ? route('products.index') : route('stock-movements.index')"
>
    @include('stock-movements._form', [
        'defaultType' => $defaultType,
        'defaultProductId' => $defaultProductId,
        'returnTo' => $returnTo,
        'defaultMovedAt' => $defaultMovedAt,
    ])
</x-ui.form-page>
