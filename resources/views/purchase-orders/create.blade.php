<x-ui.form-page
    eyebrow="Inventory"
    title="New Purchase Order"
    subtitle="Create a purchase order for supplier inventory."
    panel-title="Purchase Order Details"
    panel-icon="receipt_long"
    :action="route('purchase-orders.store')"
    submit-label="Create Purchase Order"
    :cancel-url="route('purchase-orders.index')"
>
    @include('purchase-orders._form')
</x-ui.form-page>
