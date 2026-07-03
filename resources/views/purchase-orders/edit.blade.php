<x-ui.form-page
    eyebrow="Inventory"
    title="Edit Purchase Order"
    subtitle="Update purchase order items and delivery details."
    panel-title="Purchase Order Details"
    panel-icon="receipt_long"
    :action="route('purchase-orders.update', $purchaseOrder)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('purchase-orders.index')"
>
    @include('purchase-orders._form', ['purchaseOrder' => $purchaseOrder])
</x-ui.form-page>
