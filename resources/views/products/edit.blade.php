<x-ui.form-page
    eyebrow="Inventory"
    title="Edit Product"
    subtitle="Update product information, pricing, and stock levels."
    panel-title="Product Details"
    panel-icon="inventory"
    :action="route('products.update', $product)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('products.index')"
>
    @include('products._form', ['product' => $product])
</x-ui.form-page>
