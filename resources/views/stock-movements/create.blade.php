<x-ui.form-page
    eyebrow="Inventory"
    title="Record Stock Movement"
    subtitle="Log an inventory adjustment or transfer."
    panel-title="Stock Movement Details"
    panel-icon="inventory_2"
    :action="route('stock-movements.store')"
    submit-label="Record Movement"
    :cancel-url="route('stock-movements.index')"
>
    @include('stock-movements._form')
</x-ui.form-page>
