<x-ui.form-page
    eyebrow="Inventory"
    title="Add Supplier"
    subtitle="Register a new supplier for inventory procurement."
    panel-title="Supplier Details"
    panel-icon="local_shipping"
    :action="route('suppliers.store')"
    submit-label="Create Supplier"
    :cancel-url="route('suppliers.index')"
>
    @include('suppliers._form')
</x-ui.form-page>
