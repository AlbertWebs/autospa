<x-ui.form-page
    eyebrow="Inventory"
    title="Add Product"
    subtitle="Add a new product to your inventory catalog."
    panel-title="Product Details"
    panel-icon="inventory"
    :action="route('products.store')"
    submit-label="Create Product"
    :cancel-url="route('products.index')"
>
    @include('products._form')
</x-ui.form-page>
